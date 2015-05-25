<div class="btn-group">
    {if $user->isNew() || $user->isSuspended() || $user->isDeclined()}
    <a class="btn" href="//{$wikiurl}/w/index.php?diff={$user->getConfirmationDiff()|escape:'url'}">
        <i class="icon icon-edit"></i>&nbsp;
        <span class="visible-desktop">Diff</span>
    </a>
    <a class="btn" href="//meta.wikimedia.org/wiki/Identification_noticeboard">
        <i class="icon icon-user"></i>&nbsp;
        <span class="visible-desktop">ID Noticeboard</span>
    </a>
    <a class="btn" href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$user->getOnWikiName()|escape:'url'}">
        <i class="icon icon-th"></i>&nbsp;
        <span class="visible-desktop">Count</span>
    </a>
    {/if}
</div>
<div class="btn-group">
    {if $user->isSuspended() || $user->isNew() || $user->isDeclined()}
    <a class="btn btn-success" href="{$baseurl}/users.php?approve={$user->getId()}">
        <i class="icon-white icon-ok-sign"></i>&nbsp;
        <span class="visible-desktop">Approve</span>
    </a>
    {/if}
    {if $user->isNew()}
    <a class="btn btn-danger" href="{$baseurl}/users.php?decline={$user->getId()}">
        <i class="icon-white icon-ban-circle"></i>&nbsp;
        <span class="visible-desktop">Decline</span>
    </a>
    {/if}
    {if $user->isUser() || $user->isAdmin()}
    <a class="btn btn-danger" href="{$baseurl}/users.php?suspend={$user->getId()}">
        <i class="icon-white icon-ban-circle"></i>&nbsp;
        <span class="visible-desktop">Suspend</span>
    </a>
    {/if}
    <a class="btn btn-warning" href="{$baseurl}/users.php?rename={$user->getId()}">
    <i class="icon-white icon-tag"></i>&nbsp;
    <span class="visible-desktop">Rename</span>
    </a>
    <a class="btn btn-warning" href="{$baseurl}/users.php?edituser={$user->getId()}">
    <i class="icon-white icon-pencil"></i>&nbsp;
    <span class="visible-desktop">Edit</span>
    </a>
    {if $user->isUser()}
    <a class="btn btn-info" href="{$baseurl}/users.php?promote={$user->getId()}">
        <i class="icon-white icon-arrow-up"></i>&nbsp;
        <span class="visible-desktop">Promote</span>
    </a>
    {/if}
    {if $user->isAdmin()}
    <a class="btn btn-inverse" href="{$baseurl}/users.php?demote={$user->getId()}">
        <i class="icon-white icon-arrow-down"></i>&nbsp;
        <span class="visible-desktop">Demote</span>
    </a>
    {/if}
</div>