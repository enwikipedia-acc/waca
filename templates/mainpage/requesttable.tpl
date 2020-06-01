<table class="table table-striped table-sm sortable mb-0 request-table">
    <thead>
    <tr>
        <th {defaultsort id="id" req=$sort dir=$dir}><span class="d-none d-sm-inline">#</span></th>
        {if $showStatus}
            <th {defaultsort id="status" req=$sort dir=$dir}><span class="d-none d-sm-inline">Request state</span></th>
        {/if}
        {if $list->showPrivateData}
            <th {defaultsort id="email" req=$sort dir=$dir}><span class="d-none d-md-inline">Email address</span></th>
            <th {defaultsort id="ip" req=$sort dir=$dir}>IP address</th>
        {/if}
        <th {defaultsort id="username" req=$sort dir=$dir}>Username</th>
        <th {defaultsort id="date" req=$sort dir=$dir}><span class="d-none d-md-inline">Request time</span></th>
        <th {defaultsort id="updated" req=$sort dir=$dir}><span class="d-none {if $list->showPrivateData}d-lg-inline{else}d-md-inline{/if}">Last updated</span></th>
        <th data-defaultsort="disabled"><!-- ban --></th>
        <th data-defaultsort="disabled"><!-- reserve status --></th>
        <th data-defaultsort="disabled"><!--reserve button--></th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$list->requests item="r"}
        <tr>
            <td data-value="{$r->getId()}">
                <a class="btn btn-sm{if $r->hasComments() == true} btn-info{else} btn-secondary{/if}"
                   {if $r->hasComments() == true}data-title="This request has comments" data-toggle="tooltip"{/if}
                href="{$baseurl}/internal.php/viewRequest?id={$r->getId()}"><i class="fas fa-search"></i><span class="d-none d-md-inline">&nbsp;{$r->getId()}</span></a>
            </td>

            {if $showStatus}
                <td><span class="d-none d-sm-inline">{$r->getStatus()}</span></td>
            {/if}

            {if $list->showPrivateData}
                <td>
                    {if $r->getEmail() === $list->dataClearEmail}
                        <span class="text-muted font-italic d-none d-md-inline">Email address purged</span>
                    {else}
                        <span class="d-none d-md-inline">
                            {$r->getEmail()|escape}
                            {if $list->relatedEmailRequests[$r->getId()] > 0}
                                <span class="badge badge-pill badge-danger"
                                    data-toggle="tooltip" data-original-title="{$list->relatedEmailRequests[$r->getId()]} other request(s) from this email address"
                                >
                                    <i class="fas fa-clone"></i>&nbsp;{$list->relatedEmailRequests[$r->getId()]}
                                </span>
                            {/if}
                            {if !$list->commonEmail[$r->getId()]}<span class="badge badge-warning badge-pill" data-toggle="tooltip" title="Uncommon email domain"><i class="fas fa-gem"></i></span>{/if}
                        </span>
                    {/if}
                </td>

                <td data-value="{$list->requestTrustedIp[$r->getId()]|escape|iphex}">
                    {if $list->requestTrustedIp[$r->getId()] === $list->dataClearIp}
                        <span class="text-muted font-italic">IP address purged</span>
                    {else}
                        <a href="https://en.wikipedia.org/wiki/User_talk:{$list->requestTrustedIp[$r->getId()]|escape}" target="_blank">{$list->requestTrustedIp[$r->getId()]|escape}</a>
                        {if $list->relatedIpRequests[$r->getId()] > 0}
                            <span class="badge badge-pill badge-danger"
                                  data-toggle="tooltip" data-original-title="{$list->relatedIpRequests[$r->getId()]} other request(s) from this IP address"
                            >
                                <i class="fas fa-clone"></i>&nbsp;{$list->relatedIpRequests[$r->getId()]}
                            </span>
                        {/if}
                    {/if}
                </td>
            {/if}

            {* Username *}
            <td data-value="{$r->getName()|escape}">
                <a href="https://en.wikipedia.org/wiki/User:{$r->getName()|escape:'url'}"
                   target="_blank">{$r->getName()|escape}</a>
            </td>

            {* Request Time *}
            <td data-value="{$r->getDate()|date}" data-dateformat="YYYY-MM-DD hh:mm:ss">
                <span class="d-none d-md-inline"><span title="{$r->getDate()|date}" data-toggle="tooltip" data-placement="top" id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</span></span>
            </td>

            {* Last updated *}
            <td data-value="{$r->getLastUpdated()|date}" data-dateformat="YYYY-MM-DD hh:mm:ss">
                <span class="d-none {if $list->showPrivateData}d-lg-inline{else}d-md-inline{/if}"><span title="{$r->getLastUpdated()|date}" data-toggle="tooltip" data-placement="top" id="#rqupdatetime{$r->getId()}">{$r->getLastUpdated()|relativedate}</span></span>
            </td>

            {* Bans *}
            <td>
                {if $list->canBan}
                    <div class="dropdown d-none d-md-block">
                        <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="banDropdown{$r->getId()}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ban"></i><span class="d-none d-lg-inline">&nbsp;Ban&nbsp;<span class="caret"></span></span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{$baseurl}/internal.php/bans/set?type=IP&amp;request={$r->getId()}">IP</a>
                            <a class="dropdown-item" href="{$baseurl}/internal.php/bans/set?type=EMail&amp;request={$r->getId()}">Email</a>
                            <a class="dropdown-item" href="{$baseurl}/internal.php/bans/set?type=Name&amp;request={$r->getId()}">Name</a>
                        </div>
                    </div>
                {/if}
            </td>

            {* Reserve status *}
            <td>
                {if $r->getReserved() !== null && $r->getReserved() != $currentUser->getId()}
                    <span class="d-none d-md-block">Being handled by {$list->userList[$r->getReserved()]|escape}</span>
                {/if}
            </td>

            {* Reserve Button *}
            <td>
                {if $r->getReserved() === null}
                    <form action="{$baseurl}/internal.php/viewRequest/reserve" method="post" class="form-row">
                        {include file="security/csrf.tpl"}
                        <input class="form-control" type="hidden" name="request" value="{$r->getId()}"/>
                        <input class="form-control" type="hidden" name="updateversion" value="{$r->getUpdateVersion()}"/>
                        <button class="btn btn-sm btn-success" type="submit">
                            <i class="fas fa-star"></i>&nbsp;Reserve
                        </button>
                    </form>
                {else}

                    {if $r->getReserved() == $currentUser->getId()}
                        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post"
                              class="form-row">
                            {include file="security/csrf.tpl"}
                            <input class="form-control" type="hidden" name="request" value="{$r->getId()}"/>
                            <input class="form-control" type="hidden" name="updateversion" value="{$r->getUpdateVersion()}"/>
                            <button class="btn btn-sm btn-dark" type="submit">
                                <i class="fas fa-star"></i>&nbsp;Unreserve
                            </button>
                        </form>
                    {else}
                        {if $list->canBreakReservation}
                            <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post"
                                  class="form-row">
                                {include file="security/csrf.tpl"}
                                <input class="form-control" type="hidden" name="request" value="{$r->getId()}"/>
                                <input class="form-control" type="hidden" name="updateversion" value="{$r->getUpdateVersion()}"/>
                                <button class="btn btn-sm btn-warning" type="submit">
                                    <i class="fas fa-hand-paper"></i> <span class="d-none d-lg-inline">Force break</span><span class="d-inline d-lg-none">{$list->userList[$r->getReserved()]|escape}</span>
                                </button>
                            </form>
                        {else}
                            <span class="d-inline d-lg-none">{$list->userList[$r->getReserved()]|escape}</span>
                        {/if}
                    {/if}
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
