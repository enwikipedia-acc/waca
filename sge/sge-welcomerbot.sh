#!/bin/bash
#$ -l h_rt=0:30:00
#$ -j y
#$ -o $HOME/sge.welcomerbot.out
#$ -m a

. $HOME/scripts/runHelloBot.sh
