<!-- tpl:zoom-parts/username-section.tpl -->
<div class="row-fluid">
  <h3>Username data:</h3>
  {if $isblacklisted}
    {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Requested Username is Blacklisted"
      alertmessage="The requested username is currently blacklisted by the regular expression <code>{$blacklistregex|escape}</code>."}
  {/if}
           
  <div class="btn-group">
    <a id="UsernameUserPage" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User:{$usernamerawunicode|escape:'url'}" onMouseUp="$('#UsernameUserPage').addClass('btn-inverse');">User page</a>
    <a id="UsernameCreationLog" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$usernamerawunicode|escape:'url'}" onMouseUp="$('#UsernameCreationLog').addClass('btn-inverse');">Creation log</a>
    <a id="UsernameSUL" class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=sulutil&amp;data={$usernamerawunicode|escape:'url'}" onMouseUp="$('#UsernameSUL').addClass('btn-inverse');">SUL</a>
    <a id="UsernameCentralAuth" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$usernamerawunicode|escape:'url'}" onMouseUp="$('#UsernameCentralAuth').addClass('btn-inverse');">Special:CentralAuth</a>
    <a id="UsernameUsernameList" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$usernamerawunicode|escape:'url'}&amp;group=&amp;limit=1" onMouseUp="$('#UsernameUsernameList').addClass('btn-inverse');">Username list</a>
    <a id="UsernameMainspaceSearch" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$usernamerawunicode|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced" onMouseUp="$('#UsernameMainspaceSearch').addClass('btn-inverse');">Wikipedia mainspace search</a>
    <a id="UsernameGoogleSearch" class="btn btn-small" target="_blank" href="https://www.google.com/search?q={$usernamerawunicode|escape:'url'}" onMouseUp="$('#UsernameGoogleSearch').addClass('btn-inverse');">Google search</a>
  </div>
            
  <h4>AntiSpoof results:</h4>
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
            <a id="SpoofContribs-{$spoof@iteration}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}" onMouseUp="$('#SpoofContribs-{$spoof@iteration}').addClass('btn-inverse');">Contributions</a>
            <a id="SpoofLogs-{$spoof@iteration}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1" onMouseUp="$('#SpoofLogs-{$spoof@iteration}').addClass('btn-inverse');">Logs</a>
            <a id="SpoofSUL-{$spoof@iteration}" class="btn btn-small" target="_blank" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$spoof|escape:'url'}" onMouseUp="$('#SpoofSUL-{$spoof@iteration}').addClass('btn-inverse');">SUL</a>
            <a id="SpoofCentralAuth-{$spoof@iteration}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}" onMouseUp="$('#SpoofCentralAuth-{$spoof@iteration}').addClass('btn-inverse');">Special:CentralAuth</a>
            <a id="SpoofPassReset-{$spoof@iteration}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}" onMouseUp="$('#SpoofPassReset-{$spoof@iteration}').addClass('btn-inverse');">Send Password reset</a>
            <a id="SpoofCount-{$spoof@iteration}" class="btn btn-small" target="_blank" href="https://tools.wmflabs.org/xtools/pcount/index.php?lang=en&amp;wiki=wikipedia&amp;name={$spoof|escape:'url'}" onMouseUp="$('#SpoofCount-{$spoof@iteration}').addClass('btn-inverse');">Count</a>
          </td>
        </tr>
      {/foreach}
    </table>
  {/if}
</div>
<!-- /tpl:zoom-parts/username-section.tpl -->
