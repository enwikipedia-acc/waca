﻿<div class="btn-group">
    {if $user->isNewUser() || $user->isSuspended() || $user->isDeclined()}
        <a class="btn" href="{$mediawikiScriptPath}?diff={$user->getConfirmationDiff()|escape:'url'}">
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
    {if $user->isSuspended() || $user->isNewUser() || $user->isDeclined()}
        <a class="btn btn-success" href="{$baseurl}/internal.php/userManagement/approve?user={$user->getId()}">
            <i class="icon-white icon-ok-sign"></i>&nbsp;
            <span class="visible-desktop">Approve</span>
        </a>
    {/if}
    {if $user->isNewUser()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/decline?user={$user->getId()}">
            <i class="icon-white icon-ban-circle"></i>&nbsp;
            <span class="visible-desktop">Decline</span>
        </a>
    {/if}
    {if $user->isUser() || $user->isAdmin()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/suspend?user={$user->getId()}">
            <i class="icon-white icon-ban-circle"></i>&nbsp;
            <span class="visible-desktop">Suspend</span>
        </a>
    {/if}
    <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/rename?user={$user->getId()}">
        <i class="icon-white icon-tag"></i>&nbsp;
        <span class="visible-desktop">Rename</span>
    </a>
    <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/editUser?user={$user->getId()}">
        <i class="icon-white icon-pencil"></i>&nbsp;
        <span class="visible-desktop">Edit</span>
    </a>
    {if $user->isUser()}
        <a class="btn btn-info" href="{$baseurl}/internal.php/userManagement/promote?user={$user->getId()}">
            <i class="icon-white icon-arrow-up"></i>&nbsp;
            <span class="visible-desktop">Promote</span>
        </a>
    {/if}
    {if $user->isAdmin()}
        <a class="btn btn-inverse" href="{$baseurl}/internal.php/userManagement/demote?user={$user->getId()}">
            <i class="icon-white icon-arrow-down"></i>&nbsp;
            <span class="visible-desktop">Demote</span>
        </a>
    {/if}
</div>