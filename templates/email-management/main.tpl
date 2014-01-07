<div class="page-header">
  <h1>Email Management<small>
    Create and edit close reasons{if $currentUser->isAdmin() == true} &nbsp;<a class="btn btn-primary" href="{$tsurl}/acc.php?action=emailmgmt&amp;create=1">
      <i class="icon-white icon-plus"></i>&nbsp;Create
    </a>{/if}
  </small></h1>
</div>

<div class="row-fluid">
  <div class="span6">
	  <h3>Active Emails</h3>
    <table class="table table-striped table-nonfluid">
      {foreach $activeemails as $row}
      <tr>
        <td>{$row@iteration}.</td>
        <th>{$row.name}</th>
        <td>
          <a class="btn {if $currentUser->isAdmin()}btn-warning{/if}" href="{$tsurl}/acc.php?action=emailmgmt&amp;edit={$row.id}">
            {if $currentUser->isAdmin()}<i class="icon-white icon-pencil"></i>&nbsp;Edit!{else}<i class="icon-black icon-eye-open"></i>&nbsp;View!{/if}
          </a>
        </td>
      </tr>
      {/foreach}
    </table>
  </div>
  {if $displayinactive == true}
  <div class="span6">
	  <h3>Inactive Emails</h3>
    <table class="table table-striped table-nonfluid">
      {foreach $inactiveemails as $row}
      <tr>
        <td>{$row@iteration}.</td>
        <th>{$row.name}</th>
        <td>
          <a class="btn {if $currentUser->isAdmin()}btn-warning{/if}" href="{$tsurl}/acc.php?action=emailmgmt&amp;edit={$row.id}">
            {if $currentUser->isAdmin()}<i class="icon-white icon-pencil"></i>&nbsp;Edit!{else}<i class="icon-black icon-eye-open"></i>&nbsp;View!{/if}
          </a>
        </td>
      </tr>
      {/foreach}
    </table>
  </div>
  {/if}
</div>