{extends file="base.tpl"}
{block name="content"}
    <h3 class="text-center mt-5 mb-4">Account Creation Interface</h3>

    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            <div class="card mb-5" id="loginCredentialForm">
                <div class="card-body p-4">
                    {include file="sessionalerts.tpl"}
                    <form method="post" id="loginForm">
                        {include file="security/csrf.tpl"}

                        {if $partialStage !== 1}
                            {include file="alert.tpl" alertblock="true" alerttype="alert-info" alertclosable=false alertheader="Provide multi-factor credentials" alertmessage="Welcome, {$username|escape}. To continue the login process, provide your multi-factor credentials."}
                        {else}
                            <div class="form-group row">
                                <div class="col">
                                    <label for="username" class="sr-only">Username</label>
                                    <input type="text" id="username" name="username" placeholder="Username" autocomplete="username"
                                           value="{$username|escape}" class="form-control" required tabindex="1">
                                    <span class="form-text text-muted float-right">No tool account? <a class="" href="{$baseurl}/internal.php/register" tabindex="4">Register!</a></span>
                                </div>
                            </div>
                        {/if}

                        {block name="credentialform"}{/block}

                        {if $showSignIn}
                            <div class="form-group row">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary btn-block" tabindex="3">Sign in</button>
                                </div>
                            </div>
                        {/if}
                    </form>

                    {if count($alternatives) > 0 || $partialStage !== 1 }
                        <hr />
                        <div class="row cols split-cols">
                            {foreach from=$alternatives key="path" item="authmethods"}
                                <div class="col">
                                    <p class="text-center mb-0">
                                        <small><a href="{$baseurl}/internal.php/login/{$path}" class="muted">Use {$authmethods|nlimplode} instead?</a></small>
                                    </p>
                                </div>
                            {/foreach}
                            {if $partialStage !== 1}
                                <div class="col">
                                    <form method="post" action="{$baseurl}/internal.php/logout">
                                        <p class="text-center mb-0">
                                            <button type="submit" class="btn btn-link p-0"><small>Cancel login</small></button>
                                        </p>
                                    </form>
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/block}
