	  <div class="row-fluid">
		<!-- page header -->
		<div class="span12">
			<h3>Details for Request #{$id}:</h3>
		</div>
	  </div><!--/row-->	 
        
      <div class="row-fluid">
		<!-- request details -->
          <div class="span6 container-fluid">
               <div class="row-fluid visible-phone">
                       <div class="span12">
                           <h4>Request data:</h4>
                           <table class="table table-condensed table-striped">
                          <tbody>
                              {if $showinfo}<tr><th>Email address:</th><td><a href="mailto:{$email}">{$email}</a></td><td><span class="badge{if $numemail > 0} badge-important{/if}">{$numemail}</span></td></tr>
                              <tr><th>IP address:</th><td>{$ip}</td><td>{if $proxyip != NULL}<span class="label label-info">XFF</span>{/if}<span class="badge{if $numip > 0} badge-important{/if}">{$numip}</span></td></tr>{/if}
                              <tr><th>Requested name:</th><td>{$username}</td><td></td></tr>
                              <tr><th>Date:</th><td>{$date}</td><td></td></tr>
                              {if $viewuseragent}<tr><th>User Agent:</th><td>{$useragent}</td><td></td></tr>{/if}
                              {if $youreserved && $isclosed}<tr><th>Reveal link:</th><td><a href="{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}">{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}</a></td><td></td></tr>{/if}
                              <tr><th>Reserved by:</th><td>{if $reserved}{$reserved}{else}None{/if}</td>
                              <td></td></tr>
                          </tbody>
                      </table>
                  </div>
              </div>
              {if $showinfo}<div class="row-fluid hidden-phone">
                  <div class="span4"><strong>Email address:</strong></div>
                  <div class="span7"><a href="mailto:{$email}">{$email}</a></div>
                  <div class="span1"><span class="badge{if $numemail > 0} badge-important{/if}">{$numemail}</span></div>
              </div>
              <div class="row-fluid hidden-phone">
                  <div class="span4"><strong>IP address:</strong></div>
                  <div class="span7">{$ip}</div>
                  <div class="span1"><span class="label label-info">XFF</span><span class="badge{if $numip > 0} badge-important{/if}">{$numip}</span></div>
              </div>{/if}
              <div class="row-fluid hidden-phone">
                  <div class="span4"><strong>Requested name:</strong></div>
                  <div class="span8">{$username}</div>
              </div>
              <div class="row-fluid hidden-phone">
                  <div class="span4"><strong>Date:</strong></div>
                  <div class="span8">{$date}</div>
              </div>
              {if $viewuseragent}<div class="row-fluid hidden-phone">
                  <div class="span4"><strong>User Agent:</strong></div>
                  <div class="span8">{$useragent}</div>
              </div>{/if}
              {if $youreserved == $tooluser && $isclosed}<div class="row-fluid hidden-phone">
                  <div class="span4"><strong>Reveal link:</strong></div>
                  <div class="span8"><a href="{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}">{$tsurl}/acc.php?action=zoom&amp;id={$id}&amp;hash={$hash}</a></div>
              </div>{/if}
              <div class="row-fluid hidden-phone">
                  <div class="span4"><strong>Reserved by:</strong></div>
                  <div class="span8">{if $reserved}{$reserved}{else}None{/if}</div>
              </div>
              
              <hr />
              
              <div class="row-fluid">
              {if $showinfo == true && $isprotected == false && $isreserved == true}
                  <div class="span6">
                      <a class="btn btn-primary span6 offset3" href="https://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&amp;wpName={$username}&amp;wpEmail={$email}&amp;wpReason=Requested+account+at+%5B%5BWP%3AACC%5D%5D%2C+request+%23111&amp;wpCreateaccountMail=true">Create account</a>
                  </div>
                  {/if}
                  <div class="span6">{if $youreserved}<a class="btn btn-inverse span6" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$id}">Break reservation</a>
				  {elseif $isadmin == true}<a class="btn span6 btn-warning" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$rid}">Force break</a>{/if}</div>
              </div>
              <hr />
              {if $isprotected == false}
              <div class="row-fluid">
                  {if !array_key_exists($type, $requeststates)}
                  <a class="btn span 3" href="{$tsurl}/acc.php?action=defer&amp;id={$id}&amp;sum={$checksum}&amp;target={$defaultstate}">Reset request</a>
                  {else}{foreach $requeststates as $state}
                  <a class="btn span3" href="{$tsurl}/acc.php?action=defer&amp;id={$id}&amp;sum={$checksum}&amp;target={$state@key}">{$state.deferto}</a>
                  {/foreach}{/if}
              </div>
              <hr/>    
              {/if}
                 
              <div class="row-fluid">
                  {if $isprotected == false}
                  {if $isreserved == true}
                  <a class="btn btn-success" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=1&amp;sum={$checksum}">Created</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=2&amp;sum={$checksum}">Similar</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=3&amp;sum={$checksum}">Taken</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=26&amp;sum={$checksum}">SUL Taken</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=4&amp;sum={$checksum}">UPolicy</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=5&amp;sum={$checksum}">Invalid</a>
                  <a class="btn btn-warning" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=30&amp;sum={$checksum}">Password reset</a>
                  <a class="btn btn-info" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=custom&amp;sum={$checksum}">Custom</a>
                  {/if}
                  <a class="btn btn-inverse" href="{$tsurl}/acc.php?action=done&amp;id={$id}&amp;email=0&amp;sum={$checksum}">Drop</a>
                  {/if}
              </div>
              {if $isadmin}
              <hr />
              <div class="row-fluid">
                  <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;name={$id}">Ban Username</a>
                  <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;email={$id}">Ban Email</a>
                  <a class="btn btn-danger span4" href="{$tsurl}/acc.php?action=ban&amp;ip={$id}">Ban IP</a>
              </div>
              {/if}
                  
          </div>
          <div class="span6 container-fluid">
          
              <div class="row-fluid">
                  <div class="span12">
                      <h4>Log:</h4>
                      <table class="table table-condensed table-striped">
                          <tbody>
							{if $zoomlogs}{foreach $zoomlogs as $zoomrow}
                          	  <tr><td>{if $zoomrow.userid != NULL}<a href='{$tsurl}/statistics.php?page=Users&amp;user={$zoomrow.userid}'>{$zoomrow.user}</a>{else}{$zoomrow.user}{/if}{if $zoomrow.security == "admin"}<br /><span style="color:red">(admin only)</span>{/if}</td><td><em>{$zoomrow.entry nofilter}</em></td><td>{$zoomrow.time}</td><td>{if $zoomrow.canedit == true}<a class="btn btn-small" href="{$tsurl}/acc.php?action=ec&amp;id={$zoomrow.id}">Edit</a></td></tr>{/if}
                          	{/foreach}
                          	{else}
                          	  <tr><td></td><td><em>None.</em></td><td></td><td></td>
                          	{/if}
                              <tr><td><a href="{$tsurl}/statistics.php?page=Users&amp;user={$userid}">{$tooluser}</a></td><td><form action='{$tsurl}/acc.php?action=comment-quick&amp;hash={$hash}' method='post'><input type='hidden' name='id' value='{$id}'/><input class="span12" placeholder="Quick comment"/></td><td><button class="btn btn-primary" type="submit">Save</button></form></td><td></td></tr>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
	  </div><!--/row-->	  
	  <hr />
     {if $showinfo == true}
     {if $proxyip != NULL}
        <div class="row-fluid">
            <h4>IP Address data:</h4>
            <p class="muted">This request came from {$ip}, stating it was forwarded for {$proxyip} via the X-Forwared-For HTTP header. The IP address which Wikipedia will see is the first "untrusted" IP address in the list below. Links are shown for all addresses starting from where the chain becomes untrusted. IPs past the first untrusted address are not trusted to be correct. Please see the <a href="https://toolserver.org/~acc/other/xff.html">XFF demo</a> for more details.</p>
            <h5>Forwarded IP addresses:</h5>    
            <table class="table table-condensed table-striped">
            {foreach $proxies as $proxy}
            	 <tr>
                    <td><span class="label {if $proxy.trust == false}label-important{/if}">{if $proxy.trust == false}un{/if}trusted</span>{if $origin == $proxy.ip}<span class="label label-inverse">origin</span>{/if}</td>
                    <td>{$proxy.ip}<br /><span class="muted">{if $proxy.rdns != NULL}RDNS: {$proxy.rdns}{elseif $proxy.routable == false}<em><a style="color:grey;" href="http://en.wikipedia.org/wiki/Private_network">Non-routable address</a></em>{elseif $proxy.rdnsfailed == true}<em>(unable to determine address)</em>{else}<em>(no rdns available)</em>{/if}</span></td>
                    <td>{if $proxy.trust == false && $proxy.routable == true && $proxy.rdnsfailed == false}<a class="btn btn-small" href="https://en.wikipedia.org/wiki/User talk:{$proxy.ip}">Talk page</a>
						<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:Contributions/{$proxy.ip}">Local Contributions</a>
						<a class="btn btn-small" href="{$tsurl}/redir.php?tool=tparis-pcount&amp;data={$proxy.ip}">Deleted Edits</a>
						<a class="btn btn-small" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$proxy.ip}">Global Contributions</a>
						<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$proxy.ip}">Local Block Log</a>
						<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:BlockList/{$proxy.ip}">Active Local Blocks</a>
						<a class="btn btn-small" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$proxy.ip}">Global Block Log</a>
						<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$proxy.ip}">Active Global Blocks</a>
						<a class="btn btn-small" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$proxy.ip}">Whois</a>
						<a class="btn btn-small" href="{$tsurl}/redir.php?tool=ipinfodb-locator&amp;data={$proxy.ip}">Geolocate</a>
						<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$proxy.ip}">Abuse Filter Log</a>
						{if $ischeckuser == true}<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$proxy.ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>{/if}
					{/if}</td></tr>
            {/foreach}
            </table>
        </div>
        {else}
        	<div class="row-fluid">
        		<h4>IP Address links:</h4>
        		<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:Contributions/{$ip}">Local Contributions</a>
				<a class="btn btn-small" href="{$tsurl}/redir.php?tool=tparis-pcount&data={$ip}">Deleted Edits</a>
				<a class="btn btn-small" href="{$tsurl}/redir.php?tool=luxo-contributions&amp;data={$ip}">Global Contributions</a>
				<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page={$ip}">Local Block Log</a>
				<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:BlockList/{$ip}">Active Local Blocks</a>
				<a class="btn btn-small" href="https://meta.wikimedia.org/w/index.php?title=Special:Log&amp;type=gblblock&amp;page={$ip}">Global Block Log</a>
				<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:GlobalBlockList/{$ip}">Active Global Blocks</a>
				<a class="btn btn-small" href="{$tsurl}/redir.php?tool=oq-whois&amp;data={$ip}">Whois</a>
				<a class="btn btn-small" href="{$tsurl}/redir.php?tool=ipinfodb-locator&amp;data={$ip}">Geolocate</a>
				<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:AbuseLog&amp;wpSearchUser={$ip}">Abuse Filter Log</a>
				{if $ischeckuser == true}<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:CheckUser&amp;ip={$ip}&amp;reason=%5B%5BWP:ACC%5D%5D%20request%20%23{$id}">CheckUser</a>{/if}
			</div>{/if}
        <hr />{/if}
        <div class="row-fluid">
            <h4>Username data:</h4>
           
            <div class="btn-group">
                <a class="btn btn-small" href="https://en.wikipedia.org/wiki/User:{$username}">User page</a>
                <a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page={$username}">Creation log</a>
                <a class="btn btn-small" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$username}">SUL</a>
                <a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$username}">Special:CentralAuth</a>
                <a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username={$username}&amp;group=&amp;limit=1">Username list</a>
                <a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special%3ASearch&amp;profile=advanced&amp;search={$username}&amp;fulltext=Search&amp;ns0=1&amp;redirs=1&amp;profile=advanced">Wikipedia mainspace search</a>
                <a class="btn btn-small" href="https://www.google.com/search?q={$username}">Google search</a>
            </div>
            
            {if $isblacklisted}<p><b>Requested username is blacklisted.</b></p>{/if}
            <h5>AntiSpoof results:</h5>
            {if !$spoofs}
            <p class="muted">None detected</p>
            {elseif !is_array($spoofs)}
            <h3 style='color: red'>{$spoofs}</h3>
            {else}
            	<table class="table table-condensed table-striped">
            	{foreach $spoofs as $spoof}
            	{if $spoof == $username}<tr><td></td><td><h3>Note: This account has already been created</h3></td>{continue}{/if}
            	<tr><td><a href="https://en.wikipedia.org/wiki/User:{$spoof|escape:'url'}">{$spoof}</a></td>
            	<td><a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:Contributions/{$spoof|escape:'url'}">User page</a>
            	<a class="btn btn-small" href="https://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page={$spoof|escape:'url'}">Logs</a>
            	<a class="btn btn-small" href="http://toolserver.org/~quentinv57/tools/sulinfo.php?showinactivity=1&amp;showblocks=1&amp;username={$spoof|escape:'url'}">SUL</a>
            	<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:CentralAuth/{$spoof|escape:'url'}">Special:CentralAuth</a>
            	<a class="btn btn-small" href="https://en.wikipedia.org/wiki/Special:PasswordReset?wpUsername={$spoof|escape:'url'}">Send Password reset</a>
            	<a class="btn btn-small" href="https://tools.wmflabs.org/xtools/pcount/index.php?lang=en&amp;wiki=wikipedia&amp;name={$spoof|escape:'url'}">Count</a></td></tr>
            	{/foreach}
            {/if}
        </div>
        
        <div class="row-fluid container-fluid">
            <div class="row-fluid">
                <div class="span6">
                    <h4>Other requests from {if $showinfo == true}{$ip}{else}this email address{/if}</h4>
                    {if $email == "acc@toolserver.org"}<p class="muted">Email information cleared</p>
                    {elseif $otheremail == false}<p class="muted">None detected</p>
                    {else}<table class="table table-condensed table-striped">
                    {foreach $otheremail as $others}
                    <tr><td>{$others.date}</td><td><a href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a></td></tr>
                    {/foreach}
                    </table>
                    {/if}
                </div>
                <div class="span6">
                    <h4>Other requests from {if $showinfo == true}{$ip}{else}this IP address{/if}</h4>
                    {if $ip == "127.0.0.1"}<p class="muted">IP information cleared</p>
                    {elseif $otherip == false}<p class="muted">None detected</p>
                    {else}<table class="table table-condensed table-striped">
                    {foreach $otherip as $others}
                    <tr><td>{$others.date}</td><td><a href="{$tsurl}/acc.php?action=zoom&amp;id={$others.id}">{$others.name}</a></td></tr>
                    {/foreach}
                    </table>
                    {/if}
                </div>
            </div>
        </div>
