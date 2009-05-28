<?php

function showQuestion($id, $results)
{
	global $username;
	$email = "foo@bar.baz";
	$ip = "12.12.12.12";
	$request = "MyAccount";
	$comment = "meh";
	$createlink = "javascript:alert('create link');";
	$createlink = ""; 
	$similarlink = "";
	$takenlink = "";
	$upolicylink = "";
	$invalidlink = "";
	$defertoadminslink = "";
	$droplink = "";
	$unreservelink = "";
	
	
	return '<h2>Details for Request #'.$id.':</h2><table cellspacing="0"><tr><td><small>[ </small></td>
	<td><small><a class="request-src" href="javascript:alert(\'Your email client is opening with an email to '.$email.' ...\');">'.$email.'</a></small></td>
 	<td><small><span class="request-src">(0)</span></small></td>
	<td><small> | </small></td>
	<td><small><a class="request-src" name="ip-link" href="http://stwalkerster.unixpod.com/w/index.php?title=User_talk:'.$ip.'" target="_blank">'.$ip.'</a> </small></td>
	<td><small><span class="request-src">(0)</span></small></td>
	<td><small>
		<a class="request-src" href="http://stwalkerster.unixpod.com/w/index.php?title=Special:Contributions/'.$ip.'" target="_blank">c</a>
		<a class="request-src" href="http://stwalkerster.unixpod.com/w/index.php?title=Special:Log&amp;type=block&amp;page=User:'.$ip.'" target="_blank">b</a> 
		<a class="request-src" href="http://stwalkerster.unixpod.com/w/index.php?title=Special%3ABlockList&ip='.$ip.'" target="_blank">r</a> 
		<a class="request-src" href="http://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page=User:'.$ip.'" target="_blank">gb</a> 
		<a class="request-src" href="http://en.wikipedia.org/w/index.php?title=Special%3AGlobalBlockList&ip='.$ip.'" target="_blank">gr</a> 
		<a class="request-src" href="http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup='.$ip.'" target="_blank">w</a>
		</small></td>
	<td><small> ] </small></td>
	<td><small><a class="request-req" href="http://stwalkerster.unixpod.com/w/index.php?title=User:'.$request.'" target="_blank"><strong>'.$request.'</strong></a> </small></td>
	<td><small>(
		<a class="request-req" href="http://stwalkerster.unixpod.com/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page=User:'.$request.'" target="_blank">Creation</a> 
		<a class="request-req" href="http://toolserver.org/~vvv/sulutil.php?user='.$request.'" target="_blank">SUL</a> 
		<a class="request-req" href="http://stwalkerster.unixpod.com/w/index.php?title=Special%3AListUsers&amp;username='.$request.'&amp;group=&amp;limit=1" target="_blank">List</a>)
		<b><a class="request-req" href="'.$createlink.'">Create!</a></b>
		</small></td>
	<td><small> | </small></td>
	
	<td><small>
		<a class="request-done" href="'.$createlink.'">Done!</a> - 
		<a class="request-done" href="'.$similarlink.'">Similar</a> - 
		<a class="request-done" href="'.$takenlink.'">Taken</a> - 
		<a class="request-done" href="'.$upolicylink.'">UPolicy</a> - 
		<a class="request-done" href="'.$invalidlink.'">Invalid</a> - 
		<a class="request-done" href="'.$defertoadminslink.'">Defer to flagged users</a> - 
		<a class="request-done" href="'.$droplink.'">Drop</a>
		</small></td>
	<td><small> |</small></td>
	<td><small>YOU are handling this request. <a href="'.$unreservelink.'">Break reservation</a></small></td>
	</tr></table>
<br /><strong>Comment</strong>: '.$comment.'<br />

<h3>This request is currently being handled by '.$username.'</h3><h2>Possibly conflicting usernames</h2>
<i>None detected</i><br />
<h2>Logs for this request:</h2>
<h2>Other requests from '.$ip.':</h2>
<i>None.</i>
<h2>Other requests from '.$email.':</h2>
<i>None.</i>';
	
	// get question data
	
	// show question data
	
	// get antispoof stuff
	
	// get other requests from that username
	
}

function showIntro($username)
{
	$out = '<h1>English Wikipedia Internal Account Creation Interface</h1>
<h2>New user quiz</h2><p>
Hi there '.$username.'! Welcome to the account creation internal interface. Before proceeding, you will need to complete a short quiz
so we can be sure you know what you are doing. You will be presented with a number of scenarios, and you need to
choose the best course of action for each. You will need to read the documentation before completing this quiz.</p>
<p>Please remember, this is only a mock-up of the interface, and the real interface will differ slightly from this.</p>
<p><a href="?action=ask">Start the quiz!</a></p>';

	return $out;
}