#!/bin/bash
#$ -l h_rt=0:30:00
#$ -l sql=1
#$ -l virtual_free=50M
#$ -l user_slot=1
#$ -j y
#$ -o $HOME/sge/dataclear.out
#$ -m a
#$ -l arch=sol

### Resource limits:
###  30 minute hard runtime (est)
###  1 DB slot for sql
###  50M virtual memory (est)
###  1 user slot

/usr/bin/php /home/project/a/c/c/acc/public_html/ClearOldData.php
