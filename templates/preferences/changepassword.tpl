<form class="form-horizontal" method="post" action="{$baseurl}/acc.php?action=changepassword">
    <fieldset>
        <legend>Change your password</legend>
        <div class="control-group">
            <label class="control-label" for="inputOldpassword">Your old password</label>
            <div class="controls">
                <input class="input-xlarge" type="password" id="inputOldpassword" name="oldpassword"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputNewpassword">Your new password</label>
            <div class="controls">
                <input class="input-xlarge" type="password" id="inputNewpassword" name="newpassword"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="inputNewpasswordconfirm">Confirm new password</label>
            <div class="controls">
                <input class="input-xlarge" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm"/>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">Update password</button>
            </div>
        </div>
    </fieldset>
</form>