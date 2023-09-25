{extends file="base.tpl"}
{block name="content"}
    <h3 class="text-center mt-5 mb-4">Reset password</h3>
    <div class="row mb-3">
        <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            {include file="sessionalerts.tpl"}
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            <div class="card card-body">
                <form method="post">
                    {include file="security/csrf.tpl"}
                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <label for="newpassword" class="sr-only">New password</label>
                            <input type="password" id="newpassword" name="newpassword" placeholder="Password" class="form-control password-strength" required="required" autocomplete="new-password"/>
                            <div class="progress password-strength-progress">
                                <div class="progress-bar" id="password-strength-bar"></div>
                            </div>
                            <span class="form-text text-danger" id="password-strength-warning"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <label for="newpasswordconfirm" class="sr-only">Confirm password</label>
                            <input type="password" id="newpasswordconfirm" name="newpasswordconfirm" placeholder="Confirm password" class="form-control" required="required" autocomplete="new-password"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <button type="submit" class="btn btn-primary btn-block btn-large form-control">Submit</button>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <a class="btn btn-block btn-outline-secondary form-control" href="{$baseurl}/internal.php/login">Return to login</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}
