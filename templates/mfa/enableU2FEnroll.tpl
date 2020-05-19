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
                    <form method="post" id="u2fEnroll">
                        {include file="security/csrf.tpl"}
                        <h4 class="card-title">Register your device</h4>

                        <p class="card-text text-center">Please press the button on your U2F device</p>
                        <p class="card-text text-center">If your U2F device doesn't have a button, please remove and insert it</p>

                        <div class="d-block mx-auto w-fit-content py-5">
                            <img src="{$baseurl}/resources/yubikey.svg" alt="" />
                        </div>

                        <input type="hidden" name="u2fData" id="u2fData" />
                        <input type="hidden" name="u2fRequest" id="u2fRequest" />
                        <input type="hidden" name="stage" value="enroll">
                    </form>
                </div>
            </div>
        </div>
    </div>
{/block}
