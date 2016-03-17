{extends file="base.tpl"}
{block name="content"}
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="page-header"><h1>Edit Conflict!</h1></div>
                <p>It looks like someone else has modified the same object since you've been looking at it!</p>
                <p>All of your changes have been rolled back, as we can't merge the changes together safely.</p>
                <p>Please use your browser's back button to go back to where you were, and try and make the change again if it is still appropriate.</p>
            </div>
        </div>
    </div>
{/block}