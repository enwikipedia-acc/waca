{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
<div class="row-fluid">
	<div class="span6">
    <h4>Menu</h4>
    <ul>
      {foreach from=$statsPages item=page key=title}
        <li>
          <a href="{$baseurl}/internal.php/statistics/{$title}">{$page}</a>
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
      <img src="http://accounts-dev.wmflabs.org/graph/{$graph}/acc.svg" alt="graph"/>
    </p>
    {/foreach}
  </div>
</div>
{/block}