{include file="alert.tpl" alertblock="false" alerttype="" alertclosable=false alertheader="" alertmessage="You're not logged in! Please log in to continue."}
<div class="row-fluid">
	<div class="offset4 span4">
		{$errorbar}
	</div>
</div>
<div class="row-fluid">
	<div class="offset4 span4 well">
		<h3 class="text-center">Account Creation Interface - Login</h3>
		<form class="container-fluid" action="{$tsurl}/acc.php?action=login&amp;nocheck=1" method="post">
			<div class="control-group row">
				<input type="text" id="username" name="username" placeholder="Username" class="offset2 span8" required>
			</div>
			<div class="control-group row">
				<input type="password" id="password" name="password" placeholder="Password" class="offset2 span8" required>
			</div>
			<div class="control-group row">
					<button type="submit" class="btn btn-primary btn-block btn-large span8 offset2">Sign in</button>
			</div>
			<div class="control-group row">
					<a class="btn btn-block span8 offset2" href="{$tsurl}/acc.php?action=forgotpw">Forgot password?</a>
			</div>
			<div class="control-group row">
					<a class="btn btn-block span8 offset2" href="{$tsurl}/acc.php?action=register">Register</a>
			</div>
		</form>
	</div>
</div>