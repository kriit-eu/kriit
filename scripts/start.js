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
        await $`docker compose -f ${COMPOSE_FILE} build ${service}`;
    }
}

async function up() {
    console.log("🚀 Starting containers...");
    await $`docker compose -f ${COMPOSE_FILE} up -d --remove-orphans`;
}

async function showStatus() {
    console.log("\n📦 Container status:");
    await $`docker compose -f ${COMPOSE_FILE} ps`;
}

function checkDockerAvailability() {
    try {
        // Check if docker is available
        $`docker --version`.quiet();
        $`docker compose version`.quiet();
    } catch (error) {
        console.error("❌ Docker or Docker Compose is not available.");
        console.error("   Please make sure Docker is installed and running.");
        process.exit(1);
    }
}

async function ensureNetworkExists() {
    try {
        // Check if the network already exists, create if not
        await $`docker network inspect docker_kriit_net`.quiet();
    } catch (error) {
        // Network doesn't exist, it will be created by docker compose
    }
}

(async () => {
    try {
        console.log("🔍 Checking Docker availability...");
        checkDockerAvailability();

        console.log("🔍 Checking for file changes...");
        const changedServices = getChangedServices();

        if (changedServices.length > 0) {
            await buildSpecificServices(changedServices);
        }

        await ensureNetworkExists();
        await up();
        await showStatus();

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