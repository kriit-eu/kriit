#!/bin/bash
# Alpine Linux setup script for KRIIT – single‑prompt edition (safe‑test)
# ----------------------------------------------------------------------------
# Installs Podman & friends in one privileged block, then *optionally* runs a
# rootless smoke‑test.  The test is skipped (with a warning) when fresh
# subuid/subgid mappings were just added, because they only take effect after
# the user logs in again.

set -e

# ─────────────────────────────── Colours ────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

USERNAME=${USER:-kriit}
SETUP_LOG=".kriit-setup.log"

print_header()  { echo -e "${BLUE}$1${NC}"; printf '=%.0s' {1..40}; echo; }
print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error()   { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info()    { echo -e "${CYAN}ℹ $1${NC}"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

log_action() {
  echo "│ [$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$SETUP_LOG"
}

get_privilege_cmd() {
  if [ "$PRODUCTION" = 1 ]; then
    command_exists doas && { echo doas; return; }
    command_exists sudo  && { echo sudo; return; }
    print_error "Neither doas nor sudo found – aborting."; exit 1
  fi
  echo ""
}

podman_smoke_test() {
  # Just check if podman can list containers (basic functionality test)
  timeout 5 podman ps >/dev/null 2>&1
}

# ────────────────────────────────── main ─────────────────────────────────
main() {
  print_header "🏔️  Alpine Linux Setup for KRIIT"
  [ "$PRODUCTION" = 1 ] && print_info "Production mode – will elevate via doas/sudo" || print_warning "Dev mode – running as root"

  echo "┌─────────────────────────────────────────────────────────────────" >> "$SETUP_LOG"

  #── 1. Detect current state
  print_header "🔍 Checking current system state"
  has_podman=$(command_exists podman && echo true || echo false)
  has_compose=$(command_exists podman-compose && echo true || echo false)
  has_iptables=$(command_exists iptables && echo true || echo false)

  [ "$has_podman"   = true ] && print_success "Podman installed"          || print_info "Podman not found"
  [ "$has_compose"  = true ] && print_success "podman-compose installed"   || print_info "podman-compose not found"
  [ "$has_iptables" = true ] && print_success "iptables installed"         || print_info "iptables not found"

  pkgs_needed=()
  [ "$has_podman"   = true ] || pkgs_needed+=(podman)
  [ "$has_compose"  = true ] || pkgs_needed+=(podman-compose)
  [ "$has_iptables" = true ] || pkgs_needed+=(iptables)

  needs_subuid=false; grep -q "^${USERNAME}:" /etc/subuid 2>/dev/null || needs_subuid=true
  needs_subgid=false; grep -q "^${USERNAME}:" /etc/subgid 2>/dev/null || needs_subgid=true
  $needs_subuid && print_info "Need to add subuid for $USERNAME" || print_success "subuid already set"
  $needs_subgid && print_info "Need to add subgid for $USERNAME" || print_success "subgid already set"

  cgroups_running=false; rc-service cgroups status 2>/dev/null | grep -q started && cgroups_running=true
  $cgroups_running && print_success "cgroups service running" || print_info "cgroups service not running"

  #── 2. One‑shot privileged block if required
  needs_tun_module=false; [ ! -c /dev/net/tun ] && needs_tun_module=true
  $needs_tun_module && print_info "Need to load tun kernel module" || print_success "TUN device available"

  if [ ${#pkgs_needed[@]} -gt 0 ] || $needs_subuid || $needs_subgid || ! $cgroups_running || $needs_tun_module; then
    print_header "🛡  Requesting privileges (single prompt)"
    priv=$(get_privilege_cmd)

    if [ -n "$priv" ]; then
      $priv sh <<ROOT
set -e
[ ${#pkgs_needed[@]} -eq 0 ] || apk add ${pkgs_needed[*]}
[ "$needs_subuid" = true ] && echo "${USERNAME}:100000:65536" >> /etc/subuid
[ "$needs_subgid" = true ] && echo "${USERNAME}:100000:65536" >> /etc/subgid
[ "$needs_tun_module" = true ] && modprobe tun
# Handle cgroups service separately (may fail if already mounted)
set +e
rc-service cgroups status >/dev/null 2>&1 || rc-service cgroups start >/dev/null 2>&1
set -e
ROOT
    else
      # already root
      [ ${#pkgs_needed[@]} -eq 0 ] || apk add ${pkgs_needed[*]}
      [ "$needs_subuid" = true ] && echo "${USERNAME}:100000:65536" >> /etc/subuid
      [ "$needs_subgid" = true ] && echo "${USERNAME}:100000:65536" >> /etc/subgid
      if ! $cgroups_running; then
        rc-service cgroups start >/dev/null 2>&1 || true
      fi
      $needs_tun_module && modprobe tun
    fi
  fi

  #── 3. Log installs/configs
  if [ ${#pkgs_needed[@]} -gt 0 ]; then
    for p in "${pkgs_needed[@]}"; do log_action "Installed Alpine package: $p"; done
  else
    log_action "Packages check: podman, podman-compose, iptables all already installed"
  fi
  
  if $needs_subuid; then
    log_action "Added subuid mapping for $USERNAME (100000:65536) to /etc/subuid"
  else
    log_action "Subuid mapping for $USERNAME already exists in /etc/subuid"
  fi
  
  if $needs_subgid; then
    log_action "Added subgid mapping for $USERNAME (100000:65536) to /etc/subgid"
  else
    log_action "Subgid mapping for $USERNAME already exists in /etc/subgid"
  fi
  
  if ! $cgroups_running; then
    log_action "Started Alpine cgroups service via rc-service"
  else
    log_action "Alpine cgroups service already running"
  fi
  
  if $needs_tun_module; then
    log_action "Loaded TUN kernel module via modprobe"
  else
    log_action "TUN device /dev/net/tun already available"
  fi

  #── 4. Rootless Podman check – skip when fresh uid/gid mappings added
print_header "🧪 Verifying Podman rootless setup"
if [ "$needs_subuid" = true ] || [ "$needs_subgid" = true ]; then
  print_warning "New subuid/subgid mappings were added – rootless containers require a re-login."
  print_info    "  1. Log out of this shell or SSH session."
  print_info    "  2. Log back in as '$USERNAME'."
  print_info    "  3. Run bun setup again to pick up where you left off and verify rootless configuration."
  print_info    "  4. Then start the stack: bun start"
  print_info    "Setup log: $SETUP_LOG"
  exit 0
else
  if podman_smoke_test; then
    print_success "Podman is working in rootless mode"
    log_action "Podman rootless verification: 'podman ps' command successful"
  else
    print_warning "Podman basic test failed. May need re-login to pick up new user mappings."
    print_info    "If problems persist after re-login, check: podman ps"
    log_action "Podman rootless verification: 'podman ps' command failed (may need re-login)"
  fi
fi

print_info "Creating Podman directories in home"
  mkdir -p ~/.config/containers ~/.local/share/containers ~/.config/containers ~/.local/share/containers
  print_success "Directories ensured"
  log_action "Created Podman directories: ~/.config/containers ~/.local/share/containers ~/.cache/containers"

    print_header "✅ Setup Complete!"
  print_info  "Setup log: $SETUP_LOG"
  if [ "$needs_subuid" = true ] || [ "$needs_subgid" = true ]; then
    print_warning "Re-login required before running the stack."
    print_info "Log out and back in before executing: bun start"
    exit 0
  else
    print_success "Alpine Linux is now configured for KRIIT – you can now run: bun start"
  fi
}

main "$@"