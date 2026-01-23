#!/bin/bash
cd /c/laragon/www/arsenal
git add -A
git commit -m "Fix: improve SQL validation for department-to-squad relationship in staff form"
git push origin dev_main
ssh -o StrictHostKeyChecking=no site_user76@212.113.120.197 "cd /var/www/site_user76/data/arsenal-repo && bash deploy.sh"
