<div class="page-header">
  <h1>Ban Management</h1>
</div>

<h2>Active Ban List</h2>
<table class="table table-striped">
  <thead>
    <th>Type</th>
    <th>Target</th>
    <td>{* search! *}</td>
    <th>Banned by</th>
    <th>Reason</th>
    <th>Time</th>
    <th>Expiry</th>
    {if $currentUser->isAdmin()}
    <th>Unban</th>
    {/if}
  </thead>
  <tbody>
    {foreach from=$activebans item="ban"}
      <tr>
        <td>{$ban->getType()}</td>
        <td>{$ban->getTarget()}</td>
        <td>
            {if $ban->getType() == "IP"}
              <a class="btn btn-small btn-info" href="{$baseurl}/search.php?type=IP&amp;term={$ban->getTarget()|escape:'url'}">
                <i class="icon-white icon-search"></i>
                <span class="visible-desktop">&nbsp;Search</span>
              </a>
            {elseif $ban->getType() == "Name"}
              <a class="btn btn-small btn-info" href="{$baseurl}/search.php?type=Request&amp;term={$ban->getTarget()|escape:'url'}">
                <i class="icon-white icon-search"></i>
                <span class="visible-desktop">&nbsp;Search</span>
              </a>
            {elseif $ban->getType() == "EMail"}
              <a class="btn btn-small btn-info" href="{$baseurl}/search.php?type=email&amp;term={$ban->getTarget()|escape:'url'}">
                <i class="icon-white icon-search"></i>
                <span class="visible-desktop">&nbsp;Search</span>
              </a>
            {/if}
          </td>
        <td>{$ban->getUser()->getUsername()|escape}</td>
        <td>{$ban->getReason()|escape}</td>
        <td>{$ban->getDate()}</td>
        <td>{if $ban->getDuration() == -1}Indefinite{else}{date("Y-m-d H:i:s", $ban->getDuration())}{/if}</td>

        {if $currentUser->isAdmin()}
        <td>
          <a class="btn btn-success btn-small" href="{$baseurl}/acc.php?action=unban&amp;id={$ban->getId()}">
            <i class="icon-white icon-ok"></i><span class="visible-desktop">&nbsp;Unban</span>
          </a>
        </td>
        {/if}
      </tr>
    {/foreach}
  </tbody>
</table>

{if $currentUser->isAdmin()}
  {include file="bans/banform.tpl" bantarget="" bantype=""}
{/if}
