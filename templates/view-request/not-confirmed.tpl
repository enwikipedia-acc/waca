{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">View request details <small class="text-muted">for request #{$requestId}</small></h1>
            </div>
        </div>
    </div>
    <div class="row">
        {if $canViewConfirmButton}
            <div class="col-lg-12">
                <form method="post" action="{$baseurl}/internal.php/viewRequest/confirm">
                    <div class="alert alert-danger">
                        <input type="hidden" name="request" value="{$requestId}" />
                        <input type="hidden" name="version" value="{$requestVersion}" />
                        {include file="security/csrf.tpl"}
                        <p>
                            As a tool administrator, you may manually confirm this request.
                            <em>This bypasses the email confirmation step, which is not recommended unless the user has replied to the email list.</em>
                        </p>
                        <button type="submit" class="btn btn-danger">Manually Confirm This Request</button>
                    </div>
                </form>
            </div>
        {/if}
        <div class="col-lg-12">
            This request has not been email confirmed.
        </div>
    </div>
{/block}
