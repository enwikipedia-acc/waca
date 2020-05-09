{extends file="pagebase.tpl"}
{block name="content"}
    <h3>Changing access level for {$user->getUsername()|escape} to {$status}</h3>
    {if $showReason}
        {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader=""
        alertmessage="The user will be shown the reason you enter here. Please keep this in mind."}
    {/if}
    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group">
            <label for="username">Username:</label>
            <input class="form-control" type="text" id="username" value="{$user->getUsername()|escape}" required="required" readonly="readonly"/>
        </div>

        <div class="form-group">
            <label for="status">Old access level:</label>
            <input class="form-control" type="text" id="status" value="{$user->getStatus()|escape}" required="required" readonly="readonly"/>
        </div>

        <div class="form-group">
            <label for="newstatus">New access level:</label>
            <input class="form-control" type="text" id="newstatus" value="{$status}" required="required" readonly="readonly"/>
        </div>

        {if $showReason}
            <div class="form-group">
                <label for="reason">Reason:</label>
                <textarea id="reason" name="reason" required="required" class="form-control" rows="5"></textarea>
            </div>
        {/if}

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Change access level</button>
        </div>
    </form>
{/block}
