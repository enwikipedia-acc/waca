<table class="table table-striped table-sm sortable mb-0">
    <thead>
    <tr>
        <th data-defaultsort="asc"><span class="d-none-xs">#</span></th>
        {if $showStatus}
            <th>Request state</th>
        {/if}
        {if $list->showPrivateData}
            <th><span class="d-none d-lg-table-cell">Email address</span></th>
            <th><span class="d-none d-lg-table-cell">IP address</span></th>
        {/if}
        <th>Username</th>
        <th><span class="d-none d-md-block">Request time</span></th>
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
                <td>{$r->getStatus()}</td>
            {/if}

            {if $list->showPrivateData}
                <td>
                    <span class="d-none d-lg-table-cell">
                        {include file="mainpage/table-sections/email-section.tpl"}
                    </span>
                </td>

                <td data-value="{$list->requestTrustedIp[$r->getId()]|escape|iphex}">
                    <span class="d-none d-lg-table-cell">
                        {include file="mainpage/table-sections/ip-section.tpl"}
                    </span>
                </td>
            {/if}

            {* Username *}
            <td data-value="{$r->getName()|escape}">
                <a href="https://en.wikipedia.org/wiki/User:{$r->getName()|escape:'url'}"
                   target="_blank">{$r->getName()|escape}</a>
                {if $list->showPrivateData}
                    <span class="d-inline d-lg-none">
                        <br  />
                        {include file="mainpage/table-sections/email-section.tpl"}
                    </span>
                    <span class="d-inline d-lg-none">
                        <br />
                        {include file="mainpage/table-sections/ip-section.tpl"}
                    </span>
                {/if}
            </td>

            {* Request Time *}
            <td data-value="{$r->getDate()|date}" data-dateformat="YYYY-MM-DD hh:mm:ss">
                <span class="d-none d-md-block"><span title="{$r->getDate()|date}" data-toggle="tooltip" data-placement="top" id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</span></span>
            </td>

            {* Bans *}
            <td>
                {if $list->canBan}
                    <div class="dropdown d-none d-sm-block">
                        <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="banDropdown{$r->getId()}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ban"></i><span class="d-none d-md-inline">&nbsp;Ban&nbsp;</span><span class="caret"></span>
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
                    <span class="d-none d-lg-block">Being handled by {$list->userList[$r->getReserved()]|escape}</span>
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
