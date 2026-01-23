@echo off
echo === Commit, Push and Deploy ===
ssh site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && git add -A && git commit -m 'Обновления' && git push origin dev_main && bash deploy.sh"
echo === Done ===
pause
