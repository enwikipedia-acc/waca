<div class="row-fluid">
	<div class="header">
		<h1>
      Welcome Templates
      {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
        <small>
          <a href="?action=templatemgmt&amp;add=yes" class="btn btn-success">
            <i class="icon-white icon-plus"></i>&nbsp;Create new Welcome Template</a>
        </small>
      {/if}
    </h1>
	</div>
</div>
<div class="row-fluid">
  <div class="span12">
    <p class="muted">
      This page allows you to choose a template to use to automatically welcome the users you create. Use the Select button to choose the template you wish to use. If the template you want to use is not on the list, please ask an admin to add it for you.
    </p>
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    <table class="table table-striped table-hover table-nonfluid">
      <thead>
        <tr>
          <th>
            Template User code
          </th>
          {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <th>
            Used by:
          </th>
          {/if}
          <td>
            <!-- View -->
          </td>
          {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <td>
            <!-- Edit -->
          </td>
          <td>
            <!-- Delete -->
          </td>
          {/if}
          <td>
            <!-- Select -->
          </td>
        </tr>
      </thead>
      <tfoot>
        {if $currentUser->getWelcomeTemplate() != 0}
        <tr>
          <th>
            Disable automatic welcoming
          </th>
          {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <td>
            <!-- count -->
          </td>
          {/if}
          <td>
            <!-- View -->
          </td>
          {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
          <td>
            <!-- Edit -->
          </td>
          <td>
            <!-- Delete -->
          </td>
          {/if}
          <td>
            <a href="?action=templatemgmt&amp;select=0" class="btn btn-primary">
              <i class="icon-white icon-ok"></i>
              &nbsp;Select
            </a>
          </td>
        </tr>
        {/if}
      </tfoot>
      <tbody>
        {foreach from=$templatelist item="t" name="templateloop"}
          <tr {if $currentUser->getWelcomeTemplate() == $t->getId()}class="success"{/if}>
            <td>
              {$t->getUserCode()|escape}
            </td>
            {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
            <td>
              <a class="btn {if count($t->getUsersUsingTemplate()) > 0}btn-warning{else}disabled{/if}" {if count($t->getUsersUsingTemplate()) > 0}rel="popover"{/if} href="#" title="Users using this template" id="#tpl{$t->getId()}" data-content="{{include file="linkeduserlist.tpl" users=$t->getUsersUsingTemplate()}|escape}" data-html="true">
                {count($t->getUsersUsingTemplate())}
              </a>
            </td>
            {/if}
            <td>
              <a href="?action=templatemgmt&amp;view={$t->getId()}" class="btn">
                <i class="icon icon-eye-open"></i>
                &nbsp;View
              </a>
            </td>
            {if $currentUser->isAdmin() || $currentUser->isCheckUser()}
            <td>
              <a href="?action=templatemgmt&amp;edit={$t->getId()}" class="btn btn-warning">
              <i class="icon-white icon-pencil"></i>
                &nbsp;Edit
              </a>
            </td>
            <td>
              <a href="?action=templatemgmt&amp;del={$t->getId()}" class="btn btn-danger">
              <i class="icon-white icon-remove"></i>
                &nbsp;Delete
              </a>
            </td>
            {/if}
            <td>
              {if $currentUser->getWelcomeTemplate() != $t->getId()}
              <a href="?action=templatemgmt&amp;select={$t->getId()}" class="btn btn-primary">
              <i class="icon-white icon-ok"></i>
                &nbsp;Select
              </a>
              {else}
              <a href="" class="btn btn-primary disabled">
                Selected
              </a>
              {/if}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>
</div>
