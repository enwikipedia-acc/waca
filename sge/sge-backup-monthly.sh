#!/bin/bash
#$ -l h_rt=1:00:00
#$ -l virtual_free=500M
#$ -l user_slot=10
#$ -j y
#$ -o $HOME/sge/backup-monthly.out
#$ -m a
#$ -l arch=sol

### Resource limits:
###  60 minute hard runtime (est)
###  500M virtual memory (est)
###  10 user slots (Locking on backups directory)

cd /home/project/a/c/c/acc/public_html
/usr/bin/php backup.php --monthly
