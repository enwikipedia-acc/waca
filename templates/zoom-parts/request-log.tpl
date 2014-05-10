<!-- tpl:zoom-parts/request-log.tpl -->
<h3>Log:</h3>
<form action="{$baseurl}/acc.php?action=comment-quick&amp;hash={$hash}" method="post">
    <table class="table table-condensed table-striped">
        <tbody>
          {if $zoomlogs}
            {foreach from=$zoomlogs item=zoomrow name=logloop}
              <tr>
                <td>
                  {if $zoomrow.userid != NULL}
                    <a href='{$baseurl}/statistics.php?page=Users&amp;user={$zoomrow.userid}'>{$zoomrow.user}</a>
                  {else}
                    {$zoomrow.user}
                  {/if}
                  
                  {if $zoomrow.security == "admin"}
                    <br />
                    <span style="color:red">(admin only)</span>
                  {/if}
                </td>
                <td>{$zoomrow.entry}</td>
                <td>
                  <a rel="tooltip" href="#log{$smarty.foreach.logloop.index}" title="{$zoomrow.time}" data-toggle="tooltip" class="plainlinks" id="#log{$smarty.foreach.logloop.index}">{$zoomrow.time|relativedate}</a>
                </td>
                <td>{if $zoomrow.canedit == true}<a class="btn btn-small" href="{$baseurl}/acc.php?action=ec&amp;id={$zoomrow.id}"><i class="icon icon-pencil"></i></a>{/if}</td>
              </tr>
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
            <td><a href="{$baseurl}/statistics.php?page=Users&amp;user={$userid}">{$currentUser->getUsername()}</a></td>
            <td>
            <input type="hidden" name="id" value="{$request->getId()}"/>
            <input type="hidden" name="visibility" value="user" />
            <input class="span12" placeholder="Quick comment" name="comment"/>
            </td>
            <td colspan="2">
            <div class="btn-group">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn" href="{$baseurl}/acc.php?action=comment&amp;id={$request->getId()}">Advanced</a>
            </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<!-- /tpl:zoom-parts/request-log.tpl -->