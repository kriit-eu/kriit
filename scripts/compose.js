#!/usr/bin/env bun
/**
 * Smart wrapper around “compose”:
 *   • uses Podman + docker/podman.override.yml when `podman` exists
 *   • falls back to Docker otherwise (OrbStack / Docker Desktop on macOS)
 */

import { $ } from "bun";

const args   = Bun.argv.slice(2);          // what the user typed after “bun run compose …”
const podman = await $`command -v podman`.quiet().then(() => true).catch(() => false);
const engine = podman ? "podman" : "docker";

// Compose files: always the main one, plus the override if we’re on Podman
const files = ["-f", "docker/docker-compose.yml"];
if (podman) files.push("-f", "docker/podman.override.yml");

// Handle logs command specially for Podman (doesn't support multi-container logs -f)
if (podman && args[0] === "logs" && args.includes("-f")) {
    const services = ["app", "nginx", "db", "mailhog", "phpmyadmin"];
    const logArgs = args.slice(1).filter(arg => arg !== "-f");
    
    // If specific services requested, use those; otherwise use all services
    const targetServices = logArgs.length > 0 ? logArgs : services;
    
    // Colors for different services
    const colors = {
        app: '\x1b[36m',        // cyan
        nginx: '\x1b[33m',      // yellow
        db: '\x1b[32m',         // green
        mailhog: '\x1b[35m',    // magenta
        phpmyadmin: '\x1b[34m'  // blue
    };
    const reset = '\x1b[0m';
    
    console.log(`Starting logs for: ${targetServices.join(', ')}`);
    
    // Start each service log in background with spawn
    const processes = [];
    for (const service of targetServices) {
        const color = colors[service] || '';
        const prefix = `${color}[${service}]${reset} `;
        
        const proc = Bun.spawn([
            'bash', '-c', 
            `podman-compose ${files.join(' ')} logs -f --tail 50 ${service} 2>&1 | while IFS= read -r line; do echo "${prefix}$line"; done`
        ], {
            stdout: 'inherit',
            stderr: 'inherit'
        });
        
        processes.push(proc);
    }
    
    // Wait for Ctrl+C or any process to exit
    await Promise.race(processes.map(p => p.exited));
    
    // Kill all processes
    processes.forEach(p => p.kill());
} else {
    // exec ⟹ streams stdout/stderr live and exits with the right status code
    if (podman) {
        // Use podman-compose directly to avoid docker-compose fallback
        await $`podman-compose ${files} ${args}`;
    } else {
        // Use Docker Compose
        await $`${engine} compose ${files} ${args}`;
    }
}
