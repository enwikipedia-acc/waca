<div class="row-fluid">
    <h5 class="zoom-button-header">Reservation</h5>
</div>

<div class="row-fluid">
    <div class="span8">
        {if $requestIsReservedByMe}
            <form action="{$baseurl}/acc.php?action=sendtouser" method="post" class="form-inline">
                <input type="hidden" name="id" value="{$requestId}"/>
                <div class="row-fluid">
                    <input type="text" required="true"
                           placeholder="Send reservation to another user..." name="user"
                           data-provide="typeahead" data-items="10"
                           class="span8 username-typeahead"
                           {if ! $requestIsReservedByMe}disabled="disabled"{/if}/>
                    <input class="btn span4" type="submit" value="Send Reservation"/>
                </div>
            </form>
        {/if}
    </div>
    {if $requestIsReservedByMe}
        <a class="btn btn-inverse span4"
           href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$requestId}">Break reservation</a>
    {elseif $currentUser->isAdmin() && $requestIsReserved}
        <a class="btn span4 btn-warning"
           href="{$baseurl}/acc.php?action=breakreserve&amp;resid={$requestId}">Force break</a>
    {/if}
    {if ! $requestIsReserved}
        <a class="btn span4 btn-success"
           href="{$baseurl}/acc.php?action=reserve&amp;resid={$requestId}">Reserve</a>
    {/if}
</div> <!-- /row-fluid -->
<hr class="zoom-button-divider" />