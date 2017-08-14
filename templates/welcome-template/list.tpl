{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="header">
            <h1>
                Welcome Templates
                {if $canAdd}
                    <small>
                        <a href="{$baseurl}/internal.php/welcomeTemplates/add" class="btn btn-success"><i
                                    class="icon-white icon-plus"></i>&nbsp;Create new Welcome Template</a>
                    </small>
                {/if}
            </h1>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <p class="muted">
                This page allows you to choose a template to use to automatically welcome the users you create. Use the
                Select button to choose the template you wish to use. If the template you want to use is not on the
                list, please ask an admin to add it for you.
            </p>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <table class="table table-striped table-hover table-nonfluid">
                <thead>
                <tr>
                    <th>Template User code</th>
                    {if $canEdit}
                        <th>Used by:</th>
                    {/if}
                    <td><!-- View --></td>
                    {if $canEdit}
                        <td><!-- Edit --></td>
                        <td><!-- Delete --></td>
                    {/if}
                    <td><!-- Select --></td>
                </tr>
                </thead>
                <tfoot>
                <tr {if $currentUser->getWelcomeTemplate() == null}class="success"{/if}>
                    <th>Disable automatic welcoming</th>
                    {if $canEdit}
                        <td><!-- count --></td>
                    {/if}
                    <td><!-- View --></td>
                    {if $canEdit}
                        <td><!-- Edit --></td>
                        <td><!-- Delete --></td>
                    {/if}
                    <td>
                        {if $currentUser->getWelcomeTemplate() !== null}
                            <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/select"
                                  class="form-compact">
                                {include file="security/csrf.tpl"}
                                <input type="hidden" name="disable" value="true"/>
                                <button type="submit" class="btn btn-primary"><i
                                            class="icon-white icon-ok"></i>&nbsp;Select
                                </button>
                            </form>
                        {else}
                            <a href="" class="btn btn-primary disabled">Selected</a>
                        {/if}
                    </td>
                </tr>
                </tfoot>
                <tbody>
                {foreach from=$templateList item="t" name="templateloop"}
                    <tr {if $currentUser->getWelcomeTemplate() == $t->getId()}class="success"{/if}>
                        <td>
                            {$t->getUserCode()|escape}
                        </td>
                        {if $canEdit}
                            <td>
                                <a class="btn {if count($t->getUsersUsingTemplate()) > 0}btn-warning{else}disabled{/if}"
                                   {if count($t->getUsersUsingTemplate()) > 0}rel="popover"{/if} href="#"
                                   title="Users using this template" id="#tpl{$t->getId()}"
                                   data-content="{{include file="linkeduserlist.tpl" users=$t->getUsersUsingTemplate()}|escape}"
                                   data-html="true">
                                    {count($t->getUsersUsingTemplate())}
                                </a>
                            </td>
                        {/if}
                        <td>
                            <a href="{$baseurl}/internal.php/welcomeTemplates/view?template={$t->getId()}"
                               class="btn"><i
                                        class="icon icon-eye-open"></i>&nbsp;View</a>
                        </td>
                        {if $canEdit}
                            <td>
                                <a href="{$baseurl}/internal.php/welcomeTemplates/edit?template={$t->getId()}"
                                   class="btn btn-warning"><i
                                            class="icon-white icon-pencil"></i>&nbsp;Edit</a>
                            </td>
                            <td>
                                <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/delete"
                                      class="form-compact">
                                    {include file="security/csrf.tpl"}
                                    <input type="hidden" name="template" value="{$t->getId()}"/>
                                    <input type="hidden" name="updateversion" value="{$t->getUpdateVersion()}"/>
                                    <button type="submit" class="btn btn-danger"><i
                                                class="icon-white icon-remove"></i>&nbsp;Delete
                                    </button>
                                </form>
                            </td>
                        {/if}
                        <td>
                            {if $currentUser->getWelcomeTemplate() != $t->getId()}
                                <form method="post" action="{$baseurl}/internal.php/welcomeTemplates/select"
                                      class="form-compact">
                                    {include file="security/csrf.tpl"}
                                    <input type="hidden" name="template" value="{$t->getId()}"/>
                                    <button type="submit" class="btn btn-primary"><i
                                                class="icon-white icon-ok"></i>&nbsp;Select
                                    </button>
                                </form>
                            {else}
                                <a href="" class="btn btn-primary disabled">Selected</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/block}
