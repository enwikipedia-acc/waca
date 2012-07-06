#!/bin/bash
#$ -l h_rt=0:30:00
#$ -l virtual_free=50M
#$ -l sql=1
#$ -l user_slot=1
#$ -j y
#$ -o $HOME/sge.welcomerbot.out
#$ -m a

### Resource limits:
###  30 minute hard runtime (est)
###  1 DB slot for sql
###  50M virtual memory (est)
###  1 user slot

. $HOME/scripts/runHelloBot.sh
