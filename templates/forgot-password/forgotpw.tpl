{extends file="base.tpl"}
{block name="content"}
    <h3 class="text-center mt-5 mb-4">Forgot password?</h3>
    <div class="row mb-3">
        <div class="col-md-4 offset-md-4">
            {include file="sessionalerts.tpl"}
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 offset-md-4">
            <div class="card card-body">
                <form method="post">
                    {include file="security/csrf.tpl"}
                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <input class="form-control" type="text" id="username" name="username" placeholder="Username" class="form-control" required="required"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-10 offset-md-1">
                            <input class="form-control" type="text" id="email" name="email" placeholder="Email" class="form-control" required="required"/>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="offset-md-1 col-md-10">
                            <button type="submit" class="btn btn-primary btn-block btn-large form-control">Submit</button>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="offset-md-1 col-md-10">
                            <a class="btn btn-block btn-outline-secondary" href="{$baseurl}/internal.php/login">Return to login</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}
