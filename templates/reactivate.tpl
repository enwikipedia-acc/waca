{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h4>Request account reactivation</h4>
            <p>
                If you wish to appeal the deactivation of your account, please
                use the form below.
            </p>
            <p>The reason given for your account's deactivation is shown below:</p>
            <div class="card border mb-3">
                <div class="card-body">
                    <div class="prewrap">{$deactivationReason|escape}</div>
                </div>
            </div>

            {if !$ableToAppeal}
                {include file="alert.tpl" alertblock="true" alerttype="alert-danger" alertclosable=false alertmessage="Your account is unable to appeal through this interface."}
            {/if}

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="form-group row">
                    <div class="col-12">
                        <label class="col-form-label" for="reason">Please explain why you believe your account should be reactivated:</label>
                    </div>
                    <div class="col-12">
                        <textarea required="required" class="form-control" rows="4" name="reason" id="reason" maxlength="65535" {if !$ableToAppeal}readonly{/if}></textarea>
                    </div>
                </div>

                <input type="hidden" name="updateVersion" value="{$updateVersion}"/>

                <div class="form-group row">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-block btn-primary" {if !$ableToAppeal}disabled{/if}>Request Reactivation</button>
                    </div>
                </div>


            </form>
        </div>
    </div>
{/block}
