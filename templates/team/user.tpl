<div class="col mb-4">
<div class="card">
    <div class="card-header py-2">
        <h5 class="mb-0">{$devName}</h5>
    </div>
    <div class="card-body py-1">

        {if isset($devInfo['wiki'])}
            <div class="row">
                <div class="col-4 pr-0 pl-1"><i class="fab fa-wikipedia-w"></i> Wikipedia:</div>
                <div class="col-8"><a href="http://en.wikipedia.org/wiki/User:{$devInfo['wiki']|escape:'url'}">{$devInfo['wiki']|escape}</a></div>
            </div>
        {/if}
        {if isset($devInfo['Name'])}
            <div class="row">
                <div class="col-4 pr-0 pl-1"><i class="fas fa-signature"></i> Name:</div>
                <div class="col-8 pr-1">{$devInfo['Name']|escape}</div>
            </div>
        {/if}

        {if isset($devInfo['ToolID'])}
            <div class="row">
                <div class="col-4 pr-0 pl-1"><i class="fas fa-user"></i> Tool user:</div>
                <div class="col-8 pr-1"><a href="{$baseurl}/internal.php/statistics/users/detail?user={$devInfo['ToolID']|escape:'url'}">Click here</a></div>
            </div>
        {/if}

        {foreach from=$devInfo key=infoName item=infoContent}
            {if $infoContent != null}
                {if $infoName == "IRC"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-comments"></i> IRC:</div>
                        <div class="col-8 pr-1">{$infoContent|escape}</div>
                    </div>
                    {if isset($devInfo['Cloak'])}
                    <div class="row">
                        <div class="col-12 text-right pr-1"><code>{$devInfo['Cloak']|escape}</code></div>
                    </div>
                    {/if}
                {/if}
                {if $infoName == "GitHub"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fab fa-github"></i> GitHub:</div>
                        <div class="col-8"><a href="https://github.com/{$infoContent|escape:'url'}">{$infoContent|escape}</a></div>
                    </div>
                {/if}
                {if $infoName == "WWW"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-globe"></i> Website:</div>
                        <div class="col-8"><a href="{$infoContent}">{$infoContent|escape}</a></div>
                    </div>
                {/if}
                {if $infoName == "Other"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-file"></i> Other:</div>
                        <div class="col-8">{$infoContent|escape}</div>
                    </div>
                {/if}
                {if $infoName == "Role"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-user-tag"></i> Roles:</div>
                        <div class="col-8">
                            <ul class="mb-1 pl-0">
                                <li>{$infoContent|join:'</li><li>'}</li>
                            </ul>
                        </div>
                    </div>
                {/if}
                {if $infoName == "Retired"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-user-tag"></i> Former roles:</div>
                        <div class="col-8">
                            <ul class="mb-1 pl-0">
                                <li>{$infoContent|join:'</li><li>'}</li>
                            </ul>
                        </div>
                    </div>
                {/if}
                {if $infoName == "Access"}
                    <div class="row">
                        <div class="col-4 pr-0 pl-1"><i class="fas fa-lock-open"></i> Access:</div>
                        <div class="col-8">
                            <ul class="mb-1 pl-0">
                                <li>{$infoContent|join:'</li><li>'}</li>
                            </ul>
                        </div>
                    </div>
                {/if}
            {/if}
        {/foreach}
    </div>
</div>
</div>
