{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="row-fluid">
        <input type="text" id="otp" name="otp" placeholder="Enter your one-time code" class="span12"
               required tabindex="2">
    </div>
{/block}