?<!-- tpl:zoom-parts/username-section.tpl -->
<div class="row-fluid">
  <h3>Username data:</h3>
  {if $isblacklisted}
    {include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Requested Username is Blacklisted"
      alertmessage="The requested username is currently blacklisted by the regular expression <code>{$blacklistregex|escape}</code>."}
  {/if}
           
  <div class="btn-group">
    <a id="UsernameUserPage" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User:{$usernamerawunicode|escape:'url'}" OnClick="$('#UsernameUserPage').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">User page</a>
    <a id="UsernameCreationLog" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$usernamerawunicode|escape:'url'}" OnClick="$('#UsernameCreationLog').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Creation log</a>
    <a id="UsernameSUL" class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=sulutil&amp;data={$usernamerawunicode|escape:'url'}" OnClick="$('#UsernameSUL').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">SUL</a>
    <a id="UsernameCentralAuth" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$usernamerawunicode|escape:'url'}" OnClick="$('#UsernameCentralAuth').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Special:CentralAuth</a>
    <a id="UsernameUsernameList" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$usernamerawunicode|escape:'url'}&amp;group=&amp;limit=1" OnClick="$('#UsernameUsernameList').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Username list</a>
    <a id="UsernameMainspaceSearch" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$usernamerawunicode|escape:'url'}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced" OnClick="$('#UsernameMainspaceSearch').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Wikipedia mainspace search</a>
    <a id="UsernameGoogleSearch" class="btn btn-small" target="_blank" href="https://www.google.com/search?q={$usernamerawunicode|escape:'url'}" OnClick="$('#UsernameGoogleSearch').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Google search</a>
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
            <a id="SpoofContribs-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}" OnClick="$('#SpoofContribs-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Contributions</a>
            <a id="SpoofLogs-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A{$spoof|escape:'url'}&amp;year=&amp;month=-1&amp;tagfilter=&amp;hide_patrol_log=1&amp;hide_review_log=1&amp;hide_thanks_log=1" OnClick="$('#SpoofLogs-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Logs</a>
            <a id="SpoofSUL-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$spoof|escape:'url'}" OnClick="$('#SpoofSUL-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">SUL</a>
            <a id="SpoofCentralAuth-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}" OnClick="$('#SpoofCentralAuth-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Special:CentralAuth</a>
            <a id="SpoofPassReset-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}" OnClick="$('#SpoofPassReset-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Send Password reset</a>
            <a id="SpoofCount-{$spoof|escape:'url'}" class="btn btn-small" target="_blank" href="https://tools.wmflabs.org/xtools/pcount/index.php?lang=en&amp;wiki=wikipedia&amp;name={$spoof|escape:'url'}" OnClick="$('#SpoofCount-{$spoof|escape:'url'}').css('background-image', 'linear-gradient(to bottom, rgb(0, 255, 0), rgb(0, 230, 0))';">Count</a>
          </td>
        </tr>
      {/foreach}
    </table>
  {/if}
</div>
<!-- /tpl:zoom-parts/username-section.tpl -->
