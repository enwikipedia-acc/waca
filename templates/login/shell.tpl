{extends file="base.tpl"}
{block name="content"}
    <h3 class="text-center">Account Creation Interface</h3>

    <div class="row-fluid">
        <div class="offset4 span4 well">
            {include file="sessionalerts.tpl"}
            <form class="container-fluid" style="margin-top:20px;" method="post" id="loginCredentialForm">
                {include file="security/csrf.tpl"}

                {if $partialStage !== 1}
                    {include file="alert.tpl" alertblock="true" alerttype="alert-info" alertclosable=false alertheader="Provide multi-factor credentials" alertmessage="Welcome, {$username|escape}. To continue the login process, provide your multi-factor credentials."}
                {else}
                    <div class="row-fluid">
                        <input type="text" id="username" name="username" placeholder="Username" value="{$username|escape}"
                               class="span12" required tabindex="1"
                               style="margin-bottom: 0px;">
                    </div>

                    <div class="row-fluid">
                        <p style="margin-bottom: 10px;font-size: small;text-align: right;">
                            <a class="muted" href="{$baseurl}/internal.php/register" tabindex="4">No tool account? Register!</a>
                        </p>
                    </div>
                {/if}

                {block name="credentialform"}{/block}

                {if $showSignIn}
                    <div class="row-fluid">
                        <button type="submit" class="btn btn-primary btn-block btn-large span12" tabindex="3">Sign in</button>
                    </div>
                {/if}

                {if count($alternatives) > 0 }
                    <div class="row-fluid">
                        <hr />
                        {foreach from=$alternatives key="path" item="authmethods"}
                            <p style="margin-bottom: 0px;font-size: small;text-align: center;">
                                <a href="{$baseurl}/internal.php/login/{$path}" class="muted">Use {$authmethods|nlimplode} instead?</a>
                            </p>
                        {/foreach}
                    </div>
                {/if}
            </form>
        </div>
    </div>
{/block}