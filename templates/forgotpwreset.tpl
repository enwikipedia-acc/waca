<div class="row-fluid">
        <div class="offset4 span4 well">
                <h3 class="text-center">Reset password for {$user_name} ({$user_email})</h3>
                <form class="container-fluid" action="{$tsurl}/acc.php?action=forgotpw&si={$si}&id={$id}" method="post">
                        <div class="control-group row">
                                <input type="password" id="password" name="pw" placeholder="Password" class="offset2 span8" required>
                        </div>
                        <div class="control-group row">
                                 <input type="password" id="password" name="pw2" placeholder="Password" class="offset2 span8" required>
                        </div>
                        <div class="control-group row">
                                 <button type="submit" class="btn btn-primary btn-block btn-large span8 offset2">Submit</button>
                        </div>
                        <div class="control-group row">
                                 <a class="btn btn-block span8 offset2" href="{$tsurl}/acc.php">Return to login</a>
                        </div>
                </form>
        </div>
</div>

