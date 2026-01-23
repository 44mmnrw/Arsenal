#!/bin/bash
cd /c/laragon/www/arsenal
git add -A
git commit -m "Fix: improve SQL validation for department-to-squad relationship in staff form"
git push origin dev_main
ssh -o StrictHostKeyChecking=no "${SSH_USER}@${SSH_HOST}" "cd ${REPO_DIR} && bash deploy.sh"
