<div class="row-fluid">
    <h5 class="zoom-button-header">Reservation</h5>
</div>

<div class="row-fluid">
    <div class="span8">
        {if $requestIsReservedByMe}
            <form action="{$baseurl}/internal.php/viewRequest/sendToUser" method="post" class="form-inline">
                {include file="security/csrf.tpl"}
                <input type="hidden" name="request" value="{$requestId}"/>
                <input type="hidden" name="updateversion" value="{$updateVersion}"/>
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
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}"/>
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn span4 btn-inverse" type="submit">Break reservation</button>
        </form>
    {elseif $canBreakReservation && $requestIsReserved}
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}"/>
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn span4 btn-warning" type="submit">Force break</button>
        </form>
    {/if}

    {if ! $requestIsReserved}
        <form action="{$baseurl}/internal.php/viewRequest/reserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}" />
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn span4 btn-success" type="submit">Reserve</button>
        </form>
    {/if}
</div> <!-- /row-fluid -->
<hr class="zoom-button-divider" />
