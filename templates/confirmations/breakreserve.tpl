{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="alert alert-block alert-info col-md-8 offset-md-2">
            <h4>Warning!</h4>
            <p
                {$reservedUser->getUsername()|escape} has reserved this request, and may still be working on it. Are you
                sure you wish to forcefully break their reservation?
            </p>
            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="row" style="margin-top:30px;">
                    <button class="btn btn-success offset-md-3 col-md-3" name="confirm" value="true">Yes</button>
                    <a class="btn btn-danger col-md-3" href="{$baseurl}/internal.php/viewRequest?id={$request}">No</a>
                </div>

                <input type="hidden" name="request" value="{$request}" />
                <input type="hidden" name="updateversion" value="{$updateversion}" />
            </form>
        </div>
    </div>
{/block}
