#!/bin/bash
#$ -l h_rt=1:00:00
#$ -l sql=1
#$ -l virtual_free=500M
#$ -l user_slot=10
#$ -j y
#$ -o $HOME/sge/backup.out
#$ -m a
#$ -l arch=sol

### Resource limits:
###  60 minute hard runtime (est)
###  1 DB slot for sql
###  500M virtual memory (est)
###  10 user slots (Large operation on entire database / locking on backups directory)

/usr/bin/php /home/project/a/c/c/acc/public_html/backup.php
