<h5 class="zoom-button-header">Reservation</h5>

<div class="row">
    <div class="col-md-8">
        {if $requestIsReservedByMe}
            <form action="{$baseurl}/internal.php/viewRequest/sendToUser" method="post">
                    {include file="security/csrf.tpl"}
                    <input type="hidden" name="request" value="{$requestId}"/>
                    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
                    <div class="form-row">
                        <div class="col-sm-7 form-group">
                            <input type="form-control text" required="true"
                                   placeholder="Send reservation to another user..." name="user"
                                   data-provide="typeahead" data-items="10"
                                   class="typeahead form-control username-typeahead"
                                   {if ! $requestIsReservedByMe}disabled="disabled"{/if} />
                        </div>
                        <div class="col-sm-5 form-group">
                            <input class="btn btn-secondary form-control " type="submit" value="Send Reservation"/>
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
            <button class="btn btn-inverse" type="submit">Break reservation</button>
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
<hr class="zoom-button-divider" />
