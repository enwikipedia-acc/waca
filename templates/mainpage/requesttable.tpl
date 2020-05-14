<table class="table table-striped table-sm sortable mb-0">
    <thead>
    <tr>
        <th data-defaultsort="asc"><span class="d-none-xs">#</span></th>
        {if $showStatus}
            <th>Request state</th>
        {/if}
        <th>Username</th>
        <th><span class="d-none d-md-block">Request time</span></th>
        <th><!-- ban --></th>
        <th><!-- reserve status --></th>
        <th><!--reserve button--></th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$requests item="r"}
        <tr>
            <td>
                <a class="btn btn-sm{if $r->hasComments() == true} btn-info{else} btn-secondary{/if}"
                href="{$baseurl}/internal.php/viewRequest?id={$r->getId()}"><i class="fas fa-search"></i><span class="d-none d-md-inline">&nbsp;{$r->getId()}</span></a>
            </td>

            {if $showStatus}
                <td>{$r->getStatus()}</td>
            {/if}

            {* Username *}
            <td>
                <a href="https://en.wikipedia.org/wiki/User:{$r->getName()|escape:'url'}"
                   target="_blank">{$r->getName()|escape}</a>
            </td>

            {* Request Time *}
            <td>
                <span class="d-none d-md-block"><span title="{$r->getDate()|date}" data-toggle="tooltip" data-placement="top" id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</span></span>
            </td>

            {* Bans *}
            <td>
                {if $canBan}
                    <div class="dropdown">
                        <button class="btn btn-danger btn-sm dropdown-toggle" type="button" id="banDropdown{$r->getId()}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ban"></i>&nbsp;Ban&nbsp;<span class="caret"></span>
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
                    <span class="d-none d-md-block">Being handled by {$userlist[$r->getReserved()]|escape}</span>
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
                        {if $canBreakReservation }
                            <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post"
                                  class="form-row">
                                {include file="security/csrf.tpl"}
                                <input class="form-control" type="hidden" name="request" value="{$r->getId()}"/>
                                <input class="form-control" type="hidden" name="updateversion" value="{$r->getUpdateVersion()}"/>
                                <button class="btn btn-sm btn-warning" type="submit">
                                    <i class="fas fa-hand-paper"></i>&nbsp;Force break
                                </button>
                            </form>
                        {/if}
                    {/if}
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
