{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="form-group row">
        <div class="col">
            <div class="d-block mx-auto w-fit-content py-5">
                <img src="{$baseurl}/resources/yubikey.svg" alt="" />
            </div>
        </div>
    </div>

    <input type="hidden" name="token" value="/+" />
    <button id="webauthnAuthenticate" class="btn btn-block btn-primary" type="button">Authenticate with WebAuthn</button>
{/block}
