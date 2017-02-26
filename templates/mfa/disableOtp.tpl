{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Disable Multi-factor credentials</h1>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 offset4 well">
                <form class="container-fluid" style="margin-top:20px;" method="post">
                    {include file="security/csrf.tpl"}
                    {include file="alert.tpl" alertblock="true" alerttype="alert-danger" alertclosable=false alertheader="Provide credentials" alertmessage="To disable your {$otpType|escape} multi-factor credentials, please prove you are who you say you are by providing the information below."}
                    <div class="row-fluid">
                        <input type="password" id="password" name="password" placeholder="Password" class="span12"
                               required tabindex="2">
                    </div>

                    <div class="row-fluid">
                        <button type="submit" class="btn btn-danger btn-block span12" tabindex="3">Disable {$otpType|escape}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}