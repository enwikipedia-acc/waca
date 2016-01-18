<h3 class="text-center">Account Creation Interface</h3>
<div class="row-fluid">
	<div class="offset4 span4">
		{include file="alert.tpl" alertblock="false" alerttype="alert-info" alertclosable=false alertheader="" alertmessage="<strong>You're not logged in!</strong> Please log in to continue."}
		{$errorbar}
	</div>
</div>
<div class="row-fluid">
	<div class="offset4 span4 well">
		<form class="container-fluid" action="{$baseurl}/acc.php?action=login" method="post">
			<div class="control-group row">
				<input type="text" id="username" name="username" placeholder="Username" {if isset($smarty.get.tplUsername)} value="{$smarty.get.tplUsername|escape}"{/if} class="offset1 span10" required>
			</div>
			<div class="control-group row">
				<input type="password" id="password" name="password" placeholder="Password" class="offset1 span10" required>
			</div>
			<div class="control-group row">
					<button type="submit" class="btn btn-primary btn-block btn-large offset1 span10">Sign in</button>
			</div>
			<div class="control-group row">
				<div class="offset1 span5">
					<a class="btn btn-block" href="{$baseurl}/acc.php?action=forgotpw">Forgot password?</a>
				</div>
				<div class="span5">
					<a class="btn btn-block" href="{$baseurl}/acc.php?action=register">Register</a>
				</div>
			</div>
		</form>
	</div>
</div>
