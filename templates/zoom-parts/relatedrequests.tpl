<table class="table table-condensed table-striped">
    {foreach $requests as $others}
        <tr>
            <td><a target="_blank" href="{$baseurl}/acc.php?action=zoom&amp;id={$others->getId()}">#{$others->getId()}</a></td>
            <td>
                {$others->getDate()}<span class="muted">
                <em>({$others->getDate()|relativedate})</em>
              </span>
            </td>
            <td>{$others->getName()|escape}</td>
            <td>
                {if $others->getStatus() == 'Closed'}
                    <span class="label label-important">{$others->getStatus()|escape} - {$others->getClosureReason()}</span>
                {else}
                    <span class="label label-success">{$others->getStatus()|escape}</span>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>