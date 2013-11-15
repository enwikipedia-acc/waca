<!-- tpl:zoom-parts/request-info.tpl -->
<div class="row-fluid visible-phone">
    <div class="span12">
        <h4>Request data:</h4>
        <table class="table table-condensed table-striped">
        <tbody>
            {if $showinfo}
            <tr>
                <th>Email address:</th>
                <td><a href="mailto:{$email}">{$email}</a></td>
                <td><span class="badge{if $numemail > 0} badge-important{/if}">{$numemail}</span></td>
            </tr>
            <tr>
                <th>IP address:</th>
                <td>
                  {$ip}
                  <br />
                  <span class="muted">
                    {if $iplocation != null}
                      Location: {$iplocation.cityName}, {$iplocation.regionName}, {$iplocation.countryName}
                    {else}
                      <em>Location unavailable</em>
                    {/if}
                  </span>
                </td>
                <td>
                    {if $proxyip != NULL}<span class="label label-info">XFF</span>{/if}
                    <span class="badge{if $numip > 0} badge-important{/if}">{$numip}</span>
                </td>
            </tr>
            {/if}
            <tr><th>Requested name:</th><td>{$username}</td><td></td></tr>
            <tr><th>Date:</th><td>{$date}</td><td></td></tr>
            {if $viewuseragent}<tr><th>User Agent:</th><td>{$useragent}</td><td></td></tr>{/if}
            {if $youreserved && $isclosed}
            <tr>
                <th>Reveal link:</th>
                <td>
                <a href="{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}">
                    {$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}
                </a></td>
                <td></td>
            </tr>
            {/if}
            <tr><th>Reserved by:</th><td>{if $reserved}{$reserved}{else}None{/if}</td>
            <td></td></tr>
        </tbody>
    </table>
  </div>
</div>
{if $showinfo}
  <div class="row-fluid hidden-phone">
    <div class="span4"><strong>Email address:</strong></div>
    <div class="span7"><a href="mailto:{$email}">{$email}</a></div>
    <div class="span1"><span class="badge{if $numemail > 0} badge-important{/if}">{$numemail}</span></div>
  </div>
  <div class="row-fluid hidden-phone">
      <div class="span4"><strong>IP address:</strong></div>
      <div class="span7">
        {$ip}
        <br />
        <span class="muted">
          {if $iplocation != null}
            Location: {$iplocation.cityName}, {$iplocation.regionName}, {$iplocation.countryName}
          {else}
            <em>Location unavailable</em>
          {/if}
        </span>
      </div>
      <div class="span1"><span class="label label-info">XFF</span><span class="badge{if $numip > 0} badge-important{/if}">{$numip}</span></div>
  </div>
{/if}
<div class="row-fluid hidden-phone">
    <div class="span4"><strong>Requested name:</strong></div>
    <div class="span8">{$username}</div>
</div>
<div class="row-fluid hidden-phone">
    <div class="span4"><strong>Date:</strong></div>
    <div class="span8">{$date}</div>
</div>
{if $viewuseragent}
  <div class="row-fluid hidden-phone">
    <div class="span4"><strong>User Agent:</strong></div>
    <div class="span8">{$useragent}</div>
  </div>
{/if}
{if $youreserved == $tooluser && $isclosed}
  <div class="row-fluid hidden-phone">
    <div class="span4"><strong>Reveal link:</strong></div>
    <div class="span8"><a href="{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}">{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}</a></div>
  </div>
{/if}
<div class="row-fluid hidden-phone">
  <div class="span4"><strong>Reserved by:</strong></div>
  <div class="span8">{if $reserved}{$reserved}{else}None{/if}</div>
</div>
<!-- /tpl:zoom-parts/request-info.tpl -->