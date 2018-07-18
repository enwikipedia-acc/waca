<div class="btn-group">
    {if ($canApprove || $canDecline) && ($user->isSuspended() || $user->isNewUser() || $user->isDeclined())}
        <a class="btn" href="{$mediawikiScriptPath}?diff={$user->getConfirmationDiff()|escape:'url'}">
            <i class="fas fa-edit"></i>&nbsp;
            <span class="d-none d-md-block">Diff</span>
        </a>
        <a class="btn" href="//meta.wikimedia.org/wiki/Identification_noticeboard">
            <i class="fas fa-user"></i>&nbsp;
            <span class="d-none d-md-block">ID Noticeboard</span>
        </a>
        <a class="btn" href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$user->getOnWikiName()|escape:'url'}">
            <i class="fas fa-th"></i>&nbsp;
            <span class="d-none d-md-block">Count</span>
        </a>
    {/if}
</div>
<div class="btn-group">
    {if $canApprove && ($user->isSuspended() || $user->isNewUser() || $user->isDeclined())}
        <a class="btn btn-success" href="{$baseurl}/internal.php/userManagement/approve?user={$user->getId()}">
            <i class="fas fa-check"></i>&nbsp;
            <span class="d-none d-md-block">Approve</span>
        </a>
    {/if}
    {if $canDecline && $user->isNewUser()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/decline?user={$user->getId()}">
            <i class="fas fa-ban-circle"></i>&nbsp;
            <span class="d-none d-md-block">Decline</span>
        </a>
    {/if}
    {if $canSuspend && $user->isActive()}
        <a class="btn btn-danger" href="{$baseurl}/internal.php/userManagement/suspend?user={$user->getId()}">
            <i class="fas fa-ban"></i>&nbsp;
            <span class="d-none d-md-block">Suspend</span>
        </a>
    {/if}
    {if $canRename}
        <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/rename?user={$user->getId()}">
            <i class="fas fa-tag"></i>&nbsp;
            <span class="d-none d-md-block">Rename</span>
        </a>
    {/if}
    {if $canEditUser}
    <a class="btn btn-warning" href="{$baseurl}/internal.php/userManagement/editUser?user={$user->getId()}">
        <i class="fas fa-edit"></i>&nbsp;
        <span class="d-none d-md-block">Edit</span>
    </a>
    {/if}
    {if $canEditRoles}
        <a class="btn btn-info" href="{$baseurl}/internal.php/userManagement/editRoles?user={$user->getId()}">
            <i class="fas fa-tasks"></i>&nbsp;
            <span class="d-none d-md-block">Edit Roles</span>
        </a>
    {/if}
</div>
