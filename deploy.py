#!/usr/bin/env python3
import subprocess
import sys

commands = [
    'ssh "${SSH_USER}@${SSH_HOST}" "cd ${REPO_DIR} && git add -A"',
    'ssh "${SSH_USER}@${SSH_HOST}" "cd ${REPO_DIR} && git commit -m \'Обновления\'"',
    'ssh "${SSH_USER}@${SSH_HOST}" "cd ${REPO_DIR} && git push origin dev_main"',
    'ssh "${SSH_USER}@${SSH_HOST}" "cd ${REPO_DIR} && bash deploy.sh"'
]

for i, cmd in enumerate(commands, 1):
    print(f"\n[{i}/{len(commands)}] Executing: {cmd[:60]}...")
    result = subprocess.run(cmd, shell=True, cwd='c:\\laragon\\www\\arsenal')
    if result.returncode != 0:
        print(f"Error executing command {i}")
        sys.exit(1)

print("\n✅ All operations completed successfully!")
