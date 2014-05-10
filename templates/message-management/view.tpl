<div class="row-fluid">
  <div class="page-header">
    <h1>
      Message Management<small> View and edit the email and interface messages</small>
    </h1>
  </div>
  {foreach from=$data item=resultset key=title}
  <h2>{$title}</h2>
  <table class="table table-striped table-hover table-nonfluid">
    {foreach from=$resultset item="i"}
    <tr>
    <td>{$i->getId()}</td>
    <th>{$i->getDescription()}</th>
    <td>
      <a href="{$baseurl}/acc.php?action=messagemgmt&amp;edit={$i->getId()}" class="btn">
        <i class="icon icon-pencil"></i>&nbsp;Edit</a>
      <a href="{$baseurl}/acc.php?action=messagemgmt&amp;view={$i->getId()}" class="btn">
        <i class="icon icon-eye-open"></i>&nbsp;View</a>
    </td>
  </tr>
    {/foreach}
  </table>
  {/foreach}
</div>