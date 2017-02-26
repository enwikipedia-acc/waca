{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="row-fluid">
        <input type="password" id="password" name="password" placeholder="Password" class="span12"
               required style="margin-bottom: 0px;" tabindex="2">
    </div>
    <div class="row-fluid">
        <p style="margin-bottom: 10px;font-size: small;text-align: right;">
            <a class="muted" href="{$baseurl}/internal.php/forgotPassword" tabindex="5">Forgotten your password?</a>
        </p>
    </div>
{/block}