#!/usr/bin/env bun
import { existsSync } from "fs";
import { join } from "path";
import { confirm } from '@inquirer/prompts';

async function main() {
    console.log("🔧 KRIIT PROJECT SETUP");
    console.log("=".repeat(30));

    const platform = process.platform;
    let distro = null;
    try {
        const fs = require('fs');
        if (platform === 'linux' && fs.existsSync('/etc/os-release')) {
            const osRelease = fs.readFileSync('/etc/os-release', 'utf8');
            if (/alpine/i.test(osRelease)) distro = 'alpine';
            else if (/ubuntu/i.test(osRelease)) distro = 'ubuntu';
            else if (/debian/i.test(osRelease)) distro = 'debian';
            else if (/fedora/i.test(osRelease)) distro = 'fedora';
            else if (/arch/i.test(osRelease)) distro = 'arch';
        }
    } catch {}
    console.log(`🖥️  Platform: ${platform}${distro ? ` (${distro})` : ''}`);

    const isProduction = await confirm({
        message: "Is this a production environment setup?",
        default: true
    });

    if (isProduction && process.getuid && process.getuid() === 0) {
        console.error("❌ ERROR: Running as root in production environment!");
        console.error("   Please run as a normal user (rootless Podman).");
        process.exit(1);
    }

    // pick script
    let setupScript;
    if (platform === 'linux') {
        switch (distro) {
            case 'alpine':  setupScript = 'setup-alpine.sh'; break;
            case 'ubuntu':
            case 'debian':   setupScript = 'setup-debian.sh'; break;
            case 'fedora':   setupScript = 'setup-fedora.sh'; break;
            case 'arch':     setupScript = 'setup-arch.sh'; break;
            default:         setupScript = 'setup-linux.sh';
        }
    } else if (platform === 'darwin') {
        setupScript = 'setup-macos.sh';
    } else {
        console.error(`❌ Unsupported platform: ${platform}`);
        process.exit(1);
    }

    const scriptPath = join(__dirname, 'setup', setupScript);
    if (!existsSync(scriptPath)) {
        console.error(`❌ Setup script not found: ${scriptPath}`);
        process.exit(1);
    }

    console.log(`\n📋 Setup script: ${setupScript}`);
    console.log("   This will:");
    console.log("   • Check/install Podman & tools");
    console.log("   • Configure rootless containers");
    console.log("   • Set up cgroups, subuid/subgid");
    console.log("");

    if (isProduction) {
        console.log("🔒 Production mode: will use doas/sudo for privileged operations\n");
    }

    console.log("🚀 To run now, execute:");
    console.log(`   ${isProduction ? 'PRODUCTION=1 ' : ''}bash ${scriptPath}\n`);

    const runNow = await confirm({
        message: "Run the setup script now?",
        default: true
    });
    if (!runNow) {
        console.log(`\n📝 You can run it later with: ${isProduction ? 'PRODUCTION=1 ' : ''}bash ${scriptPath}`);
        return;
    }

    console.log("\n📝 Starting setup script…\n" + "=".repeat(40));
    try {
        // Inherit stdio so the shell script prints directly to your terminal
        const { execSync } = require('child_process');
        execSync(`bash ${scriptPath}`, {
            stdio: 'inherit',
            env: { ...process.env, ...(isProduction ? { PRODUCTION: '1' } : {}) }
        });
        // **No extra console.log() here**; the shell script’s own summary is the final word.
    } catch {
        console.error("\n❌ Setup failed");
        process.exit(1);
    }
}

if (import.meta.main) {
    main().catch(err => {
        console.error(err);
        process.exit(1);
    });
}
