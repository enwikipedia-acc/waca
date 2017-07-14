<!-- tpl:zoom-parts/ip-links.tpl -->
<div class="linkWrapSection">
  <a id="IPTalk-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/User_talk:{$ipaddress}" onMouseUp="$('#IPTalk-{$index}').addClass('btn-visited');">Talk page</a>
  <a id="IPLocalContribs-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress}" onMouseUp="$('#IPLocalContribs-{$index}').addClass('btn-visited');">Local Contributions</a>
  <a id="IPDelEdits-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$ipaddress}" onMouseUp="$('#IPDelEdits-{$index}').addClass('btn-visited');">Deleted Edits</a>
  <div class="btn-group">
    <a id="IPGlobalContribs-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=luxo-contributions&amp;data={$ipaddress}" onMouseUp="$('#IPGlobalContribs-{$index}').addClass('btn-visited');">Global Contribs (GCW)</a>
    <a id="IPGUC-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=guc&amp;data={$ipaddress}" onMouseUp="$('#IPGUC-{$index}').addClass('btn-visited');">(GUC)</a>
  </div>
  <a id="IPLocalBlockLog-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ipaddress}" onMouseUp="$('#IPLocalBlockLog-{$index}').addClass('btn-visited');">Local Block Log</a>
  <a id="IPActiveLocalBlock-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:BlockList/{$ipaddress}" onMouseUp="$('#IPActiveLocalBlock-{$index}').addClass('btn-visited');">Active Local Blocks</a>
  <a id="IPGlobalBlockLog-{$index}" class="btn btn-small" target="_blank" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ipaddress}" onMouseUp="$('#IPGlobalBlockLog-{$index}').addClass('btn-visited');">Global Block Log</a>
  <a id="IPActiveGlobalBlock-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ipaddress}" onMouseUp="$('#IPActiveGlobalBlock-{$index}').addClass('btn-visited');">Active Global Blocks</a>
  <div class="btn-group">
    <a id="IPWhois-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=tl-whois&amp;data={$ipaddress}" onMouseUp="$('#IPWhois-{$index}').addClass('btn-visited');">Whois</a>
    <a id="IPWhois2-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=oq-whois&amp;data={$ipaddress}" onMouseUp="$('#IPWhois2-{$index}').addClass('btn-visited');">(alt)</a>
  </div>
  <a id="IPHoneypot-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=honeypot&amp;data={$ipaddress}" onMouseUp="$('#IPHoneypot-{$index}').addClass('btn-visited');">Project Honeypot</a>
  <a id="IPStopForumSpam-{$index}" class="btn btn-small" target="_blank" href="{$baseurl}/redir.php?tool=stopforumspam&amp;data={$ipaddress}" onMouseUp="$('#IPStopForumSpam-{$index}').addClass('btn-visited');">StopForumSpam</a>
  <a id="IPAbuseLog-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ipaddress}" onMouseUp="$('#IPAbuseLog-{$index}').addClass('btn-visited');">Abuse Filter Log</a>
  {if $currentUser->isCheckUser() == true}
    <a id="IPCU-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$request->getId()}" onMouseUp="$('#IPCU-{$index}').addClass('btn-visited');">CheckUser</a>
    <a id="IPCULog-{$index}" class="btn btn-small" target="_blank" href="https://en.wikipedia.org/wiki/Special:CheckUserLog?cuSearchType=target&cuSearch={$ipaddress}" onMouseUp="$('#IPCULog-{$index}').addClass('btn-visited');">CU Log</a>
  {/if}
</div>
<!-- /tpl:zoom-parts/ip-links.tpl -->
