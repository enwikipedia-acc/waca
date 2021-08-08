{extends file="pagebase.tpl"}
{block name="content"}
<div class="row">
    <div class="col-md-12" >
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Exception log<small> for exception <code>{$id}</code>{if $globalHandler}&nbsp;<span class=" badge badge-danger">Unhandled</span>{/if}</small></h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {foreach from=$exceptionList key=i item=ex}
            <h5>Exception {$i}: {$ex.exception|escape}</h5>
            <p>{$ex.message|escape}</p>
            <p class="prewrap">{$ex.stack}</p>
        {/foreach}
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <h3>GET parameters</h3>
        <table class="table table-sm">
            {foreach from=$get key=key item=value}
                <tr><th>{$key|escape}</th><td>{$value|escape}</td></tr>
                {foreachelse}
                <tr><td colspan="2"><i class="text-muted">None.</i></td></tr>
            {/foreach}
        </table>
    </div>
    <div class="col-lg-6">
        <h3>POST parameters</h3>
        <table class="table table-sm">
            {foreach from=$post key=key item=value}
                <tr><th>{$key|escape}</th><td>{$value|escape}</td></tr>
                {foreachelse}
                <tr><td colspan="2"><i class="text-muted">None.</i></td></tr>
            {/foreach}
        </table>
    </div>
    <div class="col-lg-12">
        <h3>SERVER parameters</h3>
        <table class="table table-sm">
            {foreach from=$server key=key item=value}
                <tr><th>{$key|escape}</th><td>{$value|escape}</td></tr>
                {foreachelse}
                <tr><td colspan="2"><i class="text-muted">None.</i></td></tr>
            {/foreach}
        </table>
    </div>
</div>
{/block}