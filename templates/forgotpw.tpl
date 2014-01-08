<div class="row-fluid">
        <div class="offset4 span4">
                {$errorbar}
        </div>
</div>
<div class="row-fluid">
        <div class="offset4 span4 well">
                <h3 class="text-center">Account Creation Interface - Forgot Password</h3>
                <form class="container-fluid" action="{$tsurl}/acc.php?action=forgotpw" method="post">o
                        <div class="control-group row">
                                <input type="text" id="username" name="username" placeholder="Username" class="offset2 span8" required>
                        </div>
                        <div class="control-group row">
                                <input type="text" id="email" name="email" placeholder="Email" class="offset2 span8" required>
                        </div>
                        <div class="control-group row">
                                        <button type="submit" class="btn btn-primary btn-block btn-large span8 offset2">Submit</button>
                        </div>
                </form>
        </div>
</div>
