<div class="row-fluid">
	<div class="span6">
    <h4>Menu</h4>
    <ul>
      {foreach from=$statsPages item=page}
        <li>
          <a href="{$baseurl}/statistics.php/{$page->getPageName()}">{$page->getPageTitle()}</a>
        </li>
      
      {/foreach}
    </ul>
	</div>
  <div class="span6">
    {$smallStats}
  </div>
</div>
<div class="row-fluid">
  <div class="span12">
    {$rrdToolGraphs}
  </div>
</div>