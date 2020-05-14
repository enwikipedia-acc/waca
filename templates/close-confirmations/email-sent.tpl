{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="alert alert-block alert-info col-md-8 offset-md-2">
            <h4>Warning!</h4>

            <p>This request has already been closed in a manner that has generated an e-mail to the user. Do you wish to
                proceed?</p>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row mt-5">
                    <div class="offset-md-5 col-md-5">
                        <button class="btn btn-success" name="emailSentOverride" value="true">Yes</button>
                    </div
                    <div class="col-md-5">
                        <a class="btn btn-danger" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
                    </div>

                </div>
                <input type="hidden" name="request" value="{$request}"/>
                <input type="hidden" name="template" value="{$template}"/>

                <input type="hidden" name="updateversion" value="{$updateversion}" />

                <input type="hidden" name="reserveOverride" value="{$reserveOverride}"/>
                <input type="hidden" name="createOverride" value="{$createOverride}"/>
            </form>
        </div>
    </div>
{/block}
