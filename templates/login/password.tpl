{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="form-group row">
        <div class="col">
            <label for="password" class="sr-only">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" class="form-control" required tabindex="2">
            <span class="form-text text-muted float-right"><a href="{$baseurl}/internal.php/forgotPassword" tabindex="5">Forgotten your password?</a></span>
        </div>
    </div>
{/block}
