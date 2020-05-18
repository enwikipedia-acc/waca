{block name="requestDataPrimary"}
    <div class="row">
        <div class="col-md-4">
            <strong>Email address:</strong>
            <span class="float-right ml-1 badge{if $requestRelatedEmailRequestsCount > 0} badge-danger{else} badge-secondary{/if} badge-pill">{$requestRelatedEmailRequestsCount}</span>
        </div>
        <div class="col-md-8">
            <a href="mailto:{$requestEmail|escape:'url'}">{$requestEmail|escape}</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <strong>IP address:</strong>
            <span class="float-right ml-1 badge{if $requestRelatedIpRequestsCount > 0} badge-danger{else} badge-secondary{/if} badge-pill">{$requestRelatedIpRequestsCount}</span>
            <span class="float-right ml-1 badge badge-info badge-pill">XFF</span>
        </div>
        <div class="col-md-8">
            {$requestTrustedIp|escape}
            <br/>
            <span class="text-muted">
              {if $requestTrustedIpLocation != null}
                  Location: {$requestTrustedIpLocation.cityName|escape}, {$requestTrustedIpLocation.regionName|escape}, {$requestTrustedIpLocation.countryName|escape}
              {else}
                  <em>Location unavailable</em>
              {/if}
            </span>
        </div>
    </div>
{/block}
