{extends file="login/shell.tpl"}
{block name="credentialform"}
    <div class="row-fluid">
        <div style="text-align: center">
            <p>Please press the button on your U2F device</p>
            <p>If your U2F device doesn't have a button, please remove and insert it</p>

            {include file="icons/yubikey.tpl"}
        </div>
    </div>

    <form id="u2fForm" method="post">
        <input type="hidden" name="authenticate" id="authenticate" />
        <input type="hidden" name="request" id="request" />
    </form>
{/block}