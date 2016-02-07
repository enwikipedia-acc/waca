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
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            <input type="hidden" name="request" value="{$requestId}"/>
            <button class="btn span4 btn-inverse" type="submit">Break reservation</button>
        </form>
    {elseif $currentUser->isAdmin() && $requestIsReserved}
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            <input type="hidden" name="request" value="{$requestId}"/>
            <button class="btn span4 btn-warning" type="submit">Force break</button>
        </form>
    {/if}

    {if ! $requestIsReserved}
        <form action="{$baseurl}/internal.php/viewRequest/reserve" method="post">
            <input type="hidden" name="request" value="{$requestId}" />
            <button class="btn span4 btn-success" type="submit">Reserve</button>
        </form>
    {/if}
</div> <!-- /row-fluid -->
<hr class="zoom-button-divider" />