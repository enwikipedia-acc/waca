{* If custom create reasons are active, then make the Created button a split button dropdown. *}
<form method="post" action="{$baseurl}/internal.php/viewRequest/close" class="row">
    <div class="col-md-6">
        <a class="btn btn-primary btn-block jsconfirm" target="_blank"
           href="{$mediawikiScriptPath}?title=Special:UserLogin/signup&amp;wpName={$requestName|escape:'url'}&amp;email={$requestEmail|escape:'url'}&amp;reason={$createAccountReason|escape:'url'}{$requestId}&amp;wpCreateaccountMail=true"
                {if !$skipJsAborts && $createdHasJsQuestion}
                    data-template="{$createdId}"
                {/if}>
            Create account
        </a>
    </div>
    <div class="col-md-6">
        {if !empty($createReasons)}
            <div class="dropright btn-group btn-block">
                <button class="btn btn-success col" type="submit" name="template" value="{$createdId}">
                    {$createdName|escape}
                </button>

                <button type="button"
                        class="btn btn-success dropdown-toggle dropdown-toggle-split col-xs-auto"
                        data-toggle="dropdown">&nbsp;<span class="caret"></span></button>

                <ul class="dropdown-menu" role="menu">
                    {foreach $createReasons as $reason}
                        <li>
                            <button class="btn-link dropdown-item jsconfirm" name="template" value="{$reason->getId()}" type="submit"
                                    {if !$skipJsAborts && $reason->getJsquestion() != ''}
                                        data-template="{$reason->getId()}"
                                    {/if}>
                                {$reason->getName()|escape}
                            </button>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {else}
            <button class="btn btn-success btn-block" type="submit" name="template" value="{$createdId}">
                {$createdName|escape}
            </button>
        {/if}

        {include file="view-request/skipautowelcome.tpl" creationMode="manual"}
    </div>
    <input type="hidden" name="request" value="{$requestId}"/>
    <input type="hidden" name="updateversion" value="{$updateVersion}"/>
    {include file="security/csrf.tpl"}
</form>
