#/bin/bash
#$ -l h_rt=0:30:00
#$ -j y
#$ -o $HOME/sge.statsemail.out
#$ -m bae

cd /home/project/a/c/c/acc/public_html
/usr/bin/php accstats.php