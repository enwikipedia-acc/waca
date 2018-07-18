<h5 class="zoom-button-header">Reservation</h5>

<div class="row">
    <div class="col-md-8">
        {if $requestIsReservedByMe}
            <form action="{$baseurl}/internal.php/viewRequest/sendToUser" method="post" class="col-md-12">
                {include file="security/csrf.tpl"}
                <input type="hidden" name="request" value="{$requestId}"/>
                <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                <div class="form-group row">
                    <input type="text" required="true"
                           placeholder="Send reservation to another user..." name="user"
                           data-provide="typeahead" data-items="10"
                           class="col-sm-7 form-control username-typeahead"
                           {if ! $requestIsReservedByMe}disabled="disabled"{/if}/>
                    <input class="btn btn-secondary form-control col-md-5" type="submit" value="Send Reservation"/>
                </div>
            </form>
        {/if}
    </div>
    {if $requestIsReservedByMe}
    <div class="col-md-4">
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}"/>
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn btn-inverse" type="submit">Break reservation</button>
        </form>
    </div>
    {elseif $canBreakReservation && $requestIsReserved}
    <div class="col-md-4">
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}"/>
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn col-md-4 btn-warning" type="submit">Force break</button>
        </form>
    </div>
    {/if}

    {if ! $requestIsReserved}
    <div class="col-md-4">
        <form action="{$baseurl}/internal.php/viewRequest/reserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}" />
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn btn-success" type="submit">Reserve</button>
        </form>
    </div>
    {/if}
</div> <!-- /row-fluid -->
<hr class="zoom-button-divider" />
