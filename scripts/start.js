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
        podmanMachine: false,
        pip: false,
        pipx: false,
        python3: false
    };

    // Check container runtimes using which command
    try {
        await $`which podman`.quiet();
        status.podman = true;
    } catch { status.podman = false; }

    try {
        await $`which docker`.quiet();
        status.docker = true;
    } catch { status.docker = false; }

    // Check podman-compose
    try {
        await $`which podman-compose`.quiet();
        status.podmanCompose = true;
    } catch { status.podmanCompose = false; }

    // Check Python tools using which command
    try {
        await $`which python3`.quiet();
        status.python3 = true;
    } catch { status.python3 = false; }

    try {
        await $`which pip3`.quiet();
        status.pip = true;
    } catch { status.pip = false; }

    try {
        await $`which pipx`.quiet();
        status.pipx = true;
    } catch { status.pipx = false; }

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
        console.log(`   ${statusIcon(status.podmanCompose)} podman-compose: ${statusColor(status.podmanCompose ? 'Installed' : 'Not installed', status.podmanCompose)}`);

        if (status.platform === 'darwin' || status.platform === 'win32') {
            console.log(`   ${statusIcon(status.podmanMachine)} Podman machine: ${statusColor(status.podmanMachine ? 'Running' : 'Not running', status.podmanMachine)}`);
        }
    }

    console.log("\n🐍 Python Tools:");
    console.log(`   ${statusIcon(status.python3)} Python 3: ${statusColor(status.python3 ? 'Installed' : 'Not installed', status.python3)}`);
    console.log(`   ${statusIcon(status.pip)} pip3: ${statusColor(status.pip ? 'Installed' : 'Not installed', status.pip)}`);
    console.log(`   ${statusIcon(status.pipx)} pipx: ${statusColor(status.pipx ? 'Installed' : 'Not installed', status.pipx)}`);

    console.log("=".repeat(30));

    return status;
}

function showConditionalSetupInstructions(status) {
    const platform = status.platform;
    const shell = status.shell;
    const distro = status.distro;

    // Determine what needs to be installed
    const needsPodman = !status.podman && !status.docker;
    const needsPodmanCompose = status.podman && !status.podmanCompose;
    const needsPodmanMachine = status.podman && (platform === 'darwin' || platform === 'win32') && !status.podmanMachine;

    if (!needsPodman && !needsPodmanCompose && !needsPodmanMachine) {
        console.log("\n✅ All required components are installed!");
        return;
    }

    console.log("\n🔧 MISSING COMPONENTS - SETUP REQUIRED");
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

        if (needsPodman) {
            console.log(`   ${stepNumber}. Install Podman:`);
            console.log("      📦 Option A - Podman Desktop: https://podman.io/desktop");
            console.log(`      📦 Option B - Chocolatey: ${cmdHighlight('choco install podman')}`);
            console.log(`      📦 Option C - Scoop: ${cmdHighlight('scoop install podman')}`);
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
            showConditionalSetupInstructions(status);
            process.exit(1);
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

    if (!status.podmanCompose) {
        console.log("⚠️  podman-compose not found, attempting to install...");
        try {
            const platform = status.platform;
            if (platform === 'darwin') {
                // macOS with Homebrew
                await $`brew install podman-compose`;
                console.log("✅ podman-compose installed via Homebrew");
            } else if (platform === 'linux') {
                // Try pip3 first (most common way)
                try {
                    await $`pip3 install podman-compose`;
                    console.log("✅ podman-compose installed via pip3");
                } catch {
                    console.error("❌ Failed to install podman-compose via pip3");
                    console.error("   Please install manually: pip3 install podman-compose");
                    console.error("   Or check: https://github.com/containers/podman-compose");
                    process.exit(1);
                }
            }
        } catch (error) {
            console.error("❌ Failed to install podman-compose automatically");
            console.error("   Please install manually:");
            console.error("   - macOS: brew install podman-compose");
            console.error("   - Linux: pip3 install podman-compose");
            process.exit(1);
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
                    await $`podman machine init`;
                }

                await $`podman machine start`;
                console.log("✅ Podman machine started");
            }
        } catch (error) {
            console.log("⚠️  Could not check/start Podman machine, continuing anyway...");
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