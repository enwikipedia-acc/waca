<h4>AntiSpoof results:</h4>
{if !$spoofs}
    <p class="muted">None detected</p>
{elseif !is_array($spoofs)}
    <div class="alert alert-error">
        {$spoofs|escape}
    </div>
{else}
    <table class="table table-condensed table-striped">
        {foreach $spoofs as $spoof}
            {if $spoof == $requestName}
                <tr>
                    <td></td>
                    <td>
                        <h3>Note: This account has already been created
                            <a id="SpoofPassReset-{$spoof@iteration}" class="btn btn-small" target="_blank"
                               href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}"
                               onMouseUp="$('#SpoofPassReset-{$spoof@iteration}').addClass('btn-visited');">
                                Send Password reset
                            </a>
                        </h3>
                    </td>
                </tr>
                {continue}
            {/if}
            <tr>
                <td><a target="_blank" href="https://en.wikipedia.org/wiki/User:{$spoof|escape:'url'}">{$spoof}</a></td>
                <td>
                    <div class="linkWrapSection">
                        <a id="SpoofContribs-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}"
                           onMouseUp="$('#SpoofContribs-{$spoof@iteration}').addClass('btn-visited');">
                            Contributions
                        </a>
                        <a id="SpoofLogs-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1"
                           onMouseUp="$('#SpoofLogs-{$spoof@iteration}').addClass('btn-visited');">
                            Logs
                        </a>
                        <a id="SpoofSUL-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="{$baseurl}/redir.php?tool=sulutil&amp;data={$spoof|escape:'url'}"
                           onMouseUp="$('#SpoofSUL-{$spoof@iteration}').addClass('btn-visited');">
                            SUL
                        </a>
                        <a id="SpoofCentralAuth-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}"
                           onMouseUp="$('#SpoofCentralAuth-{$spoof@iteration}').addClass('btn-visited');">
                            Special:CentralAuth
                        </a>
                        <a id="SpoofPassReset-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}"
                           onMouseUp="$('#SpoofPassReset-{$spoof@iteration}').addClass('btn-visited');">
                            Send Password reset
                        </a>
                        <a id="SpoofCount-{$spoof@iteration}" class="btn btn-small" target="_blank"
                           href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$spoof|escape:'url'}"
                           onMouseUp="$('#SpoofCount-{$spoof@iteration}').addClass('btn-visited');">
                            Count
                        </a>
                    </div>
                </td>
            </tr>
        {/foreach}
    </table>
{/if}