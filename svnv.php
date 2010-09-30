<?php
preg_match_all('/([\d]+)/', exec("svnversion"), $match);
echo $match[0][0];