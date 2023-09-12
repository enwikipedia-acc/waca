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
                    <form method="post">
                        {include file="security/csrf.tpl"}
                        {include file="alert.tpl" alertblock="true" alerttype="alert-info" alertclosable=false alertheader="Provide credentials" alertmessage="To enable your YubiKey OTP multi-factor credentials, please prove you are who you say you are by providing your tool password below."}
                        <div class="form-group row">
                            <div class="col">
                                <label class="sr-only" for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Password" class="form-control"
                                       required tabindex="2" autocomplete="password">
                            </div>
                            <input type="hidden" name="stage" value="auth">
                        </div>

                        <p class="card-text">
                            Please place your cursor in the field below, and press the button on your YubiKey.
                        </p>

                        <div class="form-group row">
                            <div class="col">
                                <label class="sr-only" for="otp">YubiKey OTP</label>
                                <input type="text" id="otp" name="otp" placeholder="YubiKey OTP" class="form-control"
                                       required tabindex="2" autocomplete="one-time-code">
                            </div>
                            <input type="hidden" name="stage" value="auth">
                        </div>

                        <div class="form-group row">
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-block" tabindex="3">Enable YubiKey OTP</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/block}
