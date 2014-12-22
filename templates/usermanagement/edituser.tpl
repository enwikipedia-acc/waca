<h2>User Settings for {$user->getUsername()|escape}</h2>
{include file="alert.tpl" alertblock="0" alerttype="" alertclosable="0" alertheader="" alertmessage="Misuse of this interface can cause problems, please use it wisely"}
<form class="form-horizontal" action="users.php?edituser={$user->getId()}" method="post">
    <div class="control-group">
        <label class="control-label" for="user_name">Username:</label>
        <div class="controls">
            <input class="input-xlarge" type="text" id="user_name" value="{$user->getUsername()|escape}" required="true" readonly="true"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_status">User status:</label>
        <div class="controls">
            <input class="input-xlarge" type="text" id="user_status" value="{$user->getStatus()|escape}" required="true" readonly="true"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_email">Email Address:</label>
        <div class="controls">
            <input class="input-xlarge" type="email" id="user_email" name="user_email" value="{$user->getEmail()|escape}" required="true"/>
        </div>
    </div>
    {if $user->isOAuthLinked()}
        <div class="control-group">
            <label class="control-label" for="user_onwikiname">On-wiki Username:</label>
            <div class="controls">
                <span class="input-xlarge uneditable-input" id="user_onwikiname">{if $user->getOnWikiName() != "##OAUTH##"}{$user->getOnWikiName()|escape}{/if}</span>
                <span class="label {if $user->getOnWikiName() == "##OAUTH##"}label-important{else}label-success{/if}">OAuth</span>
            </div>
        </div>
    {else}
        <div class="control-group">
            <label class="control-label" for="user_onwikiname">On-wiki Username:</label>
            <div class="controls">
                <input class="input-xlarge" type="text" id="user_onwikiname" name="user_onwikiname" value="{$user->getOnWikiName()|escape}" required="true"/>
            </div>
        </div>
    {/if}
    <div class="control-group">
	    <div class="controls">
		    <button type="submit" class="btn btn-primary">Save preferences</button>
	    </div>
    </div>
</form>