<tr>
	<td><span class="hidden-phone">{$rownum}</span></td>
	<td>
    <a class="btn btn-small{if $hascmt == true} btn-info{/if} hidden-desktop" href="{$tsurl}/acc.php?action=zoom&amp;id={$rid}"><i class="{if $hascmt == true}icon-white{else}icon-black{/if} icon-search"></i></a>
    <a class="btn btn-small visible-desktop" href="{$tsurl}/acc.php?action=zoom&amp;id={$rid}"><i class="icon-black icon-search"></i>&nbsp;Zoom</a>
  </td>
  <td>{if $hascmt == true}<span class="label label-info visible-desktop">Comment</span>{/if}</td>
	<td>{if $showdata == true}<a href="mailto:{$mail}" target="_blank">{$mail}</a>&nbsp;<span class="badge{if $nummail > 0} badge-important{/if}">{$nummail}</span><span class="hidden-desktop"><br><a href="https://en.wikipedia.org/wiki/User_talk:{$ip}" target="_blank">{$ip}</a>&nbsp;<span class="badge {if $numip > 0} badge-important{/if}">{$numip}</span>{/if}
		<span class="visible-phone"><br><a href="https://en.wikipedia.org/wiki/User:{$name}" target="_blank">{$name}</a></span></span></td>
	<td>{if $showdata == true}<span class="visible-desktop"><a href="https://en.wikipedia.org/wiki/User_talk:{$ip}" target="_blank">{$ip}</a>&nbsp;<span class="badge {if $numip > 0} badge-important{/if}">{$numip}</span></span>{/if}</td>
	<td><span class="hidden-phone"><a href="https://en.wikipedia.org/wiki/User:{$name}" target="_blank">{$name}</a></span></td>
	<td>{if $canban == true}<div class="btn-group hidden-phone"><a class="btn dropdown-toggle btn-small btn-danger" data-toggle="dropdown" href="#">
    <i class="icon-white icon-ban-circle"></i>&nbsp;Ban&nbsp;<span class="caret"></span></a><ul class="dropdown-menu"><li><a href="{$tsurl}/acc.php?action=ban&amp;ip={$rid}">IP</a></li><li><a href="{$tsurl}/acc.php?action=ban&amp;email={$rid}">Email</a></li><li><a href="{$tsurl}/acc.php?action=ban&amp;name={$rid}">Name</a></li></ul></div>{/if}</td>
	<td>
{if $reserved != ""}
  {if $youreserved}
  </td>
  <td>
    <a class="btn btn-small btn-inverse" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$rid}">
      <i class="icon-white icon-star"></i>&nbsp;Unreserve
    </a>
  {else}
    <span class="visible-desktop">Being handled by {$reserved}</span>
  </td>
  <td>
    {if $canbreak == true}
    <a class="btn btn-small btn-warning visible-desktop" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$rid}">
      <i class="icon-white icon-trash"></i>&nbsp;Force break
    </a>
    <a class="btn btn-small btn-warning hidden-desktop" href="{$tsurl}/acc.php?action=breakreserve&amp;resid={$rid}">
      <i class="icon-white icon-trash"></i>&nbsp; {$reserved}</a>
    {else}
      <span class="hidden-desktop">{$reserved}</span>
    {/if}
  {/if}
{else}
  </td>
  <td>
    <a class="btn btn-small btn-success" href="{$tsurl}/acc.php?action=reserve&amp;resid={$rid}">
      <i class="icon-white icon-star-empty"></i>&nbsp;Reserve
    </a>
{/if}
  </td>
</tr>
