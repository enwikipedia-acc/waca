{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Exception log</h1>
            </div>
        </div>
    </div>

    <div class="row">
    <div class="col-md-12">
    <p>This table lists a summary of all the logged exceptions. Every single one of the ones marked as unhandled is a bug which should be caught in the application logic. A full stack trace is available on in the details.</p>
    </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped sortable">
                <thead>
                    <th>ID</th>
                    <th data-defaultsort="desc">Date</th>
                    <th>Unhandled?</th>
                    <th>Exception</th>
                    <th data-defaultsort="disabled">Message</th>
                    {if $canRemove || $canView}
                        <th data-defaultsort="disabled"></th>
                    {/if}
                </thead>
                <tbody>
                {foreach from=$exceptionEntries item="exception"}
                    <tr>
                        <td class="text-nowrap"><code>{$exception.id}</code></td>
                        <td class="text-nowrap">{$exception.date}</td>
                        {if $exception.data.globalHandler}
                            <td class="text-nowrap bg-danger text-light">Yes</td>
                        {else}
                            <td class="text-nowrap">No</td>
                        {/if}
                        <td class="text-nowrap">{$exception.data.exception}</td>
                        <td>{$exception.data.message}</td>

                        {if $canRemove || $canView}
                            <td class="table-button-cell">
                                <a class="btn btn-outline-info btn-sm" href="{$baseurl}/internal.php/errorLog/view?id={$exception.id}">
                                    <i class="fas fa-info-circle"></i><span class="d-none d-lg-inline">&nbsp;Details</span>
                                </a>
                                <a class="btn btn-danger btn-sm" href="{$baseurl}/internal.php/errorLog/remove?id={$exception.id}">
                                    <i class="fas fa-trash-alt"></i><span class="d-none d-lg-inline">&nbsp;Remove</span>
                                </a>
                            </td>
                        {/if}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

{/block}