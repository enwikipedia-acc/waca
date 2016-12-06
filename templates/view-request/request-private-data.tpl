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
        <div class="span1">
            <span class="label label-info">XFF</span>
            <span class="badge{if $requestRelatedIpRequestsCount > 0} badge-important{/if}">{$requestRelatedIpRequestsCount}</span>
        </div>
    </div>
{/block}