<!-- tpl:zoom-parts/request-log.tpl -->
<h3>Log:</h3>
<form action="{$tsurl}/acc.php?action=comment-quick&amp;hash={$hash}" method="post">
    <table class="table table-condensed table-striped">
        <tbody>
          {if $zoomlogs}
            {foreach $zoomlogs as $zoomrow}
            <tr><td>{if $zoomrow.userid != NULL}<a href='{$tsurl}/statistics.php?page=Users&amp;user={$zoomrow.userid}'>{$zoomrow.user}</a>{else}{$zoomrow.user}{/if}{if $zoomrow.security == "admin"}<br /><span style="color:red">(admin only)</span>{/if}</td><td>{$zoomrow.entry}</td><td>{$zoomrow.time}</td><td>{if $zoomrow.canedit == true}<a class="btn btn-small" href="{$tsurl}/acc.php?action=ec&amp;id={$zoomrow.id}">Edit</a></td></tr>{/if}
            {/foreach}
        {else}
            <tr>
            <td></td>
            <td>
                <em>None.</em>
            </td>
            <td></td>
            <td></td>
            </tr>
        {/if}
        <tr>
            <td><a href="{$tsurl}/statistics.php?page=Users&amp;user={$userid}">{$currentUser->getUsername()}</a></td>
            <td>
            <input type="hidden" name="id" value="{$id}"/>
            <input type="hidden" name="visibility" value="user" />
            <input class="span12" placeholder="Quick comment" name="comment"/>
            </td>
            <td colspan="2">
            <div class="btn-group">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn" href="{$tsurl}/acc.php?action=comment&amp;id={$id}">Advanced</a>
            </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<!-- /tpl:zoom-parts/request-log.tpl -->