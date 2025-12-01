<div class="linkWrapSection">
    <a id="IPTalk-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/wiki/User_talk:{$ipaddress}">
        Talk page
    </a>
    <div class="btn-group">
        <a id="IPLocalContribs-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress}">
            Local Contributions
        </a>
        {if $protocol == 6}
            <a id="IPLocalContribs64-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://en.wikipedia.org/wiki/Special:Contributions/{$ipaddress|cidr:64}/64">
                /64
            </a>
        {/if}
    </div>
    <a id="IPDelEdits-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$ipaddress}">
        Deleted Edits
    </a>
    <a id="IPGUC-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=guc&amp;data={$ipaddress}">
        Global Contribs
    </a>
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
       href="https://meta.wikimedia.org/wiki/Special:GlobalBlockList/{$ipaddress}">
        Active Global Blocks
    </a>
    <a id="IPRangeFinder-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=rangefinder&amp;data={$ipaddress}">
        Rangeblock finder
    </a>
    <div class="btn-group">
        <a id="IPWhois-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=tl-whois&amp;data={$ipaddress}">
            Whois
        </a>
        <a id="IPWhois2-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=oq-whois&amp;data={$ipaddress}">
            (alt)
        </a>
    </div>
    <a id="IPHoneypot-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=honeypot&amp;data={$ipaddress}">
        Project Honeypot
    </a>
    <a id="IPStopForumSpam-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=stopforumspam&amp;data={$ipaddress|cidr:null}">
        StopForumSpam
    </a>
    <a id="IPAbuseLog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ipaddress}">
        Abuse Filter Log
    </a>
    <a id="IPCheck-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=ipcheck&amp;data={$ipaddress}">
        IP Check
    </a>
    <a id="Spur-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=spur&amp;data={$ipaddress}">
        Spur
    </a>
    <a id="Bullseye-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=bullseye&amp;data={$ipaddress}">
        Bullseye
    </a>
    {if $protocol == 6}
        <div id="IPalyzer-{$index}" class="btn btn-sm btn-outline-secondary disabled" data-toggle="tooltip" data-placement="bottom"
             title="This tool does not support IPv6 addresses.">
            IPalyzer
        </div>
    {else}
        <a id="IPalyzer-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
           href="{$baseurl}/redir.php?tool=ipalyzer&amp;data={$ipaddress}">
            IPalyzer
        </a>
    {/if}
    <a id="IPBGPView-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
       href="{$baseurl}/redir.php?tool=bgpview&amp;data={$ipaddress}">
        BGP Prefixes
    </a>
    {if $canSeeCheckuserData}
        <div class="btn-group">
            <a id="IPCU-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23%5B%5BACC%3A{$requestId}%7C{$requestId}%5D%5D">
                CheckUser
            </a>
            <a id="IPCULog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://en.wikipedia.org/wiki/Special:CheckUserLog?cuSearchType=target&cuSearch={$ipaddress}">
                Log
            </a>
            <a id="IPCUI-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://en.wikipedia.org/w/index.php?title=Special:Investigate&amp;targets={$ipaddress}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23%5B%5BACC%3A{$requestId}%7C{$requestId}%5D%5D">
                Investigate
            </a>
        </div>
        <div class="btn-group">
            <a id="IPLWCU-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://login.wikimedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ipaddress}&amp;reason=%5B%5B:en:WP:ACC%5D%5D%20request%20%23%5B%5BACC%3A{$requestId}%7C{$requestId}%5D%5D">
                LW CU
            </a>
            <a id="IPLWCULog-{$index}" class="btn btn-sm btn-outline-secondary visit-tracking" target="_blank"
               href="https://login.wikimedia.org/wiki/Special:CheckUserLog?cuSearchType=target&cuSearch={$ipaddress}">
                Log
            </a>
        </div>
    {/if}
</div>
