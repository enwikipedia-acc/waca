{if
    ($ban->getVisibility() === 'user' && $canSeeUserVisibility)
    || ($ban->getVisibility() === 'admin' && $canSeeAdminVisibility)
    || ($ban->getVisibility() === 'checkuser' && $canSeeCheckuserVisibility)
}
    {if $ban->getVisibility() === 'admin'}<span class="badge badge-danger" title="Ban reason visibility restricted to tool admins"><i class="fas fa-lock"></i></span>{/if}
    {if $ban->getVisibility() === 'checkuser'}<span class="badge badge-visited" title="Ban reason visibility restricted to checkusers"><i class="fas fa-lock"></i></span>{/if}
    {$ban->getReason()}
{else}
    <span class="text-muted"><del>(redacted)</del></span>
{/if}