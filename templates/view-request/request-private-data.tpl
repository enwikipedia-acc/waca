{block name="requestDataPrimary"}
    <div class="row">
        <div class="col-md-4">
            <strong>Email address:</strong>
            <span class="float-right ml-1 badge{if $requestRelatedEmailRequestsCount > 0} badge-danger{else} badge-secondary{/if} badge-pill">{$requestRelatedEmailRequestsCount}</span>
            {if !$commonEmailDomain}<span class="float-right ml-1 badge badge-warning" data-toggle="tooltip" title="The domain name of this email address is not from a common provider. Look out for potential COI issues."><i class="fas fa-gem"></i>Uncommon</span>{/if}
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
