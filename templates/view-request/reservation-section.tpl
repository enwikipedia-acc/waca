<h5>Reservation</h5>

<div class="row">
    <div class="col-md-8">
        {if $requestIsReservedByMe}
            <form action="{$baseurl}/internal.php/viewRequest/sendToUser" method="post">
                    {include file="security/csrf.tpl"}
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                    <div class="form-row">
                        <div class="col-sm-7">
                            <label class="sr-only" for="sendReservationBox">Send request reservation to another user</label>
                            <input type="text" required="required"
                                   placeholder="Send reservation to another user..." name="user"
                                   class="typeahead form-control username-typeahead" id="sendReservationBox"
                                   {if ! $requestIsReservedByMe}disabled="disabled"{/if} />
                        </div>
                        <div class="col-sm-5">
                            <input class="btn btn-outline-secondary btn-block" type="submit" value="Send Reservation"/>
                        </div>
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
            <button class="btn btn-outline-dark btn-block" type="submit">Break reservation</button>
        </form>
    </div>
    {elseif $canBreakReservation && $requestIsReserved}
    <div class="col-md-4">
        <form action="{$baseurl}/internal.php/viewRequest/breakReserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}"/>
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn btn-warning btn-block" type="submit">Force break</button>
        </form>
    </div>
    {/if}

    {if ! $requestIsReserved}
    <div class="col-md-4">
        <form action="{$baseurl}/internal.php/viewRequest/reserve" method="post">
            {include file="security/csrf.tpl"}
            <input type="hidden" name="request" value="{$requestId}" />
            <input type="hidden" name="updateversion" value="{$updateVersion}"/>
            <button class="btn btn-success btn-block" type="submit">Reserve</button>
        </form>
    </div>
    {/if}
</div> <!-- /row-fluid -->
<hr />
