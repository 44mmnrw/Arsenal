#!/usr/bin/env python3
import subprocess
import sys

commands = [
    'ssh site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && git add -A"',
    'ssh site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && git commit -m \'Обновления\'"',
    'ssh site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && git push origin dev_main"',
    'ssh site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && bash deploy.sh"'
]

for i, cmd in enumerate(commands, 1):
    print(f"\n[{i}/{len(commands)}] Executing: {cmd[:60]}...")
    result = subprocess.run(cmd, shell=True, cwd='c:\\laragon\\www\\arsenal')
    if result.returncode != 0:
        print(f"Error executing command {i}")
        sys.exit(1)

print("\n✅ All operations completed successfully!")
