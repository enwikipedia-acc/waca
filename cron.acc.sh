#!/bin/bash
######################################################
## English Wikipedia Account Request Interface      ##
## Wikipedia Account Request Graphic Design by      ##
## Charles Melbye is licensed under a Creative      ##
## Commons Attribution-Noncommercial-Share Alike    ##
## 3.0 United States License. All other code        ##
## released under Public Domain by the ACC          ##
## Development Team.                                ##
##             Developers:                          ##
##  SQL ( http://en.wikipedia.org/User:SQL )        ##
##  Cobi ( http://en.wikipedia.org/User:Cobi )      ##
## Cmelbye ( http://en.wikipedia.org/User:cmelbye ) ##
##                                                  ##
######################################################

if [ "X`ps auxfwww|grep 'sql'|grep 'php'|grep 'accounts2.php'|grep -v grep`" = "X" ] ; then cd /home/sql/public_html/acc ; ( 
nohup php accounts2.php &>/dev/null & ) ; done



