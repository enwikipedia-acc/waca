#!/bin/bash
if [ "X`ps auxfwww|grep 'sql'|grep 'php'|grep 'accounts2.php'|grep -v grep`" = "X" ] ; then cd /home/sql/public_html/acc ; ( 
nohup php accounts2.php &>/dev/null & ) ; done
