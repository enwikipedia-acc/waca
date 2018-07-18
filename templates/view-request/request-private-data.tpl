{block name="requestDataPrimary"}
    <div class="row d-none-xs">
        <div class="col-md-4">
            <strong>Email address:</strong>
        </div>
        <div class="col-md-7">
            <a href="mailto:{$requestEmail|escape:'url'}">{$requestEmail|escape}</a>
        </div>
        <div class="col-md-1">
            <span class="badge{if $requestRelatedEmailRequestsCount > 0} badge-important{else} badge-secondary{/if}">{$requestRelatedEmailRequestsCount}</span>
        </div>
    </div>
    <div class="row d-none-xs">
        <div class="col-md-4"><strong>IP address:</strong></div>
        <div class="col-md-7">
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
        <div class="col-md-1">
            <span class="label label-info">XFF</span>
            <span class="badge{if $requestRelatedIpRequestsCount > 0} badge-important{else} badge-secondary{/if}">{$requestRelatedIpRequestsCount}</span>
        </div>
    </div>
{/block}
