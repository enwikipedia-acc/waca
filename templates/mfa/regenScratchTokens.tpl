{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Enable Multi-factor credentials</h1>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 offset4 well">
                <div class="container-fluid" style="margin-top:20px;">
                    <div class="row-fluid">
                        <h4>Your new emergency scratch tokens</h4>
                        <p>Below is your new set of scratch tokens. Please destroy any remaining scratch tokens you had from the last set - these are no longer valid.</p>
                        <p>Please keep these in a safe place - they're the only way you can get back into your account if you lose your code generating device. These codes will never be shown to you again, so please take a copy of them now!</p>
                        <p>Remember that each one can only be used once, so come back and generate some new emergency scratch tokens when you get low.</p>
                    </div>
                    <ul>
                        {foreach from=$tokens item="t"}
                            <li><var>{$t|escape}</var></li>
                        {/foreach}
                    </ul>
                    <div class="row-fluid">
                        <a class="btn btn-primary btn-block span12" href="{$baseurl}/internal.php/multiFactor">Continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}