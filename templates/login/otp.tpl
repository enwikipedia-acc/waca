{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="form-group row">
        <div class="col">
            <label for="otp" class="sr-only">OTP</label>
            <input type="password" id="otp" name="otp" placeholder="Enter your one-time code" class="form-control" required tabindex="2">
        </div>
    </div>
{/block}
