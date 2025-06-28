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

    if (services.length === 1) {
        // Single service - build directly
        console.log(`🔨 Building ${services[0]}...`);
        await $`bun run compose build ${services[0]}`;
    } else {
        // Multiple services - build in parallel
        console.log(`🔨 Building ${services.length} services in parallel...`);
        const buildPromises = services.map(service => {
            console.log(`🔨 Starting build for ${service}...`);
            return $`bun run compose build ${service}`;
        });
        
        try {
            await Promise.all(buildPromises);
            console.log("✅ All parallel builds completed successfully!");
        } catch (error) {
            console.error("❌ One or more builds failed:", error.message);
            throw error;
        }
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

function cmdHighlight(cmd) {
    const colors = {
        reset: '\x1b[0m',
        bright: '\x1b[1m',
        cyan: '\x1b[36m'
    };
    return `${colors.bright}${colors.cyan}${cmd}${colors.reset}`;
}

async function checkSystemDependencies() {
    const missing = [];
    
    // Check container runtimes
    let hasContainerRuntime = false;
    try {
        await $`which podman`.quiet();
        hasContainerRuntime = true;
    } catch {
        try {
            await $`which docker`.quiet();
            hasContainerRuntime = true;
        } catch {
            missing.push("container runtime (podman or docker)");
        }
    }
    
    // Check podman-compose if using podman
    if (hasContainerRuntime) {
        try {
            await $`which podman`.quiet();
            try {
                await $`which podman-compose`.quiet();
            } catch {
                missing.push("podman-compose");
            }
        } catch {
            // Using docker, no need for podman-compose
        }
    }
    
    return missing;
}

function showMissingDependencies(missing) {
    console.log("\n❌ MISSING DEPENDENCIES");
    console.log("=".repeat(30));
    
    console.log("The following dependencies are missing:");
    missing.forEach(dep => {
        console.log(`   • ${dep}`);
    });
    
    console.log("\n🔧 SOLUTION:");
    console.log(`   Run: ${cmdHighlight('bun setup')}`);
    console.log("   This will automatically detect your system and guide you through the setup process.");
    
    console.log("\n💡 The setup script will:");
    console.log("   • Detect your operating system");
    console.log("   • Check if this is a production environment");
    console.log("   • Ask for confirmation before making any system changes");
    console.log("   • Install only the necessary dependencies");
    
    console.log("=".repeat(30));
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
        console.log("🔍 Checking system dependencies...");
        
        const missingDeps = await checkSystemDependencies();
        
        if (missingDeps.length > 0) {
            showMissingDependencies(missingDeps);
            process.exit(1);
        }
        
        console.log("✅ All dependencies are available");
        
        // Test the compose functionality
        try {
            await $`bun run compose --version`.quiet();
        } catch (error) {
            console.error("❌ Container compose functionality is not working properly.");
            console.error("   Try running the setup script to fix configuration issues:");
            console.error(`   ${cmdHighlight('bun setup')}`);
            process.exit(1);
        }

        console.log("🔍 Checking for file changes...");
        const changedServices = getChangedServices();

        if (changedServices.length > 0) {
            await buildSpecificServices(changedServices);
        }

        await ensureNetworkExists();
        await up();
        await showStatus();

        console.log("📋 Setting up configuration...");
        if (!existsSync("config.php") && existsSync("config.php.sample")) {
            await $`cp config.php.sample config.php`;
            console.log("✅ Created config.php from config.php.sample");
        } else if (existsSync("config.php")) {
            console.log("✅ config.php already exists");
        } else {
            console.log("⚠️  Neither config.php nor config.php.sample found");
        }

        console.log("📦 Installing PHP dependencies...");
        await $`bun run composer install`;

        console.log("\n✅ Kriit is running!");
        console.log("   - App: http://localhost:8000");
        console.log("   - phpMyAdmin: http://localhost:8001");
        console.log("   - MailHog: http://localhost:8003");
        console.log("\n💡 Run 'bun logs' to see container logs");

        if (changedServices.length === 0) {
            console.log("⚡ Startup was fast because no Docker images needed rebuilding!");
        }

    } catch (error) {
        console.error("❌ Failed to start:", error.message);
        console.error("\n🔧 If you're missing dependencies, try running:");
        console.error(`   ${cmdHighlight('bun setup')}`);
        process.exit(1);
    }
})();