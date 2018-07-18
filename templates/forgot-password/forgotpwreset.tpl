{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="offset-md-4 col-md-4 card card-body">
            <h3 class="text-center">Reset password for {$user->getUsername()|escape} ({$user->getEmail()|escape})</h3>
            {include file="sessionalerts.tpl"}
            <form class="container-fluid" method="post">
                {include file="security/csrf.tpl"}
                <div class="form-group row">
                    <input type="password" id="password" name="pw" placeholder="Password" class="form-control col-md-8 offset-md-2"
                           required="required"/>
                </div>
                <div class="form-group row">
                    <input type="password" id="password" name="pw2" placeholder="Password" class="form-control col-md-8 offset-md-2"
                           required="required"/>
                </div>
                <div class="form-group row">
                    <button type="submit" class="btn btn-primary btn-block btn-large form-control col-md-8 offset-md-2">Submit</button>
                </div>
                <div class="form-group row">
                    <a class="btn btn-block form-control col-md-8 offset-md-2" href="{$baseurl}/internal.php/login">Return to login</a>
                </div>
            </form>
        </div>
    </div>
{/block}
