<table class="table table-striped table-hover table-sm sortable">
  <thead>
    <th data-defaultsort="asc">Username</th>
    <th>On-wiki name</th>
    <th>Roles</th>
    <th data-defaultsort="disabled"><span class="d-none d-sm-inline">Actions</span></th>
  </thead>
  <tbody>
    {foreach $userlist as $userentry}
      {include file="usermanagement/userentry.tpl" user=$userentry}
    {/foreach}
  </tbody>
</table>
