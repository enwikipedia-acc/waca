{extends file="base.tpl"}
{block name="content"}
    <br />
    <h3 class="text-center">Account Creation Interface</h3>
    <div class="row">
        <div class="col-md-4 offset-md-4">
            {include file="alert.tpl" alertblock="false" alerttype="alert-info" alertclosable=false alertheader="" alertmessage="<strong>You're not logged in!</strong> Please log in to continue."}
            {include file="sessionalerts.tpl"}
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 offset-md-4">
            <div class="card card-body">
                <form method="post">
                    {include file="security/csrf.tpl"}
                    <div class="form-group row">
                        <div class="offset-md-1 col-md-10">
                            <input type="text" id="username" name="username"
                                   placeholder="Username" {if isset($smarty.get.tplUsername)} value="{$smarty.get.tplUsername|escape}"{/if}
                                   class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="offset-md-1 col-md-10">
                            <input type="password" id="password" name="password" placeholder="Password" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="offset-md-1 col-md-10">
                            <button type="submit" class="btn btn-primary btn-block btn-large form-control">Sign in</button>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="offset-lg-1 col-lg-5 col-xs-12">
                            <a class="btn btn-secondary btn-block" href="{$baseurl}/internal.php/forgotPassword" >Forgot password?</a>
                        </div>
                        <div class="col-lg-5 col-xs-12 float-md-right">
                            <a class="btn btn-secondary btn-block" href="{$baseurl}/internal.php/register">Register</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}
