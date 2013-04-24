<?php

$data = array( 
	array(
		'comment' => false,
		'email' => 'test@stwalkerster.co.uk',
		'ec' => 0,
		'ip' => '10.4.0.54',
		'ic' => 4,
		'name' => 'Stwalkerster-sdfsdfsdf',
		'reserved' => false,
	),
	array(
		'comment' => true,
		'email' => 'FastLizard4@fastlizard4.org',
		'ec' => 0,
		'ip' => '10.4.0.54',
		'ic' => 4,
		'name' => 'AaaaahUsernameTaken',
		'reserved' => "Fastlizard4",
	),
	array(
		'comment' => true,
		'email' => 'root@fastlizard4.org',
		'ec' => 0,
		'ip' => '10.4.0.54',
		'ic' => 4,
		'name' => 'FastLizard4e44',
		'reserved' => true,
	),
	array(
		'comment' => false,
		'email' => 'postmaster@fastlizard4.org',
		'ec' => 3,
		'ip' => '10.4.0.54',
		'ic' => 4,
		'name' => 'WeNeedMoretests!',
		'reserved' => false,
	),
	array(
		'comment' => false,
		'email' => 'abuse@fastlizard4.org',
		'ec' => 0,
		'ip' => '96.126.96.9',
		'ic' => 2,
		'name' => 'AnotherXFFTest',
		'reserved' => false,
	),
	array(
		'comment' => false,
		'email' => 'webmaster@fastlizard4.org',
		'ec' => 2,
		'ip' => '2.5.6.8',
		'ic' => 3,
		'name' => 'NeedsMoarXFF',
		'reserved' => false,
	),
	array(
		'comment' => false,
		'email' => 'webmaster@fastlizard4.org',
		'ec' => 2,
		'ip' => '96.126.96.9',
		'ic' => 2,
		'name' => 'NeedsMoarXFF',
		'reserved' => false,
	),
	array(
		'comment' => false,
		'email' => 'lizardirc@fastlizard4.org',
		'ec' => 2,
		'ip' => '2.5.6.8',
		'ic' => 3,
		'name' => 'XFF2',
		'reserved' => false,
	),
	array(
		'comment' => true,
		'email' => 'fl4-bugzilla@fastlizard4.org',
		'ec' => 2,
		'ip' => '2.5.6.8',
		'ic' => 3,
		'name' => 'XFF3',
		'reserved' => false,
	),
	);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>English Wikipedia Internal Account Creation Interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="lib/bootstrap-2.3.1/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
    <link href="lib/bootstrap-2.3.1/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="lib/bootstrap-2.3.1/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="lib/bootstrap-2.3.1/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="lib/bootstrap-2.3.1/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="lib/bootstrap-2.3.1/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="lib/bootstrap-2.3.1/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="lib/bootstrap-2.3.1/ico/favicon.png">
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">Account Creation Interface</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="#">Requests</a></li>
              <li><a href="#">Logs</a></li>
              <li><a href="#">Users</a></li>
              <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Management<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="#">Ban Management</a></li>
					<li><a href="#">Message Management</a></li>
					<li><a href="#">Template Management</a></li>
					<li><a href="#">User Management</a></li>
				  </ul>
			  </li>
              <li><a href="#">Search</a></li>
              <li><a href="#">Statistics</a></li>
              <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Help<b class="caret"></b></a>
				  <ul class="dropdown-menu">
					<li><a href="#">Guide</a></li>
					<li><a href="#">Username Policy</a></li>
					<li><a href="#">Chat</a></li>
				  </ul>
			  </li>
            </ul>
			<ul class="nav pull-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">stwalkerster <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="nav-header">Account</li>
						<li><a href="#">My statistics</a></li>
						<li><a href="#">Edit Preferences</a></li>
						<li class="divider"></li>						
						<li><a href="#">Logout</a></li>
					</ul>
				</li>
			</ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row-fluid">
		<!-- site notice -->
		<div class="span12">
		<div class="alert alert-block">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<ul>
				<li><span class="text-error">Please remember that <u>ACC is <strong><em>NOT</em></strong> a race</u></span>.  Please slow down and make sure you assess each request correctly.</li>
				<li>It is possible to create accounts, so please DO NOT.</li>
				<li>Please write emails and custom closes in plain, simple English that newcomers to Wikipedia can understand.</li>
				<li><span class="text-info">Please note, there will be several requests coming from IPs starting with 196 and  41, from Polytechnic of Namibia. There are supposed to be many requests coming from these IPs with "Polytechnic2012" in the comment line. Please approve these and do not hold them back for the purpose of mass creation</span>.  There is a conference going on at this institute. A mailing list message was sent, see it for further details.</li>
			</ul>
		</div>
		</div>
      </div><!--/row-->

	  <div class="row-fluid">
		<!-- site header -->
		<div class="span12">
			<h3>Create an account!</h3>
		</div>
	  </div><!--/row-->	  
	  
	  
	  
	  <div class="row-fluid">
		<div class="span12"><h4>Open requests</h4>
		<p class="muted">This section hold the currently open requests, which have not been automatically detected to have issues which need attention from a specific group of people, but this does not preclude this possibility.</p>
		</div>
	  </div>
	  <div class="row-fluid">
		<div class="span12">
			<!-- request-section -->
			<table class="table table-striped">
				<thead><tr>
					<th>#</th>
					<th><!-- zoom --></th>
					<th><!-- comment --></th>
					<th>Email address</th>
					<th>IP address</th>
					<th>Username</th>
					<th><!-- ban --></th>
					<th><!-- reserve status --></th>
					<th><!--reserve button--></th>
				</tr></thead>
				<tbody><?php
				$i=0;
				foreach($data as $d){
					$i++;
					echo "<tr>\n";
					echo "<td>$i</td>\n";
					if($d["comment"]) {
						echo '<td><a class="btn btn-small btn-info hidden-desktop" href="#">Zoom</a><a class="btn btn-small visible-desktop" href="#">Zoom</a></td><td><span class="label label-info visible-desktop">Comment</span></td>' . "\n";
					} else {
						echo '<td><a class="btn btn-small" href="#">Zoom</a></td><td></td>' . "\n";
					}
					echo '<td><a href="#" target="_blank">'.$d['email'].'</a>&nbsp;';
					if($d["ec"] != 0) {
						echo '<span class="badge badge-important">'.$d["ec"].'</span></td>' . "\n";
					} else {
						echo '<span class="badge">0</span></td>' . "\n";
					}
					echo '<td><a href="#" target="_blank">'.$d['ip'].'</a>&nbsp;';
					if($d["ic"] != 0) {
						echo '<span class="badge badge-important">'.$d["ic"].'</span></td>' . "\n";
					} else {
						echo '<span class="badge">0</span></td>' . "\n";
					}
					echo '<td><a href="#" target="_blank">'.$d["name"].'</a></td>' . "\n";
					echo '<td><div class="btn-group"><a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">Ban<span class="caret"></span></a><ul class="dropdown-menu"><li><a href="#">IP</a></li><li><a href="#">Email</a></li><li><a href="#">Name</a></li></ul></div></td>' . "\n";
					if($d["reserved"] === false ) {
						echo '<td></td><td><a class="btn btn-small btn-success" href="#">Reserve</a></td>'  . "\n";
					} else if($d["reserved"] === true ) {
						echo '<td>Being handled by you</td><td><a class="btn btn-small btn-warning" href="#">Break reservation</a></td>'  . "\n";
					} else {
						echo '<td>Being handled by '.$d["reserved"].'</td><td><a class="btn btn-small btn-warning" href="#">Force break</a></td>'  . "\n";
					}
					echo "</tr>\n";
				}
				?>
				</tbody>
			</table>
		</div>
	  </div><!--/row-->
      <hr>	  
	  
	  
	  <div class="row-fluid">
		<div class="span12"><h4>Flagged user needed</h4>
		<p class="muted">This section hold the requests which have been found to have AntiSpoof conflicts which require the accountcreator flag to be created. You don't need the flag to handle these unless you are creating the account.</p>
		</div>
	  </div>
	  <div class="row-fluid">
		<div class="span12">
			<!-- request-section -->
			<table class="table table-striped">
				<thead><tr>
					<th>#</th>
					<th><!-- zoom --></th>
					<th><!-- comment --></th>
					<th>Email address</th>
					<th>IP address</th>
					<th>Username</th>
					<th><!-- ban --></th>
					<th><!-- reserve status --></th>
					<th><!--reserve button--></th>
				</tr></thead>
				<tbody>
					<tr>
						<td>1</td>
						<td><a class="btn btn-small btn-info hidden-desktop" href="#">Zoom</a><a class="btn btn-small visible-desktop" href="#">Zoom</a></td><td><span class="label label-info visible-desktop">Comment</span></td>
						<td><a href="#">simon@stwalkerster.co.uk</a>&nbsp;<span class="badge">0</span></td>
						<td><a href="#" target="_blank">127.0.0.1</a>&nbsp;<span class="badge">0</span></td>
						<td><a href="#" target="_blank">Retsreklawts</a></td>
						<td><div class="btn-group"><a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">Ban<span class="caret"></span></a><ul class="dropdown-menu"><li><a href="#">IP</a></li><li><a href="#">Email</a></li><li><a href="#">Name</a></li></ul></div></td>
						<td></td>
						<td><a class="btn btn-small btn-success" href="#">Reserve</a></td>
					</tr>
				</tbody>
			</table>
		</div>
	  </div><!--/row-->
      <hr>

      <footer>
                <p><small>Account Creation Assistance Manager (<a href="https://github.com/enwikipedia-acc/waca/tree/rel4.32">version rel4.32</a>) by <a href="http://toolserver.org/~acc/sand/team.php">The ACC development team</a> (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>).</small></p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="lib/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="lib/bootstrap-2.3.1/js/bootstrap.js" type="text/javascript"></script>

  </body>
</html>
