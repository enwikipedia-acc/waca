{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <!-- page header -->
        <div class="span12">
            <h2>Details for Request #{$requestId}:</h2>
        </div>
    </div>

    <div class="row-fluid">
        <!-- request details -->
        <div class="span6 container-fluid">
            {include file="view-request/request-info.tpl"}
            <hr class="zoom-button-divider" />

            {include file="view-request/reservation-section.tpl"}

            {block name="createButton"}
                {if $requestIsReservedByMe && !$requestIsClosed}
                    <h5 class="zoom-button-header">Create account:</h5>
                    <form class="form-inline zoom-button-header visible-desktop">
                        {if $canManualCreate}
                            <label class="radio">
                                <input type="radio" name="createMode" id="createModeManual" value="manual"
                                       onchange="changeCreateMode(this);"
                                       {if $currentUser->getCreationMode() == 0}checked="checked"{/if}>Manual
                            </label>
                        {/if}
                        {if $canOauthCreate}
                            <label class="radio">
                                <input type="radio" name="createMode" id="createModeOauth" value="oauth"
                                       onchange="changeCreateMode(this);"
                                       {if $currentUser->getCreationMode() == 1}checked="checked"{/if}>Use my Wikimedia account
                            </label>
                        {/if}
                        {if $canBotCreate}
                            <label class="radio">
                                <input type="radio" name="createMode" id="createModeBot" value="bot"
                                       onchange="changeCreateMode(this);"
                                       {if $currentUser->getCreationMode() == 2}checked="checked"{/if}>Use the bot
                            </label>
                        {/if}
                    </form>

                    <div class="row-fluid">
                        {if $canManualCreate}
                            <div class="create-button-row" id="createManual"
                                 {if $currentUser->getCreationMode() == 0}style="display: block;"{/if}>
                                {block name="manualcreationbutton"}{/block}
                            </div>
                        {/if}
                        {if $canOauthCreate}
                            <div class="create-button-row" id="createOauth"
                                 {if $currentUser->getCreationMode() == 1}style="display: block;"{/if}>
                                {include file="view-request/createbuttons/auto.tpl" creationMode="oauth" }
                            </div>
                        {/if}
                        {if $canBotCreate}
                            <div class="create-button-row" id="createBot"
                                 {if $currentUser->getCreationMode() == 2}style="display: block;"{/if}>
                                {include file="view-request/createbuttons/auto.tpl" creationMode="bot"}
                            </div>
                        {/if}
                    </div>

                    <hr class="zoom-button-divider"/>
                {/if}
            {/block}

            {block name="requestStatusButtons"}
                {include file="view-request/request-status-buttons.tpl"}
            {/block}

            {block name="banSection"}{/block}
        </div>
        <div class="span6 container-fluid">
            {include file="view-request/request-log.tpl"}
        </div>
    </div><!--/row-->

    {include file="view-request/username-section.tpl"}
    
    {block name="ipSection"}{/block}

    {block name="emailSection"}{/block}

    {block name="otherRequests"}
        <div class="row-fluid">
            <div class="span6">
                <h3>Other requests from this email address</h3>
                {if $requestDataCleared}
                    <p class="muted">Email information cleared</p>
                {else}
                    <p class="muted">Data currently not visible.</p>
                {/if}
            </div>
            <div class="span6">
                <h3>Other requests from this IP address</h3>
                {if $requestDataCleared}
                    <p class="muted">IP information cleared</p>
                {else}
                    <p class="muted">Data currently not visible.</p>
                {/if}
            </div>
        </div>
    {/block}
{/block}
