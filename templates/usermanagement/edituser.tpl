{extends file="pagebase.tpl"}
{block name="content"}
    <h3>User Settings for {$user->getUsername()|escape}</h3>
    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="form-group">
            <label for="user_name">Username:</label>
            <input class="form-control" type="text" id="user_name" value="{$user->getUsername()|escape}" required="required" readonly="readonly"/>
        </div>

        <div class="form-group">
            <label for="user_status">User status:</label>
            <input class="form-control" type="text" id="user_status" value="{$user->getStatus()|escape}" required="required" readonly="readonly"/>
        </div>

        <div class="form-group">
            <label for="user_email">Email Address:</label>
            <input class="form-control" type="email" id="user_email" name="user_email"
                   value="{$user->getEmail()|escape}" required="required"/>
        </div>

        {if $user->isOAuthLinked()}
            <div class="form-group">
                <label for="user_onwikiname">On-wiki Username:</label>
                <span class="form-control uneditable-input"
                      id="user_onwikiname">{if $user->getOnWikiName() != "##OAUTH##"}{$user->getOnWikiName()|escape}{/if}</span>
                <span class="badge {if $user->getOnWikiName() == "##OAUTH##"}badge-danger{else}badge-success{/if}">OAuth</span>
            </div>
        {else}
            <div class="form-group">
                <label for="user_onwikiname">On-wiki Username:</label>
                <input class="form-control" type="text" id="user_onwikiname" name="user_onwikiname"
                       value="{$user->getOnWikiName()|escape}" required="required"/>
            </div>
        {/if}

        <input type="hidden" name="updateversion" value="{$user->getUpdateVersion()}"/>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save preferences</button>
        </div>
    </form>
{/block}
