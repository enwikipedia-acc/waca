<div class="btn-group">
    {if ($canApprove || $canDecline) && ($user->isSuspended() || $user->isNewUser() || $user->isDeclined())}
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
    {if $canApprove && ($user->isSuspended() || $user->isNewUser() || $user->isDeclined())}
        <a class="btn btn-success" href="{$baseurl}/internal.php/userManagement/approve?user={$user->getId()}">
            <i class="icon-white icon-ok-sign"></i>&nbsp;
            <span class="visible-desktop">Approve</span>
        </a>
    {/if}
    {if $canDecline && $user->isNewUser()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/decline?user={$user->getId()}">
            <i class="icon-white icon-ban-circle"></i>&nbsp;
            <span class="visible-desktop">Decline</span>
        </a>
    {/if}
    {if $canSuspend && $user->isActive()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/suspend?user={$user->getId()}">
            <i class="icon-white icon-ban-circle"></i>&nbsp;
            <span class="visible-desktop">Suspend</span>
        </a>
    {/if}
    {if $canRename}
        <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/rename?user={$user->getId()}">
            <i class="icon-white icon-tag"></i>&nbsp;
            <span class="visible-desktop">Rename</span>
        </a>
    {/if}
    {if $canEditUser}
    <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/editUser?user={$user->getId()}">
        <i class="icon-white icon-pencil"></i>&nbsp;
        <span class="visible-desktop">Edit</span>
    </a>
    {/if}
    {if $canEditRoles}
        <a class="btn btn-info" href="{$baseurl}/internal.php/userManagement/editRoles?user={$user->getId()}">
            <i class="icon-white icon-tasks"></i>&nbsp;
            <span class="visible-desktop">Edit Roles</span>
        </a>
    {/if}
</div>
