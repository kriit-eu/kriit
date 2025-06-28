#!/usr/bin/env bun
import { $ } from "bun";
import { unlinkSync, existsSync } from "fs";

const COMPOSE_FILE = "docker/docker-compose.yml";
const CHECKSUMS_FILE = ".docker-checksums.json";

async function forceRebuild() {
    console.log("🗑️ Cleaning up checksums file...");

    // Remove checksums file to force rebuild
    if (existsSync(CHECKSUMS_FILE)) {
        unlinkSync(CHECKSUMS_FILE);
        console.log("✅ Checksums file removed");
    }

    console.log("🔨 Force rebuilding all Docker images...");
    await $`docker compose -f ${COMPOSE_FILE} build --no-cache`;

    console.log("🚀 Starting containers...");
    await $`docker compose -f ${COMPOSE_FILE} up -d --remove-orphans`;

    console.log("\n📦 Container status:");
    await $`docker compose -f ${COMPOSE_FILE} ps`;

    console.log("\n✅ Kriit is running!");
    console.log("   - App: http://localhost:8000");
    console.log("   - phpMyAdmin: http://localhost:8001");
    console.log("   - MailHog: http://localhost:8003");
}

(async () => {
    try {
        await forceRebuild();
    } catch (error) {
        console.error("❌ Failed to force rebuild:", error.message);
        process.exit(1);
    }
})();