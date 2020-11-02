{extends file="base.tpl"}
{block name="navmenu"}{/block}
{block name="modals"}{/block}
{block name="sitenotice"}{/block}
{block name="footerjs"}{/block}
{block name="content"}
<div class="row">
    <div class="col-md-12">
        <h1>Whoops!</h1>
        <p>After much experimentation, someone finally managed to kill ACC. So, the tool is currently offline while our resident developers pound their skulls against the furniture.</p>
        <p>Apparently, this is supposed to fix it.</p>
        <p>Once the nature of the problem is known, we will insert it here: <b>{$dontUseDbReason}</b></p>
        {if !$hideCulprit}<p>Once the identity of the culprit(s) is known, trout should be applied here: <b>{$dontUseDbCulprit}</b></p>{/if}
        <p>Although the tool is dead and the Bot is sleeping, email still works fine. So, we expect a swarm of irate potential editors to bury us in requests shortly. Please keep an eye on the mailing list. Remember to 'cc' or 'bcc' the mailing list address when you reply to let others know you have replied.</p>
        <p>For more information, join IRC, check the mailing list or just light candles – they may help too.</p>
    </div>
</div>
{/block}
