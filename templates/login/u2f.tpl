{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="form-group row">
        <div class="col">
            <p class="card-text text-center">Please press the button on your U2F device</p>
            <p class="card-text text-center">If your U2F device doesn't have a button, please remove and insert it</p>

            <div class="d-block mx-auto w-fit-content py-5">
                <img src="{$baseurl}/resources/yubikey.svg" alt="" />
            </div>
        </div>
    </div>

    <form id="u2fForm" method="post">
        <input type="hidden" name="authenticate" id="authenticate" />
        <input type="hidden" name="request" id="request" />
    </form>
{/block}
