<table class="table table-striped sortable">
    <thead>
    <tr>
        <th data-defaultsort="asc"><span class="hidden-phone">#</span></th>
        {if $showStatus}
            <th>Request state</th>
        {/if}
        <th>Username</th>
        <th><span class="visible-desktop">Request time</span></th>
        <td><!-- ban --></td>
        <td><!-- reserve status --></td>
        <td><!--reserve button--></td>
    </tr>
    </thead>
    <tbody>
    {foreach from=$requests item="r"}
        <tr>
            <td>
                <a class="btn btn-small{if $r->hasComments() == true} btn-info{/if}"
                   href="{$baseurl}/acc.php?action=zoom&amp;id={$r->getId()}"><i
                            class="{if $r->hasComments() == true}icon-white{else}icon-black{/if} icon-search"></i><span
                            class="visible-desktop">&nbsp;{$r->getId()}</span></a>
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
        <span class="visible-desktop"><a rel="tooltip" href="#rqtime{$r->getId()}" title="{$r->getDate()}"
                                         data-toggle="tooltip" class="plainlinks"
                                         id="#rqtime{$r->getId()}">{$r->getDate()|relativedate}</a></span>
            </td>

            {* Bans *}
            <td>
                {if $currentUser->isAdmin() || $currentUser->isCheckuser() }
                    <div class="btn-group hidden-phone">
                        <a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">
                            <i class="icon-white icon-ban-circle"></i>&nbsp;Ban&nbsp;<span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{$baseurl}/internal.php/bans/set?type=IP&amp;request={$r->getId()}">IP</a></li>
                            <li><a href="{$baseurl}/internal.php/bans/set?type=EMail&amp;request={$r->getId()}">Email</a></li>
                            <li><a href="{$baseurl}/internal.php/bans/set?type=Name&amp;request={$r->getId()}">Name</a></li>
                        </ul>
                    </div>
                {/if}
            </td>

            {* Reserve status *}
            <td>
                {if $r->getReserved() != false && $r->getReserved() != $currentUser->getId()}
                    <span class="visible-desktop">Being handled by {$r->getReservedObject()->getUsername()|escape}</span>
                {/if}
            </td>

            {* Reserve Button *}
            <td>
                {if $r->getReserved() == false}
                    <a class="btn btn-small btn-success"
                       href="{$baseurl}/acc.php?action=reserve&amp;resid={$r->getId()}">
                        <i class="icon-white icon-star-empty"></i>&nbsp;Reserve
                    </a>
                {else}

                    {if $r->getReserved() == $currentUser->getId()}
                        <a class="btn btn-small btn-inverse"
                           href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
                            <i class="icon-white icon-star"></i>&nbsp;Unreserve
                        </a>
                    {else}

                        {if $currentUser->isAdmin() || $currentUser->isCheckUser() }
                            <a class="btn btn-small btn-warning visible-desktop"
                               href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
                                <i class="icon-white icon-trash"></i>&nbsp;Force break
                            </a>
                            <a class="btn btn-small btn-warning hidden-desktop"
                               href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$r->getId()}">
                                <i class="icon-white icon-trash"></i>&nbsp; {$r->getReservedObject()->getUsername()|escape}
                            </a>
                        {else}
                            <span class="hidden-desktop">{$r->getReservedObject()->getUsername()|escape}</span>
                        {/if}

                    {/if}

                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
