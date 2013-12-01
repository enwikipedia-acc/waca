<!-- tpl:zoom-parts/username-section.tpl -->
<div class="row-fluid">
  <h4>Username data:</h4>        
  {if $isblacklisted}
    {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Requested Username is Blacklisted"
      alertmessage="The requested username is currently blacklisted by the regular expression <code>{$blacklistregex|escape}</code>."}
  {/if}
           
  <div class="btn-group">
    <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User:{$usernamerawunicode|escape:'url'}">User page</a>
    <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$usernamerawunicode|escape:'url'}">Creation log</a>
    <a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=sulutil&amp;data={$usernamerawunicode|escape:'url'}">SUL</a>
    <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$usernamerawunicode|escape:'url'}">Special:CentralAuth</a>
    <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$usernamerawunicode|escape:'url'}&amp;group=&amp;limit=1">Username list</a>
    <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$usernamerawunicode|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced">Wikipedia mainspace search</a>
    <a class="btn btn-small" target="_blank" href="https://www.google.com/search?q={$usernamerawunicode|escape:'url'}">Google search</a>
  </div>
            
  <h5>AntiSpoof results:</h5>
  {if !$spoofs}
    <p class="muted">None detected</p>
  {elseif !is_array($spoofs)}
    <div class="alert alert-error">{$spoofs}</div>
  {else}
    <table class="table table-condensed table-striped">
      {foreach $spoofs as $spoof}
        {if $spoof == $username}
          <tr>
            <td></td>
            <td><h3>Note: This account has already been created</h3></td>
          </tr>
          {continue}
        {/if}
        <tr>
          <td><a target="_blank" href="https://en.wikipedia.org/wiki/User:{$spoof|escape:'url'}">{$spoof}</a></td>
          <td>
            <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}">User page</a>
            <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1">Logs</a>
            <a class="btn btn-small" target="_blank" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$spoof|escape:'url'}">SUL</a>
            <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}">Special:CentralAuth</a>
            <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}">Send Password reset</a>
            <a class="btn btn-small" target="_blank" href="https://tools.wmflabs.org/xtools/pcount/index.php?lang=en&amp;wiki=wikipedia&amp;name={$spoof|escape:'url'}">Count</a>
          </td>
        </tr>
      {/foreach}
    </table>
  {/if}
</div>
<!-- /tpl:zoom-parts/username-section.tpl -->