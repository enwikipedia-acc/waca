{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Enable Multi-factor credentials</h1>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 offset4 well">
                <form class="container-fluid" style="margin-top:20px;" method="post">
                    {include file="security/csrf.tpl"}
                    <div class="row-fluid">
                        <h4>Set up your app</h4>
                        <p>Scan the image above with the two-factor authentication app on your phone.</p>
                        <p class="muted">If you can’t use a barcode, enter this text code instead: <code>{$secret|escape}</code></p>

                        <div style="float:none; margin-right: auto; margin-left:auto; width: 256px;">
                            {$svg}
                        </div>

                        <h4>Enter the six-digit code from the application</h4>
                        <p>After scanning the barcode image, the app will display a six-digit code that you can enter below.</p>
                        <p>You will need to provide the latest six-digit code from this app whenever you log in.</p>
                    </div>

                    <div class="row-fluid">
                        <input type="number" id="otp" name="otp" placeholder="One-time token" class="span12"
                               required tabindex="2">
                        <input type="hidden" name="stage" value="enroll">
                    </div>

                    <div class="row-fluid">
                        <button type="submit" class="btn btn-primary btn-block span12" tabindex="3">Verify token and enable</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}