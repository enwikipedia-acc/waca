<div class="row-fluid">
  <div class="span12">
	  <h2>Request an account!</h2>
	  <p>We need a few bits of information to create your account. However, you do not need an account to read the encyclopedia or look up information - that can be done by anyone with or without an account. The first is a username, and secondly, a <strong>valid email address that we can send your password to</strong> (please don't use temporary inboxes, or email aliasing, as this may cause your request to be rejected). If you want to leave any comments, feel free to do so. Note that if you use this form, your IP address will be recorded, and displayed to <a href="{$baseurl}/statistics.php?page=Users">those who review account requests</a>. When you are done, click the "Submit" button. If you have difficulty using this tool, send an email containing your account request (but not password) to <a href="mailto:accounts-enwiki-l@lists.wikimedia.org">accounts-enwiki-l@lists.wikimedia.org</a>, and we will try to deal with your requests that way.</p>

	  <div class="alert alert-block">
		  <h4>Please note!</h4>
		  We do not have access to existing account data. If you have lost your password, please reset it using <a href="http://en.wikipedia.org/wiki/Special:UserLogin">this form</a> at wikipedia.org. If you are trying to 'take over' an account that already exists, please use <a href="http://en.wikipedia.org/wiki/WP:CHU/U">"Changing usernames/Usurpation"</a> at wikipedia.org. We cannot do either of these things for you.
	  </div>
  </div>
</div>

<div class="row-fluid">
  <div class="span12">
	  <form class="form-horizontal" method="post">
		  <div class="control-group">
			  <label class="control-label" for="inputUsername">Username</label>
			  <div class="controls">
				  <input type="text" id="inputUsername" placeholder="Username" name="name"><span class="help-block">Case sensitive, first letter is always capitalized, you do not need to use all uppercase. Note that this need not be your real name. Please make sure you don't leave any trailing spaces or underscores on your requested username.</span>
			  </div>
		  </div>
		  <div class="control-group">
			  <label class="control-label" for="inputEmail">Email</label>
			  <div class="controls">
				  <input type="email" id="inputEmail" placeholder="Email" name="email">
			  </div>
		  </div>
		  <div class="control-group">
			  <label class="control-label" for="inputEmailConfirm">Confirm Email</label>
			  <div class="controls">
				  <input type="email" id="inputEmailConfirm" placeholder="Confirm Email" name="emailconfirm"><span class="help-block">We need this to send you your password. Without it, you will not receive your password, and will be unable to log in to your account.</span>
			  </div>
		  </div>
		  <div class="control-group">
			  <label class="control-label" for="inputComments">Comments</label>
			  <div class="controls">
				  <textarea id="inputComments" rows="4" name="comments"></textarea><span class="help-block">Please do NOT ask for a specific password. One will be randomly created for you.</span>
			  </div>
		  </div>
		  <div class="control-group">
			  <div class="controls">
				  <button type="submit" class="btn">Send request</button>
			  </div>
		  </div>
	  </form>
  </div>
</div>