{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="alert alert-block alert-warning col-md-8 offset-md-2">
            <h4>Warning!</h4>

            <p>This request has already been closed in a manner that has generated an e-mail to the user. Sending another
                templated email to the user is likely to cause confusion - sending a custom email instead is now highly
                recommended. Do you wish to proceed with sending the templated email?</p>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row mt-5">
                    <div class="offset-md-3 col-md-3">
                        <button class="btn btn-success btn-block" name="emailSentOverride" value="true">Yes, send templated message</button>
                    </div>
                    <div class="col-md-3">
                        <a class="btn btn-danger btn-block" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
                    </div>

                </div>
                <input type="hidden" name="request" value="{$request}"/>
                <input type="hidden" name="template" value="{$template}"/>

                <input type="hidden" name="updateversion" value="{$updateversion}" />

                <input type="hidden" name="reserveOverride" value="{$reserveOverride}"/>
                <input type="hidden" name="createOverride" value="{$createOverride}"/>
                <input type="hidden" name="skipAutoWelcome" value="{$skipAutoWelcome}" />
            </form>
        </div>
    </div>
{/block}
