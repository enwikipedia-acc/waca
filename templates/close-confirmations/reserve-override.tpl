{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="alert alert-block alert-info col-md-8 offset-md-2">
            <h4>Warning!</h4>

            <p>This request is currently marked as being handled by {$reserveUser|escape}. Do you wish to proceed?</p>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row mt-5">
                    <div class="offset-md-3 col-md-3">
                        <button class="btn btn-success" name="reserveOverride" value="true">Yes</button>
                    </div>
                    <div class="col-md-3">
                        <a class="btn btn-danger" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
                    </div>
                </div>

                <input type="hidden" name="request" value="{$request}" />
                <input type="hidden" name="template" value="{$template}" />

                <input type="hidden" name="updateversion" value="{$updateversion}" />

                <input type="hidden" name="emailSentOverride" value="{$emailSentOverride}" />
                <input type="hidden" name="createOverride" value="{$createOverride}" />
            </form>
        </div>
    </div>
{/block}
