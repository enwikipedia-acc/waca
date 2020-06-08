{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="alert alert-block alert-warning col-md-8 offset-md-2">
            <h4>Warning!</h4>

            <p>You have chosen to mark this request as "created", but the account does not exist on the English
                Wikipedia. Do you wish to proceed?</p>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row mt-5">
                    <div class="offset-md-3 col-md-3">
                        <button class="btn btn-success btn-block" name="createOverride" value="true">Yes</button>
                    </div>
                    <div class="col-md-3">
                        <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
                    </div>
                </div>
                <input type="hidden" name="request" value="{$request}" />
                <input type="hidden" name="template" value="{$template}" />

                <input type="hidden" name="updateversion" value="{$updateversion}" />

                <input type="hidden" name="emailSentOverride" value="{$emailSentOverride}" />
                <input type="hidden" name="reserveOverride" value="{$reserveOverride}" />
            </form>
        </div>
    </div>
{/block}
