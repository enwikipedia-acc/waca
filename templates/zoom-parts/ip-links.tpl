<!-- tpl:zoom-parts/ip-links.tpl -->
<a id="IPTalk-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User_talk:{$ipaddress}" OnClick="$('#IPTalk-{$index}').addClass('btn-inverse');">Talk page</a>
<a id="IPLocalContribs-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress}" OnClick="$('#IPLocalContribs-{$index}').addClass('btn-inverse');">Local Contributions</a>
<a id="IPDelEdits-{$index}" class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=tparis-pcount&amp;data={$ipaddress}" OnClick="$('#IPDelEdits-{$index}').addClass('btn-inverse');">Deleted Edits</a>
<a id="IPGlobalContribs-{$index}" class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$ipaddress}" OnClick="$('#IPGlobalContribs-{$index}').addClass('btn-inverse');">Global Contributions</a>
<a id="IPLocalBlockLog-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ipaddress}" OnClick="$('#IPLocalBlockLog-{$index}').addClass('btn-inverse');">Local Block Log</a>
<a id="IPActiveLocalBlock-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:BlockList/{$ipaddress}" OnClick="$('#IPActiveLocalBlock-{$index}').addClass('btn-inverse');">Active Local Blocks</a>
<a id="IPGlobalBlockLog-{$index}" class="btn btn-small" target="_blank" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ipaddress}" OnClick="$('#IPGlobalBlockLog-{$index}').addClass('btn-inverse');">Global Block Log</a>
<a id="IPActiveGlobalBlock-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ipaddress}" OnClick="$('#IPActiveGlobalBlock-{$index}').addClass('btn-inverse');">Active Global Blocks</a>
<a id="IPWhois-{$index}" class="btn btn-small" target="_blank" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$ipaddress}" OnClick="$('#IPWhois-{$index}').addClass('btn-inverse');">Whois</a>
<a id="IPAbuseLog-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ipaddress}" OnClick="$('#IPAbuseLog-{$index}').addClass('btn-inverse');">Abuse Filter Log</a>
{if $ischeckuser == true}
  <a id="IPCU-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}" OnClick="$('#IPCU-{$index}').addClass('btn-inverse');">CheckUser</a>
{/if}
<!-- /tpl:zoom-parts/ip-links.tpl -->
