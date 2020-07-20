{extends file="view-request/main.tpl"}

{include file="view-request/request-private-data.tpl"}

{block name="declinedeferbuttons"}
{*    <div class="col-md-6">*}
        {include file="view-request/decline-button.tpl"}{include file="view-request/custom-button.tpl"}
{*    </div>*}
{/block}

{block name="banSection"}
    {if $canSetBan}
        <h5>Ban</h5>
        <div class="row">
            <div class="col-md-4">
                <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/bans/set?type=Name&amp;request={$requestId}">
                    Ban Username
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/bans/set?type=EMail&amp;request={$requestId}">
                    Ban Email
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/bans/set?type=IP&amp;request={$requestId}">
                    Ban IP
                </a>
            </div>
        </div>
        <hr/>
    {/if}
{/block}

{block name="usernameSection"}
    {include file="view-request/username-section.tpl"}
    <hr />
{/block}

{block name="ipSection"}
    {if !$requestDataCleared}
        {include file="view-request/ip-section.tpl"}

        <hr/>
    {/if}
{/block}

{block name="emailSection"}
    {include file="view-request/email-section.tpl"}
{/block}

{block name="manualcreationbutton"}
    {include file="view-request/createbuttons/manual.tpl"}
{/block}
