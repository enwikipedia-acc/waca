{extends file="base.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="alert alert-block alert-info span8 offset2">
            <h4>Warning!</h4>

            <p>This request has already been closed in a manner that has generated an e-mail to the user. Do you wish to
                proceed?</p>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row-fluid" style="margin-top:30px;">
                    <button class="btn btn-success offset3 span3" name="emailSentOverride" value="true">Yes</button>
                    <a class="btn btn-danger span3" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
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