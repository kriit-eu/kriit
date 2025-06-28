# Kriit Setup Scripts

This directory contains platform-specific setup scripts that prepare systems for running the Kriit application with containerized services.

## Overview

The setup scripts automate the installation and configuration of container runtimes (Podman/Docker) and related dependencies needed to run Kriit's multi-container application stack.

## Architecture

- **Main entry point**: `../setup.js` - Detects platform and delegates to appropriate script
- **Platform scripts**: `setup-{platform}.js` - Platform-specific installation logic
- **Revert scripts**: `revert-{platform}.js` - Safe removal of installed components
- **Install tracking**: `.kriit-setup-log.json` - Tracks what was installed for safe reverting

## Core Principles

### ✅ What Setup Scripts MUST Do

1. **Detect existing installations**: Check if packages are already installed before attempting installation
2. **Track installations**: Log only packages that weren't previously installed to `.kriit-setup-log.json`
3. **Production safety**: Use secure privilege escalation (`doas` on Alpine, `sudo` on others)
4. **User consent**: Ask for explicit confirmation before making system changes
5. **Environment detection**: Automatically detect OS/distribution and use appropriate package manager
6. **Graceful failure**: Handle authentication failures and missing dependencies properly
7. **Clear feedback**: Provide detailed progress information and error messages
8. **Rootless containers**: Configure container runtimes for rootless operation when possible

### ❌ What Setup Scripts MUST NOT Do

1. **Never overwrite existing configurations**: Don't modify configs that already exist
2. **Never assume root access**: Always use privilege escalation tools, never require running as root
3. **Never install without permission**: Always ask before installing packages or changing system state
4. **Never break existing setups**: Preserve pre-existing package installations and configurations
5. **Never hardcode credentials**: No embedded passwords, keys, or secrets
6. **Never bypass package managers**: Use official repositories and package managers only
7. **Never leave partial states**: Either complete successfully or revert changes
8. **Never ignore errors**: Properly handle and report all failure conditions

## Platform-Specific Requirements

### Alpine Linux (`setup-alpine.sh`)
- Use `apk` package manager
- Support `doas` for privilege escalation
- Configure subuid/subgid for rootless Podman
- Start cgroups service
- Install: podman, python3, py3-pip, podman-compose, iptables

### Future Platforms
- Debian/Ubuntu: Use `apt`, support `sudo`
- Fedora: Use `dnf`, support `sudo`
- Arch: Use `pacman`, support `sudo`

## Revert Scripts

### Safety Requirements
1. **Install log dependency**: Must refuse to run without `.kriit-setup-log.json`
2. **Selective removal**: Only remove packages that were installed by setup
3. **Pre-existing protection**: Never remove packages that existed before setup
4. **Configuration cleanup**: Revert only configurations that were added by setup
5. **Data preservation**: Warn about container/image removal before proceeding

### Naming Convention
- `revert-{platform}.js` - Matches corresponding setup script
- Platform-specific to handle different package managers and configurations

## Error Handling

### Authentication Failures
- Detect failed privilege escalation attempts
- Provide clear error messages
- Exit gracefully without false success indicators
- Handle TTY interaction issues (double Enter requirements)

### Package Installation Failures
- Check individual package installation results
- Continue with remaining packages when possible
- Report specific failures clearly
- Distinguish between authentication and package errors

### Partial Installation Recovery
- Track progress during installation
- Allow resuming from interruptions
- Clean up partial states when necessary

## User Experience Guidelines

### Information Display
- Show detected platform and environment type
- Display package installation progress
- Indicate which packages are already installed
- Provide clear next steps after completion

### Confirmation Prompts
- Production vs development environment detection
- Package installation consent
- Configuration change permissions
- Service startup confirmations

### Progress Feedback
- Real-time installation progress
- Success/failure indicators for each step
- Final summary of changes made
- Instructions for next steps

## Development Guidelines

### Code Style
- Use consistent error handling patterns
- Implement proper async/await usage
- Handle TTY interaction properly
- Use descriptive variable names
- Add comments for complex logic

### Testing Approach
- Test on clean systems (disposable VMs recommended)
- Verify revert functionality
- Test with pre-existing packages
- Validate authentication failure handling
- Check partial installation scenarios

### Platform Support
When adding new platforms:
1. Create `setup-{platform}.js` following existing patterns
2. Implement corresponding `revert-{platform}.js`
3. Add platform detection to main `setup.js`
4. Update this README with platform-specific requirements
5. Test thoroughly on target platform

## Security Considerations

### Privilege Escalation
- Use platform-appropriate tools (`doas`, `sudo`)
- Never require running setup as root
- Validate commands before execution
- Handle authentication timeouts gracefully

### Package Security
- Only install from official repositories
- Verify package manager functionality before use
- Check package signatures when possible
- Avoid installing unnecessary dependencies

### Configuration Safety
- Check existing configurations before modifying
- Create backups when appropriate
- Use atomic operations where possible
- Validate configuration syntax

## Troubleshooting

### Common Issues
1. **Double Enter requirement**: Known Bun TTY limitation with privilege escalation
2. **Hanging after auth failure**: Process timeout handling implemented
3. **Package conflicts**: Individual package installation prevents cascade failures
4. **Permission denied**: Check privilege escalation tool configuration

### Debug Information
- Install logs saved to `.kriit-setup-log.json`
- Platform detection results
- Package manager availability
- Privilege escalation tool status

## Integration

Setup scripts integrate with:
- **Main application**: `bun setup` command in package.json
- **Start script**: Detects missing dependencies and suggests setup
- **Container orchestration**: Prepares environment for `bun start`
- **Development workflow**: Supports both development and production setups