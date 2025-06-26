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

async function checkAndInstallPodmanRequirements() {
    try {
        // Check if podman is available first, then docker
        const hasPodman = await $`command -v podman`.quiet().then(() => true).catch(() => false);
        const hasDocker = await $`command -v docker`.quiet().then(() => true).catch(() => false);
        
        if (!hasPodman && !hasDocker) {
            console.error("❌ Neither Podman nor Docker is available.");
            console.error("   Please install either Podman or Docker.");
            
            // Detect OS and suggest installation
            const platform = process.platform;
            if (platform === 'darwin') {
                console.error("   For macOS: brew install podman");
                console.error("   Or install Docker Desktop from https://docker.com/");
            } else if (platform === 'linux') {
                console.error("   For Linux: Check your package manager (apt, yum, dnf, etc.)");
                console.error("   Ubuntu/Debian: sudo apt install podman");
                console.error("   RHEL/Fedora: sudo dnf install podman");
            }
            process.exit(1);
        }
        
        const engine = hasPodman ? "podman" : "docker";
        console.log(`✅ Using ${engine} as container engine`);
        
        // If using Podman, check for additional requirements
        if (hasPodman) {
            await checkPodmanRequirements();
        }
        
        // Test the compose functionality
        await $`bun run compose --version`.quiet();
    } catch (error) {
        console.error("❌ Container runtime is not available.");
        console.error("   Please make sure your container runtime is installed and running.");
        process.exit(1);
    }
}

async function checkPodmanRequirements() {
    console.log("🔍 Checking Podman requirements...");
    
    // Check for podman-compose
    const hasPodmanCompose = await $`command -v podman-compose`.quiet().then(() => true).catch(() => false);
    
    if (!hasPodmanCompose) {
        console.log("⚠️  podman-compose not found, attempting to install...");
        try {
            const platform = process.platform;
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