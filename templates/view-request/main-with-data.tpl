{extends file="view-request/main.tpl"}

{include file="view-request/request-private-data.tpl"}

{block name="requestDataRevealLink"}
    {if $showRevealLink}
        (
        <a href="{$baseurl}/internal.php/viewRequest?id={$requestId}&amp;hash={$revealHash}">reveal to others</a>
        )
    {/if}
{/block}

{block name="createButton"}
      <a class="btn btn-primary btn-block" target="_blank"
         href="{$mediawikiScriptPath}?title=Special:UserLogin/signup&amp;wpName={$requestName|escape:'url'}&amp;email={$requestEmail|escape:'url'}&amp;reason={$createAccountReason|escape:'url'}{$requestId}&amp;wpCreateaccountMail=true"
              {if !$currentUser->getAbortPref() && $createdHasJsQuestion} onclick="return confirm('{$createdJsQuestion}')"{/if}>
          Create account
      </a>
    <hr class="zoom-button-divider"/>
{/block}

{block name="requestStatusButtons"}
    {include file="view-request/request-status-buttons.tpl"}
{/block}

{block name="banSection"}
    {if $canSetBan}
        <h5 class="zoom-button-header">Ban</h5>
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
                <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/bans/set?type=IP&amp;request{$requestId}">
                    Ban IP
                </a>
            </div>
        </div>
        <hr class="zoom-button-divider"/>
    {/if}
{/block}

{block name="ipSection"}
    {include file="view-request/ip-section.tpl"}
{/block}

{block name="emailSection"}
    {include file="view-request/email-section.tpl"}
{/block}

{block name="otherRequests"}
    <div class="row">
        <div class="col-md-6">
            <h3>Other requests from this email address</h3>
            {if $requestDataCleared}
                <p class="text-muted">Email information cleared</p>
            {elseif $requestRelatedEmailRequestsCount == 0}
                <p class="text-muted">None detected</p>
            {else}
                {include file="view-request/related-requests.tpl" requests=$requestRelatedEmailRequests}
            {/if}
        </div>
        <div class="col-md-6">
            <h3>Other requests from this IP address</h3>
            {if $requestDataCleared}
                <p class="text-muted">IP information cleared</p>
            {elseif $requestRelatedIpRequestsCount == 0}
                <p class="text-muted">None detected</p>
            {else}
                {include file="view-request/related-requests.tpl" requests=$requestRelatedIpRequests}
            {/if}
        </div>
    </div>
{/block}
