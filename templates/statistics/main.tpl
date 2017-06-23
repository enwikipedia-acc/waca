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
      Graphs (<a href="http://accounts-dev.wmflabs.org/graph/">see more!</a>)
    </h4>
    {foreach from=$graphList item="graph"}
    <p>
      <img src="https://accounts-dev.wmflabs.org/graph/{$graph}/acc.svg" alt="graph"/>
    </p>
    {/foreach}
  </div>
</div>
