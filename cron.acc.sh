#!/bin/bash
/************************************************
** English Wikipedia Account Request Interface **
** All code is released into the public domain **
**             Developers:                     **
**  SQL ( http://en.wikipedia.org/User:SQL )   **
**  Cobi ( http://en.wikipedia.org/User:Cobi ) **
**                                             **
************************************************/

if [ "X`ps auxfwww|grep 'sql'|grep 'php'|grep 'accounts2.php'|grep -v grep`" = "X" ] ; then cd /home/sql/public_html/acc ; ( 
nohup php accounts2.php &>/dev/null & ) ; done



