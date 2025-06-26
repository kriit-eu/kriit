# System-wide Podman setup for Alpine Linux (run once on the server)
echo "root:100000:65536" >> /etc/subuid
echo "root:100000:65536" >> /etc/subgid

mkdir -p /etc/containers
printf "[containers]\nuserns=\"auto\"\n" > /etc/containers/containers.conf

# cgroupv2 service so compose pods start
rc-update add cgroups default
rc-service  cgroups start
