{if $allowWelcomeSkip}
<div class="creationOptions">
    <div class="creationOtherOptions">
        <div class="custom-control-inline custom-switch">
            <input type="checkbox" name="skipAutoWelcome" id="skipAutoWelcome{$creationMode}" class="custom-control-input" {if $forceWelcomeSkip}disabled="disabled" checked="checked"{/if} />
            <label for="skipAutoWelcome{$creationMode}" class="custom-control-label">Skip automatic welcome on account creation</label>
            {if $forceWelcomeSkip}
                <input type="hidden" name="skipAutoWelcome" value="true" />
            {/if}
        </div>
    </div>
</div>
{/if}
