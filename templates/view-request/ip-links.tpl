<div class="linkWrapSection">
    <a id="IPTalk-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/User_talk:{$ipaddress}">
        Talk page
    </a>
    <a id="IPLocalContribs-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress}">
        Local Contributions
    </a>
    <a id="IPDelEdits-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$ipaddress}">
        Deleted Edits
    </a>
    <div class="btn-group">
        <a id="IPGlobalContribs-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=luxo-contributions&amp;data={$ipaddress}">
            Global Contribs (GCW)
        </a>
        <a id="IPGUC-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=guc&amp;data={$ipaddress}">
            (GUC)
        </a>
    </div>
    <a id="IPLocalBlockLog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ipaddress}">
        Local Block Log
    </a>
    <a id="IPActiveLocalBlock-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/Special:BlockList/{$ipaddress}">
        Active Local Blocks
    </a>
    <a id="IPGlobalBlockLog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ipaddress}">
        Global Block Log
    </a>
    <a id="IPActiveGlobalBlock-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ipaddress}">
        Active Global Blocks
    </a>
    <div class="btn-group">
        <a id="IPWhois-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=oq-whois&amp;data={$ipaddress}">
            Whois
        </a>
        <a id="IPWhois2-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=tl-whois&amp;data={$ipaddress}">
            (alt)
        </a>
    </div>
    <a id="IPAbuseLog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ipaddress}">
        Abuse Filter Log
    </a>
    {if $canSeeCheckuserData}
        <a id="IPCU-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$requestId}">
            CheckUser
        </a>
        <a id="IPCULog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="https://en.wikipedia.org/wiki/Special:CheckUserLog?cuSearchType=target&cuSearch={$ipaddress}">
            CU Log
        </a>
    {/if}
</div>
