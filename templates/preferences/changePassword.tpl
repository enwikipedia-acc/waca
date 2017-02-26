{extends file="pagebase.tpl"}
{block name="content"}
<div class="page-header">
    <h1>Change password</h1>
</div>
<form class="form-horizontal" method="post" action="{$baseurl}/internal.php/changePassword">
    {include file="security/csrf.tpl"}
    <div class="control-group">
        <label class="control-label" for="inputOldpassword">Your old password</label>
        <div class="controls">
            <input class="input-xlarge" type="password" id="inputOldpassword" name="oldpassword" required="required"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputNewpassword">Your new password</label>
        <div class="controls">
            <input class="input-xlarge" type="password" id="inputNewpassword" name="newpassword" required="required"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputNewpasswordconfirm">Confirm new password</label>
        <div class="controls">
            <input class="input-xlarge" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required"/>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Update password</button>
        </div>
    </div>
</form>
{/block}