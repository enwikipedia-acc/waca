<div class="row-fluid">
  <div class="page-header">
	  <h1>Create an account!</h1>
  </div>
</div>

<div class="row-fluid">
  {foreach from=$requestSectionData key="header" item="section"}
    <div>
      <h2>{$header}</h2>
      {$section}
    </div>
  {/foreach}
</div>

<hr />

<div class="row-fluid">
  <h2>Last 5 Closed requests</h2>
  <table class="table table-condensed table-striped" style="width:auto;">
    <thead>
      <th>ID</th>
      <th>Name</th>
      <th>{* zoom *}</th>
    </thead>
    {foreach from=$lastFive item="req"}
    <tr>
      <th>{$req.pend_id}</th>
      <td>
        {$req.pend_name|escape}
      </td>
      <td>
        <a href="{$tsurl}/acc.php?action=zoom&amp;id={$req.pend_id|escape:'url'}" class="btn btn-info">
          <i class="icon-white icon-search"></i>&nbsp;Zoom
        </a>
      </td>
      <td>
        <a href="{$tsurl}/acc.php?action=defer&amp;id={$req.pend_id|escape:'url'}&amp;sum={$req.pend_checksum|escape:'url'}&amp;target=Open" class="btn btn-warning">
          <i class="icon-white icon-refresh"></i>&nbsp;Reset
        </a>
      </td>
    </tr>
    {/foreach}
  </table>
</div>