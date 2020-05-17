{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-12">
            <h1>Edit Conflict!</h1>
            <p>
                It looks like someone else has modified the same object since you've been looking at it!
            </p>
            <p>
                All of your changes have been rolled back, as we can't merge the changes together safely.
            </p>
            <p>
                Please use your browser's back button to go back to where you were, and try and make the change
                again if it is still appropriate.
            </p>
        </div>
    </div>

    {if $debugTrace}
        <div class="row">
            <div class="col-12">
                {$exceptionData}
            </div>
        </div>
    {/if}
{/block}
