<h5>AntiSpoof results:</h5>
{if !$spoofs}
    <p class="text-muted">None detected</p>
{elseif !is_array($spoofs)}
    <div class="alert alert-danger">
        {$spoofs|escape}
    </div>
{else}
    <table class="table table-sm table-striped">
        {foreach $spoofs as $spoof}
            {if $spoof == $requestName}
                <div class="alert alert-info alert-block">
                    <strong>Note:</strong> This account currently exists - it may have already been created.
                    <a id="SpoofPassReset-{$spoof@iteration}" class="btn btn-sm btn-outline-info visit-tracking" target="_blank"
                       href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}">
                        Send Password reset
                    </a>
                </div>
                {continue}
            {/if}
            <tr>
                <td><a target="_blank" href="https://en.wikipedia.org/wiki/User:{$spoof|escape:'url'}">{$spoof}</a></td>
                <td>
                    <div class="linkWrapSection">
                        <a id="SpoofContribs-{$spoof@iteration}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}">
                            Contributions
                        </a>
                        <a id="SpoofLogs-{$spoof@iteration}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
                           href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1">
                            Logs
                        </a>
                        <a id="SpoofCentralAuth-{$spoof@iteration}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}">
                            Special:CentralAuth
                        </a>
                        <a id="SpoofPassReset-{$spoof@iteration}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}">
                            Send Password reset
                        </a>
                        <a id="SpoofCount-{$spoof@iteration}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
                           href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$spoof|escape:'url'}">
                            Count
                        </a>
                    </div>
                </td>
            </tr>
        {/foreach}
    </table>
{/if}

<h5>Title Blacklist results:</h5>
{if $gettingtbl}
    <p class="text-muted">{$gettingtbl} - {$tbldomain} - {$tblendpoint} - {$tbldata}</p>
{/if}
{if !$requestBlacklist}
    <p class="text-muted">None detected</p>
{elseif !is_array($requestBlacklist)}
    <div class="alert alert-danger">
        {$requestBlacklist|escape}
    </div>
{/if}
