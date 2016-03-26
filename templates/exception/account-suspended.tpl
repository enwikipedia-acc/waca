{extends file="base.tpl"}
{block name="content"}
<div class="row-fluid">
    <h2>Account Suspended</h2>
    <p>I'm sorry, but your account request was suspended.</p>
    <p>The reason given is shown here:</p>
    <p class="well">{$suspendReason}</p>
</div>
{/block}
