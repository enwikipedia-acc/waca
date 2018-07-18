{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-4 offset-md-4 card card-body">
            <h3 class="text-center">Forgot password?</h3>
            {include file="sessionalerts.tpl"}
            <form class="container-fluid" method="post">
                {include file="security/csrf.tpl"}
                <div class="form-group row">
                    <input class="form-control" type="text" id="username" name="username" placeholder="Username" class="form-control col-md-8 offset-md-2"
                           required="required"/>
                </div>
                <div class="form-group row">
                    <input class="form-control" type="text" id="email" name="email" placeholder="Email" class="form-control col-md-8 offset-md-2"
                           required="required"/>
                </div>
                <div class="form-group row">
                    <button type="submit" class="btn btn-primary btn-block btn-large form-control col-md-8 offset-md-2">Submit</button>
                </div>
                <div class="form-group row">
                    <a class="btn btn-block col-md-8 offset-md-2" href="{$baseurl}/internal.php/login">Return to login</a>
                </div>
            </form>
        </div>
    </div>
{/block}
