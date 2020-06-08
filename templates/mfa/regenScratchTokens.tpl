{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Multi-factor credentials <small class="text-muted">Enable multi-factor credentials</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            <div class="card mb-5" id="loginCredentialForm">
                <div class="card-body p-4">
                    <h4 class="card-title">Your new emergency scratch tokens</h4>
                    <p class="card-text">Below is your new set of scratch tokens. Please destroy any remaining scratch tokens you had from the last set - these are no longer valid.</p>
                    <p class="card-text">Please keep these in a safe place - they're the only way you can get back into your account if you lose your code generating device. <strong>These codes will never be shown to you again, so please take a copy of them now!</strong></p>
                    <p class="card-text">Remember that each one can only be used once, so come back and generate some new emergency scratch tokens when you get low.</p>
                    <ul>
                        {foreach from=$tokens item="t"}
                            <li><code>{$t|escape}</code></li>
                        {/foreach}
                    </ul>

                    <p class="card-text">
                        <a class="btn btn-primary btn-block" href="{$baseurl}/internal.php/multiFactor">Continue</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
{/block}
