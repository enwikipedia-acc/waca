{extends file="view-request/main.tpl"}

{block name="requestDataPrimary"}
    <div class="row-fluid hidden-phone">
        <div class="span4">
            <strong>Email address:</strong>
        </div>
        <div class="span7">
            <a href="mailto:{$requestEmail|escape:'url'}">{$requestEmail|escape}</a>
        </div>
        <div class="span1">
            <span class="badge{if $requestRelatedEmailRequestsCount > 0} badge-important{/if}">{$requestRelatedEmailRequestsCount}</span>
        </div>
    </div>
    <div class="row-fluid hidden-phone">
        <div class="span4"><strong>IP address:</strong></div>
        <div class="span7">
            {$requestTrustedIp|escape}
            <br/>
        <span class="muted">
          {if $requestTrustedIpLocation != null}
              Location: {$requestTrustedIpLocation.cityName|escape}, {$requestTrustedIpLocation.regionName|escape}, {$requestTrustedIpLocation.countryName|escape}
          {else}
              <em>Location unavailable</em>
          {/if}
        </span>
        </div>
        <div class="span1"><span class="label label-info">XFF</span><span
                    class="badge{if $requestRelatedIpRequestsCount > 0} badge-important{/if}">{$requestRelatedIpRequestsCount}</span>
        </div>
    </div>
{/block}

{block name="requestDataRevealLink"}
    {if $showRevealLink}
        (
        <a href="{$baseurl}/internal.php/viewRequest?id={$requestId}&amp;hash={$revealHash}">reveal to others</a>
        )
    {/if}
{/block}

{block name="createButton"}
    <div class="row-fluid">
        <a class="btn btn-primary span12" target="_blank"
           href="{$mediawikiScriptPath}?title=Special:UserLogin/signup&amp;wpName={$requestName|escape:'url'}&amp;wpEmail={$requestEmail|escape:'url'}&amp;wpReason={$createAccountReason|escape:'url'}{$requestId}&amp;wpCreateaccountMail=true"
                {if !$currentUser->getAbortPref() && $createdHasJsQuestion} onclick="return confirm('{$createdJsQuestion}')"{/if}>
            Create account
        </a>
    </div>
    <hr class="zoom-button-divider"/>
{/block}

{block name="requestStatusButtons"}
    {include file="view-request/request-status-buttons.tpl"}
{/block}

{block name="banSection"}
    <div class="row-fluid">
        <h5 class="zoom-button-header">Ban</h5>
    </div>
    <div class="row-fluid">
        <a class="btn btn-danger span4" href="{$baseurl}/internal.php/bans/set?type=Name&amp;request={$requestId}">
            Ban Username
        </a>
        <a class="btn btn-danger span4" href="{$baseurl}/internal.php/bans/set?type=EMail&amp;request={$requestId}">
            Ban Email
        </a>
        <a class="btn btn-danger span4" href="{$baseurl}/internal.php/bans/set?type=IP&amp;request{$requestId}">
            Ban IP
        </a>
    </div>
    <hr class="zoom-button-divider"/>
{/block}

{block name="ipSection"}
    {include file="view-request/ip-section.tpl"}
{/block}

{block name="otherRequests"}
    <div class="row-fluid">
        <div class="span6">
            <h3>Other requests from this email address</h3>
            {if $requestDataCleared}
                <p class="muted">Email information cleared</p>
            {elseif $requestRelatedEmailRequestsCount == 0}
                <p class="muted">None detected</p>
            {else}
                {include file="view-request/related-requests.tpl" requests=$requestRelatedEmailRequests}
            {/if}
        </div>
        <div class="span6">
            <h3>Other requests from this IP address</h3>
            {if $requestDataCleared}
                <p class="muted">IP information cleared</p>
            {elseif $requestRelatedIpRequestsCount == 0}
                <p class="muted">None detected</p>
            {else}
                {include file="view-request/related-requests.tpl" requests=$requestRelatedIpRequests}
            {/if}
        </div>
    </div>
{/block}