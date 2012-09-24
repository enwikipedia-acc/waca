#/bin/bash
#$ -l h_rt=0:10:00
#$ -l virtual_free=50M
#$ -l user_slot=1
#$ -l sql=1
#$ -j y
#$ -o $HOME/sge/statsemail.out
#$ -m a
#$ -l arch=sol

### Resource limits:
###  10 minute hard runtime (est)
###  1 DB slot for sql
###  50M virtual memory (est)
###  1 user slot

cd /home/project/a/c/c/acc/public_html
/usr/bin/php accstats.php
