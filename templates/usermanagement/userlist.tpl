<table class="table table-striped table-hover table-sm">
  <thead>
    <th>Username</th>
    <th>On-wiki name</th>
    <th>Roles</th>
    <th><span class="d-none d-sm-inline">Actions</span></th>
  </thead>
  <tbody>
    {foreach $userlist as $userentry}
      {include file="usermanagement/userentry.tpl" user=$userentry}
    {/foreach}
  </tbody>
</table>
