{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Multi-factor credentials</h1>
            </div>
        </div>
    </div>

    <div class="row">
    <div class="col-xl-4 offset-xl-4 col-lg-6 offset-lg-3 col-md-8 offset-md-2">
        <div class="card mb-5" id="loginCredentialForm">
            <div class="card-body p-4">
                <form method="post">
                    {include file="security/csrf.tpl"}

                    <div class="form-group row">
                        <div class="col">
                            <h4 class="card-title">Set up your app</h4>
                            <p class="card-text">Scan the image below with the two-factor authentication app on your phone.</p>

                            <div class="d-block mx-auto w-fit-content py-5">
                                {$svg}
                            </div>

                            <p class="card-text text-muted">If you can’t use a barcode, enter this text code instead: <code>{$secret|escape}</code></p>

                            <h4>Enter the six-digit code from the application</h4>
                            <p>After scanning the barcode image, the app will display a six-digit code that you can enter below.</p>
                            <p>You will need to provide the latest six-digit code from this app whenever you log in.</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <label for="otp" class="sr-only">TOTP code</label>
                            <input type="number" id="otp" name="otp" placeholder="One-time token" class="form-control"
                                                            required tabindex="2">
                            <input type="hidden" name="stage" value="enroll">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary btn-block" tabindex="3">Verify token and enable</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}
