<table class="table table-striped table-hover table-sm">
  <thead>
    <th>Username</th>
    <th>On-wiki name</th>
    <th>Roles</th>
    <th>Actions</th>
  </thead>
  <tbody>
    {foreach $userlist as $userentry}
      {include file="usermanagement/userentry.tpl" user=$userentry}
    {/foreach}
  </tbody>
</table>
