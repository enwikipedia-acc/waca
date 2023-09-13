<div class="row">
    <div class="col-md-12">
        <h4>Last 5 Closed requests</h4>
        <table class="table table-sm table-striped table-nonfluid table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>{* zoom *}</th>
                    <th>{* reset *}</th>
                </tr>
            </thead>
            {foreach from=$lastFive item="req"}
                <tr>
                    <th>{$req.id}</th>
                    <td>
                        {$req.name|escape}
                    </td>
                    <td>
                        <a href="{$baseurl}/internal.php/viewRequest?id={$req.id|escape:'url'}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-search"></i>&nbsp;Zoom
                        </a>
                    </td>
                    <td>
                        {if $defaultRequestState !== null}
                            <form action="{$baseurl}/internal.php/viewRequest/defer" method="post" class="form-row">
                                {include file="security/csrf.tpl"}
                                <input class="form-control" type="hidden" name="request" value="{$req.id}"/>
                                <input class="form-control" type="hidden" name="updateversion"
                                       value="{$req.updateversion}"/>
                                <input class="form-control" type="hidden" name="target" value="{$defaultRequestState|escape}"/>
                                <button class="btn btn-warning btn-sm" type="submit">
                                    <i class="fas fa-sync"></i>&nbsp;Reset
                                </button>
                            </form>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
