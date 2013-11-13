<!-- tpl:zoom-parts/ip-links.tpl -->
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User_talk:{$ipaddress}">Talk page</a>
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress}">Local Contributions</a>
<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=tparis-pcount&amp;data={$ipaddress}">Deleted Edits</a>
<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$ipaddress}">Global Contributions</a>
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ipaddress}">Local Block Log</a>
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:BlockList/{$ipaddress}">Active Local Blocks</a>
<a class="btn btn-small" target="_blank" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ipaddress}">Global Block Log</a>
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ipaddress}">Active Global Blocks</a>
<a class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$ipaddress}">Whois</a>
<a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ipaddress}">Abuse Filter Log</a>
{if $ischeckuser == true}
  <a class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>
{/if}
<!-- /tpl:zoom-parts/ip-links.tpl -->