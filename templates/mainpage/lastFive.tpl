<div class="row-fluid">
    <h3>Last 5 Closed requests</h3>
    <table class="table table-condensed table-striped" style="width:auto;">
        <thead>
        <th>ID</th>
        <th>Name</th>
        <th>{* zoom *}</th>
        </thead>
        {foreach from=$lastFive item="req"}
            <tr>
                <th>{$req.id}</th>
                <td>
                    {$req.name|escape}
                </td>
                <td>
                    <a href="{$baseurl}/internal.php/viewRequest?id={$req.id|escape:'url'}" class="btn btn-info">
                        <i class="icon-white icon-search"></i>&nbsp;Zoom
                    </a>
                </td>
                <td>
                    <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-compact">
                        {include file="security/csrf.tpl"}
                        <input type="hidden" name="request" value="{$req.id}"/>
                        <input type="hidden" name="updateversion" value="{$req.updateversion}"/>
                        <input type="hidden" name="target" value="{$defaultRequestState}"/>
                        <button class="btn btn-warning" type="submit">
                            <i class="icon-white icon-refresh"></i>&nbsp;Reset
                        </button>
                    </form>
                </td>
            </tr>
        {/foreach}
    </table>
</div>