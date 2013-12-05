#!/bin/bash
#$ -l h_rt=0:30:00
#$ -l sql=1
#$ -l virtual_free=50M
#$ -l user_slot=10
#$ -j y
#$ -o $HOME/sge/blacklist.out
#$ -m a
#$ -l arch=sol

### Resource limits:
###  30 minute hard runtime (est)
###  1 DB slot for sql
###  50M virtual memory (est)
###  10 user slots (does a locking DB operation)


# do nothing.