{extends file="base.tpl"}
{block name="content"}
<div class="row-fluid">
    <h2>Account Declined</h2>
    <p>I'm sorry, but, your account request was declined at this time.</p>
    <p>The reason given is shown here:</p>
    <p class="well">{$declineReason}</p>
</div>
{/block}
