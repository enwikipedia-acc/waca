{block name="requestDataPrimary"}
    <div class="row">
        <div class="col-md-4">
            <strong>Email address:</strong>
            <span class="float-right ml-1 badge{if $requestRelatedEmailRequestsCount > 0} badge-danger{else} badge-secondary{/if} badge-pill">{$requestRelatedEmailRequestsCount}</span>
        </div>
        <div class="col-md-8">
            {if !$requestDataCleared}
                <a href="mailto:{$requestEmail|escape:'url'}">{$requestEmail|escape}</a>
            {else}
                <span class="text-muted font-italic">Email address purged</span>
            {/if}
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <strong>IP address:</strong>
            <span class="float-right ml-1 badge{if $requestRelatedIpRequestsCount > 0} badge-danger{else} badge-secondary{/if} badge-pill">{$requestRelatedIpRequestsCount}</span>
        </div>
        <div class="col-md-8">
            {if !$requestDataCleared}
                {$requestTrustedIp|escape}
                <br/>
                <span class="text-muted">
                  {if $requestTrustedIpLocation != null}
                      Location: {$requestTrustedIpLocation.cityName|escape}, {$requestTrustedIpLocation.regionName|escape}, {$requestTrustedIpLocation.countryName|escape}
                  {else}
                      <em>Location unavailable</em>
                  {/if}
                </span>
            {else}
                <span class="text-muted font-italic">IP address purged</span>
            {/if}
        </div>
    </div>
{/block}
