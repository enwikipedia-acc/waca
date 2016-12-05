<div class="row-fluid">
  <div class="page-header">
	  <h1>Create an account!</h1>
  </div>
</div>

<div class="row-fluid">

  <div class="accordion" id="requestListAccordion">
    {foreach from=$requestSectionData key="header" item="section"}
    <div class="accordion-group">
      <div class="accordion-heading">
        <a class="accordion-toggle" data-toggle="collapse" data-parent="#requestListAccordion" href="#collapse{$section.api}">
          {$header} <span class="badge {if $section.total > $requestLimitShowOnly}badge-important{else}badge-info{/if}">{if $section.total > 0}{$section.total}{/if}</span>
        </a>
      </div>
      <div id="collapse{$section.api}" class="accordion-body collapse out">
        <div class="accordion-inner">
          {include file="mainpage/requestlist.tpl" requests=$section showStatus=false type=$section.type}
        </div>
      </div>
    </div>
    {/foreach}
  </div>
</div>

<hr />

<div class="row-fluid">
  <h3>Last 5 Closed requests</h3>
  <table class="table table-condensed table-striped" style="width:auto;">
    <thead>
      <th>ID</th>
      <th>Name</th>
      <th>{* zoom *}</th>
    </thead>
    {foreach from=$lastFive item="req"}
    <tr>
      <th>{$req.id}</th>
      <td>
        {$req.name|escape}
      </td>
      <td>
        <a href="{$baseurl}/acc.php?action=zoom&amp;id={$req.id|escape:'url'}" class="btn btn-info">
          <i class="icon-white icon-search"></i>&nbsp;Zoom
        </a>
      </td>
      <td>
        <a href="{$baseurl}/acc.php?action=defer&amp;id={$req.id|escape:'url'}&amp;sum={$req.checksum|escape:'url'}&amp;target=Open" class="btn btn-warning">
          <i class="icon-white icon-refresh"></i>&nbsp;Reset
        </a>
      </td>
    </tr>
    {/foreach}
  </table>
</div>