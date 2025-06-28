#!/bin/bash
# Alpine Linux revert script for KRIIT project

set -e  # Exit on error

# Colors for output
    RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
USERNAME=${USER:-kriit}
REVERT_LOG=".kriit-revert.log"

# Helper functions
print_header() {
    echo -e "${BLUE}$1${NC}"
    # shellcheck disable=SC2005
    echo "$(printf '=%.0s' {1..40})"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${CYAN}ℹ $1${NC}"
}

log_action() {
    echo "│ [$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$REVERT_LOG"
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Determine privilege command
get_privilege_cmd() {
    if command_exists doas; then
    echo "doas"
    elif command_exists sudo; then
    echo "sudo"
else
    print_error "No privilege escalation tool found (doas or sudo)"
    exit 1
    fi
}

exec_privileged() {
    local priv_cmd
    priv_cmd=$(get_privilege_cmd)
    local max_tries=99           # <- how many password attempts
    local try=1

    if [ -n "$priv_cmd" ]; then
        while [ $try -le $max_tries ]; do
            # run the command; all stdio is still interactive
            $priv_cmd "$@"
            status=$?

            # success → return to caller
            [ $status -eq 0 ] && return 0

            # status 1 = auth error for both doas & sudo
            if [ $status -eq 1 ]; then
                if [ $try -lt $max_tries ]; then
                    echo "doas: Authentication failed – please try again." >&2
                fi
            else
                # some *other* error – don’t loop for that
                return $status
            fi
            try=$((try + 1))
        done
        # exhausted tries
        return 1
    else
        "$@"
    fi
}


# Main revert function
main() {
    print_header "🔄 KRIIT Alpine Setup Revert Script"

    print_info "This script will help you selectively remove Kriit-related components"
    print_warning "⚠️  WARNING: This will remove containers, data, and system configurations!"
    print_warning "This action may affect your development environment and running services."
    echo ""
    echo -n "To continue, type 'yes' (without quotes): "
    read -r confirmation
    
    if [ "$confirmation" != "yes" ]; then
        print_info "Revert cancelled by user"
        exit 0
    fi
    
    echo "┌─────────────────────────────────────────────────────────────────" >> "$REVERT_LOG"

    # Default configurations to potentially revert
    local configurations="subuid_entry_for_$USERNAME subgid_entry_for_$USERNAME cgroups_service_started"

# Check what's actually installed
    echo ""
    local podman_installed=false
    local iptables_installed=false
    
    if command_exists podman || apk info -e podman >/dev/null 2>&1; then
        podman_installed=true
        log_action "Found podman package installed"
    else
        log_action "Podman package not found"
    fi
    
    if command_exists iptables || apk info -e iptables >/dev/null 2>&1; then
        iptables_installed=true
        log_action "Found iptables package installed"
    else
        log_action "iptables package not found"
    fi

# Confirm with user (only ask about installed components)
    local remove_podman=false
    local remove_iptables=false
    
    if $podman_installed; then
        echo -n "Do you want to remove podman and podman-compose with all their data? [y/N] "
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            remove_podman=true
            log_action "User confirmed: will remove podman and podman-compose"
        else
            log_action "User declined: keeping podman and podman-compose"
        fi
    fi
    
    if $iptables_installed; then
        echo -n "Do you want to remove iptables? [y/N] "
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            remove_iptables=true
            log_action "User confirmed: will remove iptables"
        else
            log_action "User declined: keeping iptables"
        fi
    fi
    
    if ! $podman_installed && ! $iptables_installed; then
        print_info "No Kriit-related packages are currently installed"
    fi
    
    # Build packages list based on user choices
    local packages=""
    if $remove_podman; then
        packages="podman podman-compose"
    fi
    if $remove_iptables; then
        packages="$packages iptables"
    fi
    packages=$(echo $packages | xargs)  # trim whitespace
    
    if [ -z "$packages" ] && [ -z "$configurations" ]; then
        print_info "Nothing selected for removal"
        exit 0
    fi

# Check current state
    print_header "🔍 Checking current system state"

# Stop and remove all containers first (before removing packages)
    if command_exists podman; then
        print_header "🛑 Stopping and removing containers"
        
        # Stop all running containers
        if podman ps -q --filter status=running 2>/dev/null | grep -q .; then
            print_info "Stopping all running containers..."
            local stop_output
            set +e  # Temporarily disable exit on error
            stop_output=$(podman stop $(podman ps -q --filter status=running) 2>&1)
            local stop_status=$?
            set -e  # Re-enable exit on error
            if [ $stop_status -eq 0 ]; then
                print_success "All running containers stopped"
                log_action "Stopped all running containers"
            else
                print_warning "Failed to stop some containers"
                log_action "Failed to stop some containers: $stop_output"
            fi
        else
            print_info "No running containers found"
            log_action "No running containers to stop"
        fi
        
        # Remove all containers (stopped and running)
        if podman ps -aq 2>/dev/null | grep -q .; then
            print_info "Removing all containers..."
            local rm_output
            set +e  # Temporarily disable exit on error
            rm_output=$(podman rm -f $(podman ps -aq) 2>&1)
            local rm_status=$?
            set -e  # Re-enable exit on error
            if [ $rm_status -eq 0 ]; then
                print_success "All containers removed"
                log_action "Removed all containers (forced removal)"
            else
                print_warning "Failed to remove some containers"
                log_action "Failed to remove some containers: $rm_output"
            fi
        else
            print_info "No containers found to remove"
            log_action "No containers to remove"
        fi
        
        # Remove all images
        if podman images -q 2>/dev/null | grep -q .; then
            print_info "Removing all container images..."
            local rmi_output
            set +e  # Temporarily disable exit on error
            rmi_output=$(podman rmi -f $(podman images -q) 2>&1)
            local rmi_status=$?
            set -e  # Re-enable exit on error
            if [ $rmi_status -eq 0 ]; then
                print_success "All container images removed"
                log_action "Removed all container images (forced removal)"
            else
                print_warning "Failed to remove some images"
                log_action "Failed to remove some container images: $rmi_output"
            fi
        else
            print_info "No container images found to remove"
            log_action "No container images to remove"
        fi
        
        # Remove all volumes
        if podman volume ls -q 2>/dev/null | grep -q .; then
            print_info "Removing all container volumes..."
            local volume_output
            set +e  # Temporarily disable exit on error
            volume_output=$(podman volume rm -f $(podman volume ls -q) 2>&1)
            local volume_status=$?
            set -e  # Re-enable exit on error
            if [ $volume_status -eq 0 ]; then
                print_success "All container volumes removed"
                log_action "Removed all container volumes (forced removal)"
            else
                print_warning "Failed to remove some volumes"
                log_action "Failed to remove some container volumes: $volume_output"
            fi
        else
            print_info "No container volumes found to remove"
            log_action "No container volumes to remove"
        fi
        
        # Remove all networks (except default)
        if podman network ls --format "{{.Name}}" 2>/dev/null | grep -v "^podman$" | grep -q .; then
            print_info "Removing all custom container networks..."
            local networks=$(podman network ls --format "{{.Name}}" 2>/dev/null | grep -v "^podman$")
            for network in $networks; do
                local network_output
                set +e  # Temporarily disable exit on error
                network_output=$(podman network rm "$network" 2>&1)
                local network_status=$?
                set -e  # Re-enable exit on error
                if [ $network_status -eq 0 ]; then
                    print_success "Removed network: $network"
                    log_action "Removed container network: $network"
                else
                    print_warning "Failed to remove network: $network"
                    log_action "Failed to remove container network: $network ($network_output)"
                fi
            done
        else
            print_info "No custom container networks found to remove"
            log_action "No custom container networks to remove"
        fi
    else
        print_info "Podman not found - skipping container cleanup"
        log_action "Podman command not available - skipped container cleanup"
    fi

# Remove packages
    if [ -n "$packages" ]; then
    print_header "🗑️  Removing packages"

    for pkg in $packages; do
        if apk info -e "$pkg" >/dev/null 2>&1; then
            print_info "Removing $pkg..."
            if exec_privileged apk del "$pkg"; then
                print_success "$pkg removed"
                log_action "Successfully removed Alpine package: $pkg"
            else
                print_warning "Failed to remove $pkg"
                log_action "Failed to remove Alpine package: $pkg (permission or dependency issue)"
            fi
        else
            print_info "$pkg already removed"
            log_action "Package $pkg was already removed"
        fi
    done
    fi

# Revert configurations
    print_header "🔧 Reverting configurations"

# Remove subuid/subgid entries
    if echo "$configurations" | grep -q "subuid"; then
    if grep -q "^${USERNAME}:" /etc/subuid 2>/dev/null; then
        print_info "Removing subuid entry..."
        if exec_privileged sed -i "/^${USERNAME}:/d" /etc/subuid; then
            print_success "subuid entry removed"
            log_action "Removed subuid mapping for $USERNAME from /etc/subuid"
        else
            print_warning "Failed to remove subuid entry"
            log_action "Failed to remove subuid mapping for $USERNAME from /etc/subuid"
        fi
    else
        print_info "subuid entry already removed"
        log_action "subuid mapping for $USERNAME was already removed from /etc/subuid"
    fi
    fi

    if echo "$configurations" | grep -q "subgid"; then
    if grep -q "^${USERNAME}:" /etc/subgid 2>/dev/null; then
        print_info "Removing subgid entry..."
        if exec_privileged sed -i "/^${USERNAME}:/d" /etc/subgid; then
            print_success "subgid entry removed"
            log_action "Removed subgid mapping for $USERNAME from /etc/subgid"
        else
            print_warning "Failed to remove subgid entry"
            log_action "Failed to remove subgid mapping for $USERNAME from /etc/subgid"
        fi
    else
        print_info "subgid entry already removed"
        log_action "subgid mapping for $USERNAME was already removed from /etc/subgid"
    fi
    fi

# Stop cgroups service (if podman was removed)
    if $remove_podman && rc-service cgroups status 2>/dev/null | grep -q started; then
        print_info "Stopping cgroups service (no longer needed without Podman)..."
        if exec_privileged rc-service cgroups stop 2>/dev/null; then
            print_success "cgroups service stopped"
            log_action "Stopped Alpine cgroups service via rc-service (Podman removed)"
        else
            print_warning "Failed to stop cgroups service (may require manual intervention)"
            print_info "You can manually stop it with: doas rc-service cgroups stop"
            log_action "Failed to stop Alpine cgroups service (permission or service issue)"
        fi
    elif $remove_podman; then
        log_action "Alpine cgroups service was not running"
    else
        log_action "Skipped cgroups service stop (user did not select podman removal)"
    fi


# Unload TUN kernel module (always check if loaded, regardless of podman removal choice)
    if lsmod | grep -q "^tun "; then
        print_info "Unloading TUN kernel module (no longer needed without Podman)..."
        if exec_privileged modprobe -r tun 2>/dev/null; then
            print_success "TUN kernel module unloaded"
            log_action "Unloaded TUN kernel module via modprobe -r"
        else
            print_warning "Failed to unload TUN kernel module (may be in use by other processes)"
            log_action "Failed to unload TUN kernel module (may be in use by other processes)"
        fi
    else
        log_action "TUN kernel module was not loaded"
    fi

# Clean up Podman data and dependencies (always check for leftover data)
    print_header "🧹 Cleaning up Podman data and dependencies"
    
    if [ -d ~/.local/share/containers ] || [ -d ~/.config/containers ] || [ -d ~/.cache/containers ]; then
        print_info "Removing Podman directories..."
        rm -rf ~/.local/share/containers/ ~/.config/containers/ ~/.cache/containers/ 2>/dev/null || true
        print_success "Podman directories removed"
        log_action "Removed Podman user directories: ~/.config/containers ~/.local/share/containers ~/.cache/containers"
    else
        print_info "Podman directories already removed"
        log_action "Podman user directories were already removed"
    fi

    # Remove any podman temp files
    rm -f /tmp/podman-* 2>/dev/null || true
    log_action "Cleaned up Podman temporary files from /tmp"
    
    # Remove orphaned container runtime dependencies (only if podman packages were actually removed in this run)
    if $remove_podman && command_exists apk; then
        local orphans
        orphans=$(apk list --installed 2>/dev/null | grep -E "(conmon|crun|slirp4netns|fuse-overlayfs)" | cut -d' ' -f1 || true)
        if [ -n "$orphans" ]; then
            print_info "Removing unused container runtime dependencies..."
            log_action "Found orphaned container dependencies: $(echo "$orphans" | tr '\n' ' ')"
            for pkg in $orphans; do
                pkg_name=$(echo "$pkg" | cut -d'-' -f1)
                if exec_privileged apk del "$pkg_name" 2>/dev/null; then
                    print_success "$pkg_name removed"
                    log_action "Successfully removed orphaned package: $pkg_name"
                else
                    print_warning "Failed to remove $pkg_name"
                    log_action "Failed to remove orphaned package: $pkg_name"
                fi
            done
        else
            log_action "No orphaned container dependencies found"
        fi
    elif ! $remove_podman; then
        log_action "Skipped orphaned package cleanup (podman packages not removed this run)"
    fi


# Summary
    print_header "✅ Revert Complete!"

    print_success "System has been restored to pre-setup state"
    print_info "You can now test the setup script again with: bun setup"
    print_info "Revert log: $REVERT_LOG"
}

# Run main function
main "$@"