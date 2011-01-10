#!/bin/bash
#$ -l h_rt=0:30:00
#$ -j y
#$ -o $HOME/sge.backup-monthly.out
#$ -m a

cd /home/project/a/c/c/acc/public_html
/usr/bin/php backup.php --monthly