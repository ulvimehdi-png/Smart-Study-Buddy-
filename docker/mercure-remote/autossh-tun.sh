#!/bin/bash

USER=a25sewt301
REMOTE="shell.studev.groept.be"
PORT_FORWARD="127.0.0.1:33033:127.0.0.1:33033"

while true; do
	if ping -c 1 -W 2 "$REMOTE" &> /dev/null; then
		echo "$(date '+%Y-%m-%d %H:%M:%S') - Server reachable, starting SSH"
		ssh -N -T \
			-i ~/.ssh/id_ed25519 \
			-o IdentitiesOnly=yes \
			-o PasswordAuthentication=no \
			-o ServerAliveInterval=30 \
			-o ServerAliveCountMax=3 \
			-R "$PORT_FORWARD" "$USER@$REMOTE"
		echo "$(date '+%Y-%m-%d %H:%M:%S') - SSH connection lost, reconnecting in 5 seconds"
	else
		echo "$(date '+%Y-%m-%d %H:%M:%S') - Server not reachable, retrying in 10 seconds"
		sleep 10
	fi

	sleep 5
done
