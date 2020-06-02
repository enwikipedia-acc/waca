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
                    <h4 class="card-title">Register your device</h4>

                    <p class="card-text text-center">Please enter a name for this authenticator, and click the button below to start the enrollment process.</p>

                    <div class="d-block mx-auto w-fit-content py-5">
                        <img src="{$baseurl}/resources/yubikey.svg" alt="" />
                    </div>

                    <input type="hidden" name="enrollment" value="{$enrollment}" />
                    <div class="form-group row">
                        <div class="col">
                            <label class="sr-only" for="authenticatorName">Authenticator name</label>
                            <input type="text" name="authenticatorName" value="" class="form-control" placeholder="My hardware token" />
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <button id="beginRegistration" class="btn btn-block btn-primary">Enroll device</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}
