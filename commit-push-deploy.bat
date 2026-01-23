@echo off
echo === Commit, Push and Deploy ===
ssh "%SSH_USER%@%SSH_HOST%" "cd %REPO_DIR% && git add -A && git commit -m 'Обновления' && git push origin dev_main && bash deploy.sh"
echo === Done ===
pause
