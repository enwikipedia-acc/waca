#!/bin/bash
#$ -j y
#$ -o $HOME/sge.ircbot.out
#$ -r y

cd /home/project/a/c/c/acc/public_html/
php accbot.php

