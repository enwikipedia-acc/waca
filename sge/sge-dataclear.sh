#!/bin/bash
#$ -l h_rt=0:30:00
#$ -j y
#$ -o $HOME/sge.dataclear.out
#$ -m a

/usr/bin/php /home/project/a/c/c/acc/public_html/ClearOldData.php