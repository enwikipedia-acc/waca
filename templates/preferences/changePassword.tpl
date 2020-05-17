{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Change password</h1>
            </div>
        </div>
    </div>

    <form method="post">
        {include file="security/csrf.tpl"}

        <div class="form-group row">
            <div class="col-md-3 col-lg-2">
                <label class="col-form-label" for="inputOldpassword">Your old password</label>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <input class="form-control" type="password" id="inputOldpassword" name="oldpassword" required="required"/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 col-lg-2">
                <label class="col-form-label" for="inputNewpassword">Your new password</label>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <input class="form-control" type="password" id="inputNewpassword" name="newpassword" required="required"/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 col-lg-2">
                <label class="col-form-label" for="inputNewpasswordconfirm">Confirm new password</label>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <input class="form-control" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required"/>
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-md-3 offset-lg-2 col-md-6 col-lg-4 col-xl-3">
                <button type="submit" class="btn btn-block btn-primary">Update password</button>
            </div>
        </div>
    </form>
{/block}
