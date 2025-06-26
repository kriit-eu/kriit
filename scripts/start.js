#!/usr/bin/env bun
import { $ } from "bun";
import { createHash } from "crypto";
import { readFileSync, writeFileSync, existsSync, statSync } from "fs";
import { join, resolve } from "path";
import { glob } from "glob";

const COMPOSE_FILE = "docker/docker-compose.yml";
const CHECKSUMS_FILE = ".docker-checksums.json";

// Define which files to track for each service
const SERVICE_FILES = {
    app: [
        "docker/php/Dockerfile",
        "docker/php/dev.ini"
    ],
    nginx: [
        "docker/nginx/Dockerfile",
        "docker/nginx/nginx.conf"
    ],
    db: [
        "docker/db/Dockerfile",
        "docker/db/my.cnf",
        "docker/db/entrypoint.sh"
    ],
    mailhog: [
        "docker/mailhog/Dockerfile"
    ],
    phpmyadmin: [
        "docker/phpmyadmin/Dockerfile",
        "docker/phpmyadmin/config.inc.php"
    ]
};

function calculateFileChecksum(filePath) {
    try {
        if (!existsSync(filePath)) {
            return null;
        }
        const content = readFileSync(filePath);
        return createHash('sha256').update(content).digest('hex');
    } catch (error) {
        console.warn(`Warning: Could not read ${filePath}: ${error.message}`);
        return null;
    }
}

function calculateServiceChecksum(serviceFiles) {
    const checksums = serviceFiles
        .map(file => calculateFileChecksum(file))
        .filter(checksum => checksum !== null);

    if (checksums.length === 0) return null;

    return createHash('sha256')
        .update(checksums.join(''))
        .digest('hex');
}

function loadPreviousChecksums() {
    try {
        if (existsSync(CHECKSUMS_FILE)) {
            return JSON.parse(readFileSync(CHECKSUMS_FILE, 'utf8'));
        }
    } catch (error) {
        console.warn(`Warning: Could not load previous checksums: ${error.message}`);
    }
    return {};
}

function saveCurrentChecksums(checksums) {
    try {
        writeFileSync(CHECKSUMS_FILE, JSON.stringify(checksums, null, 2));
    } catch (error) {
        console.warn(`Warning: Could not save checksums: ${error.message}`);
    }
}

function getChangedServices() {
    const previousChecksums = loadPreviousChecksums();
    const currentChecksums = {};
    const changedServices = [];

    // Calculate current checksums for each service
    for (const [service, files] of Object.entries(SERVICE_FILES)) {
        const currentChecksum = calculateServiceChecksum(files);
        currentChecksums[service] = currentChecksum;

        // Compare with previous checksum
        if (currentChecksum !== previousChecksums[service]) {
            changedServices.push(service);
            if (previousChecksums[service]) {
                console.log(`📦 ${service}: Files changed, will rebuild`);
            } else {
                console.log(`📦 ${service}: No previous checksum, will build`);
            }
        } else {
            console.log(`✅ ${service}: No changes detected, skipping build`);
        }
    }

    // Save current checksums for next time
    saveCurrentChecksums(currentChecksums);

    return changedServices;
}

async function buildSpecificServices(services) {
    if (services.length === 0) {
        console.log("🎉 No services need rebuilding!");
        return;
    }

    console.log(`🔨 Building changed services: ${services.join(', ')}`);

    // Build only the changed services
    for (const service of services) {
        console.log(`🔨 Building ${service}...`);
        await $`bun run compose build ${service}`;
    }
}

async function up() {
    console.log("🚀 Starting containers...");
    await $`bun run compose up -d --remove-orphans`;
}

async function showStatus() {
    console.log("\n📦 Container status:");
    await $`bun run compose ps`;
}

function detectShell() {
    // Get shell from SHELL environment variable or process parent
    const shell = process.env.SHELL || '';
    if (shell.includes('zsh')) return 'zsh';
    if (shell.includes('fish')) return 'fish';
    if (shell.includes('bash')) return 'bash';
    if (shell.includes('ash')) return 'ash';
    if (process.platform === 'win32') return 'powershell';
    return 'bash'; // fallback
}

function getDistroInfo() {
    try {
        // Try to detect Linux distribution
        if (process.platform !== 'linux') return null;

        const fs = require('fs');
        if (fs.existsSync('/etc/os-release')) {
            const osRelease = fs.readFileSync('/etc/os-release', 'utf8');
            if (osRelease.includes('ubuntu') || osRelease.includes('Ubuntu')) return 'ubuntu';
            if (osRelease.includes('debian') || osRelease.includes('Debian')) return 'debian';
            if (osRelease.includes('fedora') || osRelease.includes('Fedora')) return 'fedora';
            if (osRelease.includes('centos') || osRelease.includes('CentOS')) return 'centos';
            if (osRelease.includes('rhel') || osRelease.includes('Red Hat')) return 'rhel';
            if (osRelease.includes('alpine') || osRelease.includes('Alpine')) return 'alpine';
            if (osRelease.includes('arch') || osRelease.includes('Arch')) return 'arch';
        }
        if (fs.existsSync('/etc/alpine-release')) return 'alpine';
        if (fs.existsSync('/etc/arch-release')) return 'arch';
    } catch (error) {
        // Ignore errors, fallback to generic linux
    }
    return 'linux';
}

function cmdHighlight(cmd) {
    // ANSI colors for highlighting commands
    const colors = {
        reset: '\x1b[0m',
        bright: '\x1b[1m',
        cyan: '\x1b[36m',
        yellow: '\x1b[33m',
        green: '\x1b[32m',
        blue: '\x1b[34m',
        red: '\x1b[31m'
    };

    return `${colors.bright}${colors.cyan}${cmd}${colors.reset}`;
}

function statusIcon(isInstalled) {
    return isInstalled ? '✅' : '❌';
}

function statusColor(text, isInstalled) {
    const colors = {
        reset: '\x1b[0m',
        green: '\x1b[32m',
        red: '\x1b[31m'
    };

    return isInstalled ? `${colors.green}${text}${colors.reset}` : `${colors.red}${text}${colors.reset}`;
}

async function checkSystemStatus() {
    const status = {
        platform: process.platform,
        shell: detectShell(),
        distro: getDistroInfo(),
        podman: false,
        docker: false,
        podmanCompose: false,
        hasBuiltinCompose: false,
        podmanMachine: false,
        pip: false,
        pipx: false,
        python3: false,
        winget: false,
        choco: false,
        wsl2: false,
        openssh: false
    };

    // Use appropriate command based on platform
    const whichCmd = status.platform === 'win32' ? 'where' : 'which';

    // Check container runtimes
    try {
        await $`${whichCmd} podman`.quiet();
        status.podman = true;
    } catch { status.podman = false; }

    try {
        await $`${whichCmd} docker`.quiet();
        status.docker = true;
    } catch { status.docker = false; }

    // Check podman-compose (try both modern 'podman compose' and legacy 'podman-compose')
    try {
        if (status.podman) {
            // First try modern built-in podman compose
            await $`podman compose version`.quiet();
            status.hasBuiltinCompose = true;
            status.podmanCompose = true; // Set this to true if built-in compose works
        } else {
            status.podmanCompose = false;
        }
    } catch { 
        // Fall back to legacy podman-compose
        try {
            await $`${whichCmd} podman-compose`.quiet();
            status.podmanCompose = true;
            status.hasBuiltinCompose = false;
        } catch { 
            status.podmanCompose = false; 
            status.hasBuiltinCompose = false;
        }
    }

    // Check Python tools
    try {
        await $`${whichCmd} python3`.quiet();
        status.python3 = true;
    } catch { 
        // On Windows, try 'python' if 'python3' fails
        if (status.platform === 'win32') {
            try {
                await $`${whichCmd} python`.quiet();
                status.python3 = true;
            } catch { status.python3 = false; }
        } else {
            status.python3 = false;
        }
    }

    try {
        await $`${whichCmd} pip3`.quiet();
        status.pip = true;
    } catch { 
        // On Windows, try 'pip' if 'pip3' fails
        if (status.platform === 'win32') {
            try {
                await $`${whichCmd} pip`.quiet();
                status.pip = true;
            } catch { status.pip = false; }
        } else {
            status.pip = false;
        }
    }

    try {
        await $`${whichCmd} pipx`.quiet();
        status.pipx = true;
    } catch { status.pipx = false; }

    // Check Windows package managers
    if (status.platform === 'win32') {
        try {
            await $`powershell -Command "winget --version"`.quiet();
            status.winget = true;
        } catch { 
            status.winget = false; 
        }

        try {
            await $`powershell -Command "choco --version"`.quiet();
            status.choco = true;
        } catch { status.choco = false; }
    }

    // Check podman machine status (macOS/Windows)
    if (status.podman && (status.platform === 'darwin' || status.platform === 'win32')) {
        try {
            const machineList = await $`podman machine list --format json`.quiet();
            const machines = JSON.parse(machineList.stdout);
            status.podmanMachine = machines.some(machine => machine.Running);
        } catch (error) {
            status.podmanMachine = false;
        }
    }

    // Check WSL2 status (Windows only)
    if (status.platform === 'win32') {
        try {
            await $`wsl --status`.quiet();
            status.wsl2 = true;
        } catch {
            try {
                // Fallback: check if any distributions are installed
                await $`wsl -l --quiet`.quiet();
                status.wsl2 = true;
            } catch {
                status.wsl2 = false;
            }
        }

        // Check OpenSSH client (required for Podman machine on Windows)
        try {
            await $`ssh-keygen -V`.quiet();
            status.openssh = true;
        } catch {
            try {
                // Try alternative location
                await $`powershell -Command "ssh-keygen -V"`.quiet();
                status.openssh = true;
            } catch {
                status.openssh = false;
            }
        }
    }

    return status;
}

function displaySystemStatus(status) {
    console.log("\n🔍 SYSTEM STATUS");
    console.log("=".repeat(30));

    console.log(`🖥️  Platform: ${status.platform} ${status.distro ? `(${status.distro})` : ''}`);
    console.log(`🐚 Shell: ${status.shell}`);
    console.log("");

    console.log("📦 Container Runtimes:");
    console.log(`   ${statusIcon(status.podman)} Podman: ${statusColor(status.podman ? 'Installed' : 'Not installed', status.podman)}`);
    console.log(`   ${statusIcon(status.docker)} Docker: ${statusColor(status.docker ? 'Installed' : 'Not installed', status.docker)}`);

    if (status.podman) {
        // Display compose status (will be set by checkSystemStatus)
        const composeStatus = status.podmanCompose || status.hasBuiltinCompose;
        const composeType = status.hasBuiltinCompose ? "Built-in compose" : 
                           status.podmanCompose ? "Legacy podman-compose" : "Not installed";
        
        console.log(`   ${statusIcon(composeStatus)} podman-compose: ${statusColor(composeStatus ? composeType : 'Not installed', composeStatus)}`);

        if (status.platform === 'darwin' || status.platform === 'win32') {
            console.log(`   ${statusIcon(status.podmanMachine)} Podman machine: ${statusColor(status.podmanMachine ? 'Running' : 'Not running', status.podmanMachine)}`);
        }
    }

    console.log("\n🐍 Python Tools:");
    console.log(`   ${statusIcon(status.python3)} Python 3: ${statusColor(status.python3 ? 'Installed' : 'Not installed', status.python3)}`);
    console.log(`   ${statusIcon(status.pip)} pip3: ${statusColor(status.pip ? 'Installed' : 'Not installed', status.pip)}`);
    console.log(`   ${statusIcon(status.pipx)} pipx: ${statusColor(status.pipx ? 'Installed' : 'Not installed', status.pipx)}`);

    // Show Windows package managers
    if (status.platform === 'win32') {
        console.log("\n📦 Package Managers:");
        console.log(`   ${statusIcon(status.winget)} winget: ${statusColor(status.winget ? 'Available' : 'Not available', status.winget)}`);
        console.log(`   ${statusIcon(status.choco)} chocolatey: ${statusColor(status.choco ? 'Available' : 'Not available', status.choco)}`);
        
        console.log("\n🖥️  Windows Subsystem for Linux:");
        console.log(`   ${statusIcon(status.wsl2)} WSL2: ${statusColor(status.wsl2 ? 'Installed' : 'Not installed', status.wsl2)}`);
        console.log(`   ${statusIcon(status.openssh)} OpenSSH Client: ${statusColor(status.openssh ? 'Installed' : 'Not installed', status.openssh)}`);
    }

    console.log("=".repeat(30));

    return status;
}

async function promptUserForInstallation(question) {
    return new Promise((resolve) => {
        console.log(`\n💭 ${question}`);
        process.stdout.write("   Press (y)es to install automatically, or any other key to see manual instructions: ");
        
        process.stdin.setRawMode(true);
        process.stdin.resume();
        
        const handleKeypress = (key) => {
            const char = key.toString().toLowerCase();
            process.stdin.removeListener('data', handleKeypress);
            process.stdin.pause();
            process.stdin.setRawMode(false);
            
            if (char === 'y') {
                console.log('y\n');
                resolve(true);
            } else {
                if (char !== 'n' && char !== '\r' && char !== '\n' && char !== '\u0003') {
                    console.log(`${char}`);
                } else {
                    console.log('n');
                }
                console.log();
                resolve(false);
            }
        };
        
        process.stdin.on('data', handleKeypress);
    });
}

async function checkPodmanCLI() {
    try {
        // Try different ways to check for podman CLI
        const whichCmd = process.platform === 'win32' ? 'where' : 'which';
        await $`${whichCmd} podman`.quiet();
        
        // Also try to run podman --version to ensure it's working
        await $`podman --version`.quiet();
        return true;
    } catch {
        // On Windows, also try PowerShell which might have different PATH
        if (process.platform === 'win32') {
            try {
                await $`powershell -Command "podman --version"`.quiet();
                return true;
            } catch {
                return false;
            }
        }
        return false;
    }
}

async function installPodmanAutomatically(status) {
    const platform = status.platform;
    const distro = status.distro;

    console.log("\n🔧 AUTOMATIC PODMAN INSTALLATION");
    console.log("=".repeat(40));

    try {
        if (platform === 'win32') {
            console.log("🪟 Installing Podman on Windows using winget...");
            
            try {
                // Use powershell to run winget since Bun might not have access to it directly
                // We need to handle the case where winget returns non-zero exit code for "already installed"
                let result;
                let output = '';
                let exitCode = 0;
                
                try {
                    result = await $`powershell -Command "winget install -e --id RedHat.Podman-Desktop --accept-package-agreements --accept-source-agreements"`;
                    output = result.stdout + result.stderr;
                    exitCode = result.exitCode;
                } catch (cmdError) {
                    // Bun throws an error for non-zero exit codes, but we still want to check the output
                    output = cmdError.stdout + cmdError.stderr;
                    exitCode = cmdError.exitCode || 1;
                }
                
                // Check if installation was successful or if it's already installed
                const isAlreadyInstalled = output.includes("Found an existing package already installed") || 
                                         output.includes("No available upgrade found") ||
                                         output.includes("already installed");
                
                if (exitCode === 0 || isAlreadyInstalled) {
                    if (isAlreadyInstalled) {
                        console.log("   ✅ Podman Desktop is already installed!");
                    } else {
                        console.log("   ✅ Podman Desktop installed successfully via winget!");
                    }
                    console.log("   🖥️  Podman Desktop provides a GUI and handles WSL2 setup automatically.");
                    
                    // Wait a moment for any PATH changes to settle
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Check if podman CLI is now available
                    const podmanAvailable = await checkPodmanCLI();
                    if (podmanAvailable) {
                        console.log("   ✅ Podman CLI is now available!");
                        return true;
                    } else {
                        console.log("   ⚠️  Podman Desktop is installed but CLI is not immediately available in PATH.");
                        console.log("   📝 This is normal on Windows. Please:");
                        console.log("      1. Restart your terminal/PowerShell");
                        console.log("      2. Ensure Podman Desktop is running (check system tray)");
                        console.log("      3. Run this script again");
                        console.log("   💡 If the issue persists:");
                        console.log("      - Check if podman.exe is in your PATH");
                        console.log("      - Usually installed to: %LOCALAPPDATA%\\Microsoft\\WindowsApps\\");
                        console.log("      - Or check Podman Desktop settings for CLI installation");
                        
                        // For already installed cases, this is not a failure - just need terminal restart
                        if (isAlreadyInstalled) {
                            console.log("   🔄 Since Podman Desktop is already installed, please restart your terminal and try again.");
                            console.log("   ℹ️  This is not an error - just restart needed for PATH changes.");
                            return 'restart_needed';
                        }
                        return false;
                    }
                } else {
                    throw new Error(`winget command failed with exit code ${exitCode}: ${output}`);
                }
            } catch (wingetError) {
                console.error("   ❌ winget installation failed:");
                console.error(`      Error: ${wingetError.message}`);
                console.log("   📖 Manual installation required:");
                console.log("      1. Install Podman Desktop: winget install -e --id RedHat.Podman-Desktop");
                console.log("      2. Or download from: https://podman-desktop.io/downloads/windows");
                console.log("      3. Restart your terminal after installation");
                console.log("      4. Run this script again");
                return false;
            }

        } else if (platform === 'darwin') {
            console.log("📱 Installing Podman on macOS...");
            
            // Check if brew is available
            try {
                await $`brew --version`.quiet();
                console.log("   📦 Using Homebrew to install Podman...");
                await $`brew install podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
            } catch (error) {
                throw new Error("Homebrew not available. Please install Homebrew first: https://brew.sh");
            }

        } else if (platform === 'linux') {
            console.log("🐧 Installing Podman on Linux...");
            
            if (distro === 'ubuntu' || distro === 'debian') {
                console.log("   📦 Using apt to install Podman...");
                await $`sudo apt update`;
                await $`sudo apt install -y podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
                
            } else if (distro === 'fedora') {
                console.log("   📦 Using dnf to install Podman...");
                await $`sudo dnf install -y podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
                
            } else if (distro === 'centos' || distro === 'rhel') {
                console.log("   📦 Using yum to install Podman...");
                await $`sudo yum install -y podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
                
            } else if (distro === 'alpine') {
                console.log("   📦 Using apk to install Podman...");
                await $`sudo apk add podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
                
            } else if (distro === 'arch') {
                console.log("   📦 Using pacman to install Podman...");
                await $`sudo pacman -S --noconfirm podman`;
                console.log("   ✅ Podman installed successfully!");
                return true;
                
            } else {
                throw new Error(`Unsupported Linux distribution: ${distro || 'unknown'}`);
            }
        } else {
            throw new Error(`Unsupported platform: ${platform}`);
        }
    } catch (error) {
        console.error(`   ❌ Automatic installation failed: ${error.message}`);
        console.error("   💡 Please try installing manually or check the setup instructions.");
        return false;
    }
}

async function setupPodmanMachine(platform) {
    if (platform === 'darwin' || platform === 'win32') {
        console.log("🔄 Setting up Podman machine...");
        
        try {
            // Check if machine already exists
            const machineList = await $`podman machine list --format json`.quiet();
            const machines = JSON.parse(machineList.stdout);
            
            if (machines.length === 0) {
                console.log("   📝 Initializing Podman machine...");
                await $`podman machine init`;
            }
            
            const runningMachine = machines.find(m => m.Running);
            if (!runningMachine) {
                console.log("   🚀 Starting Podman machine...");
                await $`podman machine start`;
            }
            
            console.log("   ✅ Podman machine is ready!");
            return true;
        } catch (error) {
            console.error(`   ❌ Failed to setup Podman machine: ${error.message}`);
            return false;
        }
    }
    return true;
}

function showConditionalSetupInstructions(status) {
    const platform = status.platform;
    const shell = status.shell;
    const distro = status.distro;

    // Determine what needs to be installed
    const needsPodman = !status.podman && !status.docker;
    const needsPodmanCompose = status.podman && !status.podmanCompose;
    const needsPodmanMachine = status.podman && (platform === 'darwin' || platform === 'win32') && !status.podmanMachine;
    const needsWSL2 = platform === 'win32' && !status.wsl2;
    const needsOpenSSH = platform === 'win32' && status.podman && !status.openssh;

    if (!needsPodman && !needsPodmanCompose && !needsPodmanMachine && !needsWSL2 && !needsOpenSSH) {
        console.log("\n✅ All required components are installed!");
        return;
    }

    console.log("\n🔧 MISSING COMPONENTS - MANUAL SETUP INSTRUCTIONS");
    console.log("=".repeat(50));

    let stepNumber = 1;

    if (platform === 'darwin') {
        console.log("📱 macOS Setup:");

        if (needsPodman) {
            console.log(`   ${stepNumber}. Install Podman:`);
            console.log(`      ${cmdHighlight('brew install podman')}`);
            stepNumber++;
        }

        if (needsPodmanMachine) {
            console.log(`   ${stepNumber}. Initialize and start Podman machine:`);
            console.log(`      ${cmdHighlight('podman machine init')}`);
            console.log(`      ${cmdHighlight('podman machine start')}`);
            stepNumber++;
        }

        if (needsPodmanCompose) {
            console.log(`   ${stepNumber}. Install podman-compose:`);
            console.log(`      ${cmdHighlight('brew install podman-compose')}`);
            stepNumber++;
        }

        if (needsPodman) {
            if (shell === 'zsh') {
                console.log(`   ${stepNumber}. Add to ~/.zshrc (optional, for convenience):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.zshrc")}`);
            } else if (shell === 'bash') {
                console.log(`   ${stepNumber}. Add to ~/.bash_profile (optional, for convenience):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.bash_profile")}`);
            }
        }

    } else if (platform === 'win32') {
        console.log("🪟 Windows Setup:");

        if (needsWSL2) {
            console.log(`   ${stepNumber}. Install WSL2 (Windows Subsystem for Linux):`);
            console.log(`      📦 Open PowerShell as Administrator and run:`);
            console.log(`      ${cmdHighlight('wsl --install')}`);
            console.log("      ⚠️  Restart your computer after installation");
            console.log("      💡 WSL2 is required for Podman to work on Windows");
            stepNumber++;
        }

        if (needsOpenSSH) {
            console.log(`   ${stepNumber}. Install OpenSSH Client:`);
            console.log(`      📦 Open PowerShell as Administrator and run:`);
            console.log(`      ${cmdHighlight('Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0')}`);
            console.log("      💡 OpenSSH Client is required for Podman machine initialization");
            stepNumber++;
        }

        if (needsPodman) {
            console.log(`   ${stepNumber}. Install Podman:`);
            console.log("      📦 Option A - Podman Desktop: https://podman.io/desktop");
            console.log(`      📦 Option B - winget (Windows 10+ with App Installer): ${cmdHighlight('winget install -e --id RedHat.Podman-Desktop')}`);
            console.log(`      📦 Option C - Chocolatey (requires chocolatey): ${cmdHighlight('choco install podman')}`);
            console.log(`      📦 Option D - Scoop (requires scoop): ${cmdHighlight('scoop install podman')}`);
            console.log("      💡 Note: Podman Desktop is the easiest option for most users");
            stepNumber++;
        }

        if (needsPodmanMachine) {
            console.log(`   ${stepNumber}. Initialize Podman machine:`);
            console.log(`      ${cmdHighlight('podman machine init')}`);
            console.log(`      ${cmdHighlight('podman machine start')}`);
            stepNumber++;
        }

        if (needsPodmanCompose) {
            console.log(`   ${stepNumber}. Install podman-compose:`);
            console.log(`      ${cmdHighlight('pip install --user podman-compose')}`);
            console.log("      📦 Alternative (if pip fails):");
            console.log(`         ${cmdHighlight('python3 -m venv ~/.local/podman-env')}`);
            console.log(`         ${cmdHighlight('~/.local/podman-env/bin/pip install podman-compose')}`);
        }

    } else if (platform === 'linux') {
        console.log("🐧 Linux Setup:");

        if (needsPodman) {
            if (distro === 'ubuntu' || distro === 'debian') {
                console.log("   📦 Ubuntu/Debian:");
                console.log(`   ${stepNumber}. Update and install Podman:`);
                console.log(`      ${cmdHighlight('sudo apt update')}`);
                console.log(`      ${cmdHighlight('sudo apt install -y podman')}`);
                stepNumber++;
            } else if (distro === 'fedora') {
                console.log("   📦 Fedora:");
                console.log(`   ${stepNumber}. Install Podman:`);
                console.log(`      ${cmdHighlight('sudo dnf install -y podman')}`);
                stepNumber++;
            } else if (distro === 'centos' || distro === 'rhel') {
                console.log("   📦 CentOS/RHEL:");
                console.log(`   ${stepNumber}. Install Podman:`);
                console.log(`      ${cmdHighlight('sudo yum install -y podman')}`);
                stepNumber++;
            } else if (distro === 'alpine') {
                console.log("   📦 Alpine Linux:");
                console.log(`   ${stepNumber}. Install Podman:`);
                console.log(`      ${cmdHighlight('sudo apk add podman')}`);
                stepNumber++;
            } else if (distro === 'arch') {
                console.log("   📦 Arch Linux:");
                console.log(`   ${stepNumber}. Install Podman:`);
                console.log(`      ${cmdHighlight('sudo pacman -S podman')}`);
                stepNumber++;
            } else {
                console.log("   📦 Generic Linux:");
                console.log(`   ${stepNumber}. Install Podman (check your package manager):`);
                console.log(`      • apt (Ubuntu/Debian): ${cmdHighlight('sudo apt install podman')}`);
                console.log(`      • dnf (Fedora): ${cmdHighlight('sudo dnf install podman')}`);
                console.log(`      • yum (CentOS/RHEL): ${cmdHighlight('sudo yum install podman')}`);
                console.log(`      • pacman (Arch): ${cmdHighlight('sudo pacman -S podman')}`);
                console.log(`      • apk (Alpine): ${cmdHighlight('sudo apk add podman')}`);
                stepNumber++;
            }
        }

        if (needsPodmanCompose) {
            console.log(`   ${stepNumber}. Install podman-compose (choose best option):`);

            if (distro === 'ubuntu' || distro === 'debian') {
                console.log("      📦 Option A - System package (recommended):");
                console.log(`         ${cmdHighlight('sudo apt install -y podman-compose')}`);
                console.log("      📦 Option B - Using pipx:");
                console.log(`         ${cmdHighlight('sudo apt install -y pipx')}`);
                console.log(`         ${cmdHighlight('pipx install podman-compose')}`);
                console.log("      📦 Option C - Virtual environment:");
                console.log(`         ${cmdHighlight('python3 -m venv ~/.local/podman-env')}`);
                console.log(`         ${cmdHighlight('~/.local/podman-env/bin/pip install podman-compose')}`);
                console.log(`         ${cmdHighlight('sudo ln -sf ~/.local/podman-env/bin/podman-compose /usr/local/bin/')}`);
            } else if (distro === 'fedora') {
                console.log(`      ${cmdHighlight('sudo dnf install -y podman-compose')}`);
                console.log("      📦 Alternative:");
                console.log(`         ${cmdHighlight('sudo dnf install -y pipx && pipx install podman-compose')}`);
            } else if (distro === 'arch') {
                console.log(`      ${cmdHighlight('sudo pacman -S podman-compose')}`);
            } else {
                console.log("      📦 Virtual environment (universal method):");
                console.log(`         ${cmdHighlight('python3 -m venv ~/.local/podman-env')}`);
                console.log(`         ${cmdHighlight('~/.local/podman-env/bin/pip install podman-compose')}`);
                console.log(`         ${cmdHighlight('sudo ln -sf ~/.local/podman-env/bin/podman-compose /usr/local/bin/')}`);
            }
            stepNumber++;
        }

        if (needsPodman) {
            console.log(`   ${stepNumber}. Configure rootless containers:`);
            console.log(`      ${cmdHighlight('podman system migrate')}`);
            stepNumber++;

            if (shell === 'zsh') {
                console.log(`   ${stepNumber}. Add alias (optional):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.zshrc && source ~/.zshrc")}`);
            } else if (shell === 'bash') {
                console.log(`   ${stepNumber}. Add alias (optional):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.bashrc && source ~/.bashrc")}`);
            } else if (shell === 'fish') {
                console.log(`   ${stepNumber}. Add alias (optional):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.config/fish/config.fish")}`);
            } else if (shell === 'ash') {
                console.log(`   ${stepNumber}. Add alias (optional):`);
                console.log(`      ${cmdHighlight("echo 'alias docker=podman' >> ~/.profile && source ~/.profile")}`);
            }
        }
    }

    console.log("\n🔄 After installation, run:");
    console.log(`   ${cmdHighlight('bun start')}`);
    console.log("\n💡 Alternative: Install Docker instead:");
    console.log("   • Docker Desktop: https://docker.com/");
    console.log("   • Docker Engine: https://docs.docker.com/engine/install/");
    console.log("=".repeat(50));
}

async function checkAndInstallPodmanRequirements() {
    try {
        // Get system status first
        const status = await checkSystemStatus();
        displaySystemStatus(status);

        if (!status.podman && !status.docker) {
            console.error("❌ Neither Podman nor Docker is available.");
            
            // On Windows, check WSL2 first (required for Podman)
            if (status.platform === 'win32') {
                if (!status.wsl2) {
                    console.log("🖥️  WSL2 is required for Podman on Windows but is not installed.");
                    
                    const shouldInstallWSL = await promptUserForInstallation("Would you like to install WSL2 automatically?");
                    
                    if (shouldInstallWSL) {
                        const wslInstallResult = await installWSL2();
                        
                        if (wslInstallResult === 'restart_required') {
                            console.log("\n🔄 SYSTEM RESTART REQUIRED");
                            console.log("=".repeat(35));
                            console.log("✅ WSL2 installation initiated!");
                            console.log("📝 To complete the installation:");
                            console.log("   1. Restart your computer now");
                            console.log("   2. After restart, open PowerShell as Administrator");
                            console.log("   3. Run: wsl --install (if prompted)");
                            console.log("   4. Then navigate back to this directory and run: bun start");
                            console.log("\n💡 WSL2 requires a system restart to complete installation.");
                            console.log("   After restart, Podman will be able to use WSL2 as its backend.");
                            process.exit(0);
                        } else if (wslInstallResult === false) {
                            console.error("❌ Failed to install WSL2.");
                            console.log("📝 To install WSL2 manually:");
                            console.log("   1. Open PowerShell as Administrator");
                            console.log("   💡 Run: wsl --install");
                            console.log("   2. Restart your computer");
                            console.log("   3. Run this script again: bun start");
                            process.exit(1);
                        }
                        // If wslInstallResult === true, WSL2 was already installed, continue
                    } else {
                        console.log("📝 WSL2 installation is required for Podman on Windows.");
                        console.log("   To install manually:");
                        console.log("   1. Open PowerShell as Administrator");
                        console.log("   💡 Run: wsl --install");
                        console.log("   2. Restart your computer");
                        console.log("   3. Run this script again: bun start");
                        process.exit(1);
                    }
                }

                // Check and install OpenSSH if needed
                if (!status.openssh) {
                    console.log("🔑 OpenSSH Client is required for Podman machine but is not installed.");
                    
                    const shouldInstallOpenSSH = await promptUserForInstallation("Would you like to install OpenSSH Client automatically?");
                    
                    if (shouldInstallOpenSSH) {
                        const opensshInstallResult = await installOpenSSH();
                        
                        if (!opensshInstallResult) {
                            console.error("❌ Failed to install OpenSSH Client.");
                            console.log("📝 To install OpenSSH manually:");
                            console.log("   1. Open PowerShell as Administrator");
                            console.log("   💡 Run: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0");
                            console.log("   2. Or install via Settings > Apps > Optional Features > OpenSSH Client");
                            console.log("   3. Run this script again: bun start");
                            process.exit(1);
                        }
                    } else {
                        console.log("📝 OpenSSH Client installation is required for Podman machine on Windows.");
                        console.log("   To install manually:");
                        console.log("   1. Open PowerShell as Administrator");
                        console.log("   💡 Run: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0");
                        console.log("   2. Or install via Settings > Apps > Optional Features > OpenSSH Client");
                        console.log("   3. Run this script again: bun start");
                        process.exit(1);
                    }
                }
                
                // Continue with Podman checks for Windows
                const hasDesktop = await checkForPodmanDesktop();
                if (hasDesktop) {
                    console.log("🖥️  Podman Desktop detected but CLI is missing.");
                    
                    const shouldInstallCLI = await promptUserForInstallation("Would you like to install the Podman CLI automatically?");
                    
                    if (shouldInstallCLI) {
                        const cliInstallResult = await installPodmanCLI();
                        
                        if (cliInstallResult === true) {
                            // Re-check status after CLI installation
                            console.log("🔄 Checking if Podman CLI is now available...");
                            const newStatus = await checkSystemStatus();
                            
                            if (newStatus.podman) {
                                console.log("✅ Podman CLI installation successful!");
                                // Update status for further checks
                                Object.assign(status, newStatus);
                                // Continue with normal flow
                            } else {
                                console.error("❌ CLI installation completed but Podman is still not available.");
                                console.error("   🔄 Please restart your terminal and try again.");
                                process.exit(1);
                            }
                        } else if (cliInstallResult === 'restart_needed') {
                            console.log("\n🔄 RESTART REQUIRED");
                            console.log("=".repeat(30));
                            console.log("✅ Podman CLI has been installed!");
                            console.log("📝 To continue, please:");
                            console.log("   1. Close this terminal completely");
                            console.log("   2. Open a new PowerShell or Command Prompt");
                            console.log("   3. Navigate back to this directory");
                            console.log("   4. Run: bun start");
                            console.log("\n💡 This restart is needed for the Podman CLI to be available in your PATH.");
                            process.exit(0);
                        } else {
                            console.error("❌ Failed to install Podman CLI.");
                            showConditionalSetupInstructions(status);
                            process.exit(1);
                        }
                    } else {
                        showConditionalSetupInstructions(status);
                        process.exit(1);
                    }
                } else {
                    // No Podman Desktop found, proceed with normal installation
                    const shouldInstall = await promptUserForInstallation("Would you like to install Podman automatically?");
                    
                    if (shouldInstall) {
                        const installSuccess = await installPodmanAutomatically(status);
                        // ... continue with existing logic
                        await handlePodmanInstallationResult(installSuccess, status);
                    } else {
                        showConditionalSetupInstructions(status);
                        process.exit(1);
                    }
                }
            } else {
                // Non-Windows platform, proceed with normal installation
                const shouldInstall = await promptUserForInstallation("Would you like to install Podman automatically?");
                
                if (shouldInstall) {
                    const installSuccess = await installPodmanAutomatically(status);
                    await handlePodmanInstallationResult(installSuccess, status);
                } else {
                    showConditionalSetupInstructions(status);
                    process.exit(1);
                }
            }
        }

        const engine = status.podman ? "podman" : "docker";
        console.log(`✅ Using ${engine} as container engine`);

        // If using Podman, check for additional requirements
        if (status.podman) {
            await checkPodmanRequirements(status);
        }

        // Test the compose functionality
        await $`bun run compose --version`.quiet();
    } catch (error) {
        console.error("❌ Container runtime is not available.");
        console.error("   Please make sure your container runtime is installed and running.");
        process.exit(1);
    }
}

async function checkPodmanRequirements(status) {
    console.log("🔍 Checking Podman requirements...");

    // Check if podman compose is available (modern built-in version)
    let hasModernCompose = false;
    try {
        await $`podman compose version`.quiet();
        hasModernCompose = true;
        console.log("✅ Built-in podman compose is available");
    } catch {
        hasModernCompose = false;
    }

    // Only try to install legacy podman-compose if modern version is not available
    if (!status.podmanCompose && !hasModernCompose) {
        console.log("⚠️  podman-compose not found, installing automatically...");
        const platform = status.platform;
        
        if (platform === 'win32') {
            // First ensure Python is installed
            let pythonInstalled = false;
            try {
                await $`powershell -Command "python --version"`.quiet();
                pythonInstalled = true;
                console.log("✅ Python is already installed");
            } catch {
                console.log("📦 Python not found, installing via winget...");
                try {
                    await $`powershell -Command "winget install -e --id Python.Python.3.12 --accept-package-agreements --accept-source-agreements"`;
                    console.log("✅ Python installed successfully!");
                    console.log("   ⚠️  You may need to restart your terminal for PATH changes to take effect.");
                    
                    // Wait a moment for installation to complete
                    await new Promise(resolve => setTimeout(resolve, 3000));
                    
                    // Try to verify Python is now available
                    try {
                        await $`powershell -Command "python --version"`.quiet();
                        pythonInstalled = true;
                        console.log("✅ Python is now available!");
                    } catch {
                        console.log("⚠️  Python installed but not immediately available in PATH.");
                        console.log("   Please restart your terminal and run the script again.");
                        pythonInstalled = false;
                    }
                } catch (error) {
                    console.log("❌ Failed to install Python automatically");
                    console.log("   Please install Python manually from: https://python.org/downloads");
                    console.log("   Make sure to check 'Add Python to PATH' during installation");
                    pythonInstalled = false;
                }
            }
            
            if (pythonInstalled) {
                // Windows - try multiple python/pip combinations using PowerShell
                const pythonCommands = [
                    'pip3 install podman-compose',
                    'pip install podman-compose', 
                    'python3 -m pip install podman-compose',
                    'python -m pip install podman-compose'
                ];
                let installSuccess = false;
                
                for (const cmd of pythonCommands) {
                    try {
                        console.log(`   📦 Trying: ${cmd}`);
                        await $`powershell -Command "${cmd}"`;
                        console.log("✅ podman-compose installed successfully!");
                        installSuccess = true;
                        break;
                    } catch (error) {
                        console.log(`   ❌ ${cmd} failed, trying next option...`);
                    }
                }
                
                if (!installSuccess) {
                    console.log("⚠️  Could not install podman-compose via pip.");
                    console.log("   💡 This is okay - using built-in podman compose functionality.");
                    console.log("   📝 If you encounter issues, try restarting your terminal after Python installation.");
                }
            } else {
                console.log("⚠️  Could not install Python automatically.");
                console.log("   💡 This is okay - using built-in podman compose functionality.");
                console.log("   📝 To install podman-compose later:");
                console.log("      1. Install Python from: https://python.org/downloads");
                console.log("      2. Restart your terminal");
                console.log("      3. Run: pip install podman-compose");
            }
        } else if (platform === 'darwin') {
            // macOS with Homebrew
            try {
                await $`brew install podman-compose`;
                console.log("✅ podman-compose installed via Homebrew");
            } catch (error) {
                console.log("⚠️  Could not install podman-compose via Homebrew.");
                console.log("   💡 This is okay - using built-in podman compose functionality.");
            }
        } else if (platform === 'linux') {
            // Try pip3 first (most common way)
            try {
                await $`pip3 install podman-compose`;
                console.log("✅ podman-compose installed via pip3");
            } catch {
                console.log("⚠️  Could not install podman-compose via pip3.");
                console.log("   💡 This is okay - using built-in podman compose functionality.");
            }
        }
    }

    // Check if podman machine is initialized (macOS/Windows)
    const platform = process.platform;
    if (platform === 'darwin' || platform === 'win32') {
        try {
            const machineList = await $`podman machine list --format json`.quiet();
            const machines = JSON.parse(machineList.stdout);

            let hasRunningMachine = false;
            for (const machine of machines) {
                if (machine.Running) {
                    hasRunningMachine = true;
                    break;
                }
            }

            if (!hasRunningMachine) {
                console.log("🔄 Starting Podman machine...");

                // Check if there's a machine to start
                if (machines.length === 0) {
                    console.log("📝 Initializing Podman machine...");
                    try {
                        await $`podman machine init`;
                    } catch (initError) {
                        console.error("❌ Failed to initialize Podman machine:");
                        console.error(`   Error: ${initError.message}`);
                        
                        // Check for specific SSH-related errors
                        if (initError.message.includes('ssh-keygen') || initError.message.includes('ssh')) {
                            console.log("💡 This error is likely due to missing OpenSSH Client.");
                            console.log("   To fix this:");
                            console.log("   1. Install OpenSSH Client: Run as Administrator:");
                            console.log("      Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0");
                            console.log("   2. Or install via Settings > Apps > Optional Features > OpenSSH Client");
                            console.log("   3. Restart your terminal and run: bun start");
                        }
                        throw initError; // Re-throw to be caught by outer try-catch
                    }
                }

                try {
                    await $`podman machine start`;
                    console.log("✅ Podman machine started");
                } catch (startError) {
                    console.error("❌ Failed to start Podman machine:");
                    console.error(`   Error: ${startError.message}`);
                    throw startError; // Re-throw to be caught by outer try-catch
                }
            }
        } catch (error) {
            console.log("⚠️  Could not check/start Podman machine:");
            console.log(`   Error: ${error.message}`);
            
            // Check for SSH-related errors
            if (error.message.includes('ssh-keygen') || error.message.includes('ssh')) {
                console.log("💡 This appears to be an OpenSSH-related issue.");
                console.log("   OpenSSH Client is required for Podman machine on Windows.");
                console.log("   Run this script again - it will detect and install OpenSSH automatically.");
                console.log("   Or install manually:");
                console.log("   1. Open PowerShell as Administrator");
                console.log("   2. Run: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0");
                console.log("   3. Restart your terminal and run: bun start");
            } else {
                console.log("   Continuing anyway...");
            }
        }
    }

    // Check for rootless containers configuration on Linux
    if (platform === 'linux') {
        try {
            await $`podman info --format "{{.Host.Security.Rootless}}"`.quiet();
        } catch (error) {
            console.log("⚠️  Podman rootless configuration might need setup");
            console.log("   Run: podman system migrate");
            console.log("   Or check: https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md");
        }
    }

    // Ensure the override file exists
    const overrideFile = 'docker/podman.override.yml';
    if (!existsSync(overrideFile)) {
        console.log("📝 Creating Podman override configuration...");
        const overrideContent = `# Podman-specific overrides
# This file is automatically created for Podman compatibility
services:
  app:
    security_opt:
      - label=disable
  nginx:
    security_opt:
      - label=disable
  db:
    security_opt:
      - label=disable
  mailhog:
    security_opt:
      - label=disable
  phpmyadmin:
    security_opt:
      - label=disable
`;
        writeFileSync(overrideFile, overrideContent);
        console.log(`✅ Created ${overrideFile}`);
    }
}

async function ensureNetworkExists() {
    try {
        // Network will be created by compose if it doesn't exist
        // No need to check manually as compose handles this
    } catch (error) {
        // Network doesn't exist, it will be created by compose
    }
}

async function installPodmanCLI() {
    console.log("🔧 Installing Podman CLI...");
    
    try {
        let output = '';
        let exitCode = 0;
        
        try {
            const result = await $`powershell -Command "winget install -e --id RedHat.Podman --accept-package-agreements --accept-source-agreements"`;
            output = result.stdout + result.stderr;
            exitCode = result.exitCode;
        } catch (cmdError) {
            // Handle non-zero exit codes
            output = cmdError.stdout + cmdError.stderr;
            exitCode = cmdError.exitCode || 1;
        }
        
        // Check if installation was successful or if it's already installed
        const isAlreadyInstalled = output.includes("Found an existing package already installed") || 
                                 output.includes("No available upgrade found") ||
                                 output.includes("already installed");
        
        if (exitCode === 0 || isAlreadyInstalled) {
            if (isAlreadyInstalled) {
                console.log("   ✅ Podman CLI is already installed!");
            } else {
                console.log("   ✅ Podman CLI installed successfully!");
            }
            
            // Wait a moment for PATH changes to settle
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Check if podman CLI is now available
            const podmanAvailable = await checkPodmanCLI();
            if (podmanAvailable) {
                console.log("   ✅ Podman CLI is now available!");
                return true;
            } else {
                console.log("   ⚠️  Podman CLI installed but not immediately available in PATH.");
                console.log("   🔄 Please restart your terminal and try again.");
                return 'restart_needed';
            }
        } else {
            throw new Error(`winget command failed with exit code ${exitCode}: ${output}`);
        }
    } catch (error) {
        console.error("   ❌ Failed to install Podman CLI:");
        console.error(`      Error: ${error.message}`);
        console.log("   📖 Manual installation required:");
        console.log("      1. Install Podman CLI: winget install -e --id RedHat.Podman");
        console.log("      2. Or enable CLI in Podman Desktop settings");
        console.log("      3. Restart your terminal after installation");
        return false;
    }
}

async function checkForPodmanDesktop() {
    try {
        // Check if Podman Desktop executable exists
        const desktopPath = "C:\\Users\\User\\AppData\\Local\\Programs\\podman-desktop\\Podman Desktop.exe";
        const fs = await import('fs');
        
        if (fs.existsSync(desktopPath)) {
            console.log("🖥️  Podman Desktop found but CLI is missing");
            return true;
        }
        
        // Also check if it's installed via winget
        try {
            const result = await $`powershell -Command "winget list --id RedHat.Podman-Desktop"`.quiet();
            const output = result.stdout;
            if (output.includes("RedHat.Podman-Desktop")) {
                console.log("🖥️  Podman Desktop found via winget but CLI is missing");
                return true;
            }
        } catch {
            // Ignore error, continue with other checks
        }
        
        return false;
    } catch (error) {
        return false;
    }
}

async function handlePodmanInstallationResult(installSuccess, status) {
    if (installSuccess === true) {
        // Re-check status after installation
        console.log("🔄 Checking if Podman is now available...");
        const newStatus = await checkSystemStatus();
        
        if (newStatus.podman) {
            console.log("✅ Podman installation successful!");
            
            // Setup podman machine if needed
            await setupPodmanMachine(newStatus.platform);
            
            // Update status for further checks
            Object.assign(status, newStatus);
        } else {
            console.error("❌ Installation completed but Podman is still not available.");
            
            // Special handling for Windows where Podman Desktop might be installed but CLI isn't in PATH
            if (status.platform === 'win32') {
                console.error("   🪟 Podman Desktop might be installed but CLI not accessible.");
                console.error("   🔄 Please try the following:");
                console.error("      1. Close this terminal completely");
                console.error("      2. Restart Podman Desktop from Start Menu");
                console.error("      3. Open a new PowerShell/Command Prompt as Administrator");
                console.error("      4. Run: podman --version (to verify CLI is available)");
                console.error("      5. If step 4 works, run: bun start");
                console.error("\n   💡 Common Windows issues:");
                console.error("      - PATH changes require terminal restart");
                console.error("      - WSL2 backend might need to be started");
                console.error("      - Podman Desktop app needs to be running");
            } else {
                console.error("   🔄 Please restart your terminal and try again.");
            }
            
            console.error("\n   📋 Manual verification steps:");
            console.error("      1. Check if Podman Desktop is running (system tray/applications)");
            console.error("      2. Verify CLI installation in Podman Desktop settings");
            console.error("      3. Check PATH environment variable");
            console.error("\n   If the issue persists, try the manual installation instructions below:");
            showConditionalSetupInstructions(status);
            process.exit(1);
        }
    } else if (installSuccess === 'restart_needed') {
        console.log("\n🔄 RESTART REQUIRED");
        console.log("=".repeat(30));
        console.log("✅ Podman Desktop is already installed on your system!");
        console.log("📝 To continue, please:");
        console.log("   1. Close this terminal completely");
        console.log("   2. Open a new PowerShell or Command Prompt");
        console.log("   3. Navigate back to this directory");
        console.log("   4. Run: bun start");
        console.log("\n💡 This restart is needed for the Podman CLI to be available in your PATH.");
        console.log("   After restart, the setup should continue automatically.");
        process.exit(0); // Exit cleanly, not with error
    } else {
        console.error("❌ Automatic installation failed.");
        showConditionalSetupInstructions(status);
        process.exit(1);
    }
}

async function installWSL2() {
    console.log("🔧 Installing WSL2 (Windows Subsystem for Linux)...");
    
    try {
        // First check if WSL2 is already installed but not detected
        try {
            await $`wsl --status`.quiet();
            console.log("   ✅ WSL2 is already installed!");
            return true;
        } catch {
            // WSL2 not installed, proceed with installation
        }
        
        console.log("   📦 Running WSL2 installation command...");
        console.log("   ⚠️  This may take several minutes and requires administrator privileges.");
        
        try {
            // Use wsl --install which is the modern way to install WSL2
            const result = await $`powershell -Command "Start-Process wsl -ArgumentList '--install' -Verb RunAs -Wait"`;
            console.log("   ✅ WSL2 installation command completed!");
            
            // Check if WSL2 is now available
            try {
                await $`wsl --status`.quiet();
                console.log("   ✅ WSL2 is now available!");
                return true;
            } catch {
                // WSL2 installed but requires restart
                console.log("   ⚠️  WSL2 installation completed but requires a system restart.");
                return 'restart_required';
            }
        } catch (adminError) {
            // Try alternative method without elevated privileges
            console.log("   ⚠️  Elevated installation failed, trying alternative method...");
            
            try {
                const result = await $`wsl --install`;
                console.log("   ✅ WSL2 installation initiated!");
                
                // Check if WSL2 is now available
                try {
                    await $`wsl --status`.quiet();
                    console.log("   ✅ WSL2 is now available!");
                    return true;
                } catch {
                    // WSL2 installation requires restart
                    console.log("   ⚠️  WSL2 installation completed but requires a system restart.");
                    return 'restart_required';
                }
            } catch (basicError) {
                console.error("   ❌ Failed to install WSL2:");
                console.error(`      Error: ${basicError.message}`);
                return false;
            }
        }
    } catch (error) {
        console.error("   ❌ WSL2 installation failed:");
        console.error(`      Error: ${error.message}`);
        console.log("   📖 Manual installation required:");
        console.log("      1. Open PowerShell as Administrator");
        console.log("      2. Run: wsl --install");
        console.log("      3. Restart your computer");
        console.log("      4. Run this script again");
        return false;
    }
}

async function installOpenSSH() {
    console.log("🔧 Installing OpenSSH Client...");
    
    try {
        // First check if OpenSSH is already installed but not detected
        try {
            await $`ssh-keygen -V`.quiet();
            console.log("   ✅ OpenSSH Client is already installed!");
            return true;
        } catch {
            // OpenSSH not installed, proceed with installation
        }
        
        console.log("   📦 Installing OpenSSH Client via Windows capability...");
        
        try {
            // Use Add-WindowsCapability to install OpenSSH Client
            const result = await $`powershell -Command "Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0"}`;
            console.log("   ✅ OpenSSH Client installation completed!");
            
            // Check if OpenSSH is now available
            try {
                await $`ssh-keygen -V`.quiet();
                console.log("   ✅ OpenSSH Client is now available!");
                return true;
            } catch {
                console.log("   ⚠️  OpenSSH installed but may need a terminal restart to be available in PATH.");
                console.log("   🔄 Please restart your terminal and try again if you encounter issues.");
                return true; // Consider it successful, just needs restart
            }
        } catch (capabilityError) {
            // Try alternative method with winget
            console.log("   ⚠️  Windows capability installation failed, trying winget...");
            
            try {
                const result = await $`powershell -Command "winget install Microsoft.OpenSSH.Beta --accept-package-agreements --accept-source-agreements"}`;
                console.log("   ✅ OpenSSH installed via winget!");
                
                // Check if OpenSSH is now available
                try {
                    await $`ssh-keygen -V`.quiet();
                    console.log("   ✅ OpenSSH Client is now available!");
                    return true;
                } catch {
                    console.log("   ⚠️  OpenSSH installed but may need a terminal restart.");
                    return true;
                }
            } catch (wingetError) {
                console.error("   ❌ Failed to install OpenSSH via both methods:");
                console.error(`      Capability error: ${capabilityError.message}`);
                console.error(`      Winget error: ${wingetError.message}`);
                return false;
            }
        }
    } catch (error) {
        console.error("   ❌ OpenSSH installation failed:");
        console.error(`      Error: ${error.message}`);
        console.log("   📖 Manual installation required:");
        console.log("      1. Open PowerShell as Administrator");
        console.log("      2. Run: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0");
        console.log("      3. Or install via Settings > Apps > Optional Features");
        console.log("      4. Restart your terminal");
        return false;
    }
}

(async () => {
    try {
        console.log("🔍 Checking container runtime availability...");
        await checkAndInstallPodmanRequirements();

        console.log("🔍 Checking for file changes...");
        const changedServices = getChangedServices();

        if (changedServices.length > 0) {
            await buildSpecificServices(changedServices);
        }

        await ensureNetworkExists();
        await up();
        await showStatus();

        console.log("📦 Installing PHP dependencies...");
        await $`bun run composer install`;

        console.log("\n✅ Kriit is running!");
        console.log("   - App: http://localhost:8080");
        console.log("   - phpMyAdmin: http://localhost:8081");
        console.log("   - MailHog: http://localhost:8025");
        console.log("\n💡 Run 'bun logs' to see container logs");

        if (changedServices.length === 0) {
            console.log("⚡ Startup was fast because no Docker images needed rebuilding!");
        }

    } catch (error) {
        console.error("❌ Failed to start:", error.message);
        process.exit(1);
    }
})();