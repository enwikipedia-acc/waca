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
  {include file="statistics/main-smallstats.tpl"}
</div>
<div class="row-fluid">
  <div class="span12">
    <h4>
      Graphs (<a href="http://acc.stwalkerster.info/acc-new/">see more!</a>)
    </h4>
    {foreach from=$graphList item="graph"}
    <p>
      <img src="http://acc.stwalkerster.info/acc-new/{$graph}/acc.svg" alt="graph"/>
    </p>
    {/foreach}
  </div>
</div>