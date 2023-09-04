{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Change password</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            <div class="card mb-5" id="loginCredentialForm">
                <div class="card-body p-4">
                    <h4>Change password</h4>
                    <p>To change your password, please enter your current password and desired new password below.</p>
                    {include file="alert.tpl" alertblock="true" alerttype="alert-warning" alertclosable=false alertheader="" alertmessage="Use of your Wikimedia credentials is highly discouraged in this tool. You should use a different password for your account than you would on projects like Wikipedia, Wikimedia Commons, etc."}

                    <form method="post" class="password-form">
                        {include file="security/csrf.tpl"}

                        <div class="form-group">
                            <label class="col-form-label" for="inputOldpassword">Your old password</label>
                            <input class="form-control" type="password" id="inputOldpassword" name="oldpassword" required="required" autocomplete="password"/>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="inputNewpassword">Your new password</label>
                            <input class="form-control password-strength" type="password" id="inputNewpassword" name="newpassword" required="required" autocomplete="new-password"/>
                            <div class="progress password-strength-progress">
                                <div class="progress-bar" id="password-strength-bar"></div>
                            </div>
                            <span class="form-text text-danger" id="password-strength-warning"></span>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="inputNewpasswordconfirm">Confirm new password</label>
                            <input class="form-control" type="password" id="inputNewpasswordconfirm" name="newpasswordconfirm" required="required" autocomplete="new-password"/>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-primary">Update password</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
{/block}
