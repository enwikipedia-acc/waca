<div class="btn-group">
    {if ($canApprove || $canDeactivate) && ($user->isDeactivated() || $user->isNewUser())}
        <a class="btn btn-outline-secondary btn-sm" href="{$mediawikiScriptPath}?diff={$user->getConfirmationDiff()|escape:'url'}">
            <i class="fas fa-edit"></i>
            <span class="d-none d-lg-inline">&nbsp;Diff</span>
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="//meta.wikimedia.org/wiki/Identification_noticeboard">
            <i class="fas fa-user"></i>
            <span class="d-none d-lg-inline">&nbsp;ID Noticeboard</span>
        </a>
        <a class="btn btn-outline-secondary btn-sm" href="{$baseurl}/redir.php?tool=tparis-pcount&amp;data={$user->getOnWikiName()|escape:'url'}">
            <i class="fas fa-th"></i>
            <span class="d-none d-lg-inline">&nbsp;Count</span>
        </a>
    {/if}
</div>
<div class="btn-group">
    {if $canApprove && ($user->isDeactivated() || $user->isNewUser())}
        <a class="btn btn-success btn-sm" href="{$baseurl}/internal.php/userManagement/approve?user={$user->getId()}">
            <i class="fas fa-check"></i>
            <span class="d-none d-md-inline">&nbsp;Approve</span>
        </a>
    {/if}
    {if $canDeactivate && ($user->isNewUser() || $user->isActive())}
        <a class="btn btn-danger btn-sm" href="{$baseurl}/internal.php/userManagement/deactivate?user={$user->getId()}">
            <i class="fas fa-ban"></i>
            <span class="d-none d-md-inline">&nbsp;Deactivate</span>
        </a>
    {/if}
</div>
{if $canRename}
    <a class="btn btn-outline-secondary btn-sm d-none d-md-inline-block" href="{$baseurl}/internal.php/userManagement/rename?user={$user->getId()}">
        <i class="fas fa-tag"></i>
        <span class="d-none d-xl-inline">&nbsp;Rename</span>
    </a>
{/if}
{if $canEditUser}
<a class="btn btn-outline-secondary btn-sm d-none d-md-inline-block" href="{$baseurl}/internal.php/userManagement/editUser?user={$user->getId()}">
    <i class="fas fa-edit"></i>
    <span class="d-none d-xl-inline">&nbsp;Edit</span>
</a>
{/if}
{if $canEditRoles}
    <a class="btn btn-info btn-sm d-none d-sm-inline-block" href="{$baseurl}/internal.php/userManagement/editRoles?user={$user->getId()}">
        <i class="fas fa-tasks"></i>
        <span class="d-none d-lg-inline">&nbsp;Edit Roles</span>
    </a>
{/if}
