#!/usr/bin/env bun
import { $ } from "bun";
import { unlinkSync, existsSync } from "fs";

const COMPOSE_FILE = "docker/docker-compose.yml";
const CHECKSUMS_FILE = ".docker-checksums.json";

async function cleanBuild() {
    console.log("🛑 Stopping all containers...");
    await $`docker compose -f ${COMPOSE_FILE} down`;

    console.log("🗑️ Removing Docker images...");
    try {
        // Get image names and remove them
        const images = await $`docker images --format "{{.Repository}}:{{.Tag}}" | grep "^kriit/"`.text();
        if (images.trim()) {
            const imageList = images.trim().split('\n');
            for (const image of imageList) {
                console.log(`   Removing ${image}`);
                await $`docker rmi ${image}`.quiet();
            }
        } else {
            console.log("   No Kriit Docker images found");
        }
    } catch (error) {
        console.log("   No images to remove or error occurred:", error.message);
    }

    console.log("🗑️ Cleaning up checksums file...");
    if (existsSync(CHECKSUMS_FILE)) {
        unlinkSync(CHECKSUMS_FILE);
        console.log("✅ Checksums file removed");
    }

    console.log("🧹 Pruning unused Docker resources...");
    await $`docker system prune -f`;

    console.log("🔨 Rebuilding all images from scratch...");
    await $`docker compose -f ${COMPOSE_FILE} build --no-cache`;

    console.log("🚀 Starting containers...");
    await $`docker compose -f ${COMPOSE_FILE} up -d --remove-orphans`;

    console.log("\n📦 Container status:");
    await $`docker compose -f ${COMPOSE_FILE} ps`;

    console.log("\n✅ Kriit is running with fresh images!");
    console.log("   - App: http://localhost:8000");
    console.log("   - phpMyAdmin: http://localhost:8001");
    console.log("   - MailHog: http://localhost:8003");
}

(async () => {
    try {
        await cleanBuild();
    } catch (error) {
        console.error("❌ Failed to clean build:", error.message);
        process.exit(1);
    }
})();