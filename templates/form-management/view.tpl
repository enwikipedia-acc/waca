{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Request Form Management <small class="text-muted">Create and edit request forms</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h3>
                Request form: {$name|escape}
                <small>
                    {if $enabled}
                        <span class="badge badge-success">Enabled</span>
                    {else}
                        <span class="badge badge-danger">Disabled</span>
                    {/if}
                </small>
            </h3>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <ul class="list-unstyled">
                <li><strong>Public endpoint:</strong>&nbsp;<a href="{$baseurl|escape}/index.php/{$endpoint|escape}">{$baseurl|escape}/index.php/{$endpoint|escape}</a></li>
                <li><strong>Override queue:</strong>&nbsp;{if $queue === null}(Default queue){else}{$queueObject->getHeader()|escape}{/if}</li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <iframe src="{$baseurl}/internal.php/requestFormManagement/preview" class="preview-frame"></iframe>
        </div>
    </div>
{/block}