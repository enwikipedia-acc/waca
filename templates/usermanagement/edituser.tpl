{extends file="pagebase.tpl"}
{block name="content"}
    <h3>User Settings for {$user->getUsername()|escape}</h3>
    <form class="form-horizontal" method="post">
        {include file="security/csrf.tpl"}
        <div class="control-group">
            <label class="control-label" for="user_name">Username:</label>
            <div class="controls">
                <input class="input-xlarge" type="text" id="user_name" value="{$user->getUsername()|escape}"
                       required="required" readonly="readonly"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_status">User status:</label>
            <div class="controls">
                <input class="input-xlarge" type="text" id="user_status" value="{$user->getStatus()|escape}"
                       required="required" readonly="readonly"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_email">Email Address:</label>
            <div class="controls">
                <input class="input-xlarge" type="email" id="user_email" name="user_email"
                       value="{$user->getEmail()|escape}" required="required"/>
            </div>
        </div>

        {if $oauth->isFullyLinked() || $oauth->isPartiallyLinked()}
            <div class="control-group">
                <label class="control-label" for="user_onwikiname">On-wiki Username:</label>
                <div class="controls">
                    <span class="input-xlarge uneditable-input"
                          id="user_onwikiname">{$user->getOnWikiName()|escape}</span>
                    <span class="label {if $oauth->isPartiallyLinked()}label-important{else}label-success{/if}">OAuth</span>
                </div>
            </div>
        {else}
            <div class="control-group">
                <label class="control-label" for="user_onwikiname">On-wiki Username:</label>
                <div class="controls">
                    <input class="input-xlarge" type="text" id="user_onwikiname" name="user_onwikiname"
                           value="{$user->getOnWikiName()|escape}" required="required"/>
                </div>
            </div>
        {/if}

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">Save preferences</button>
            </div>
        </div>
    </form>
{/block}