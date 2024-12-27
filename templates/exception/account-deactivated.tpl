{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-8 offset-md-2 alert alert-block alert-danger">
            <h4>Account deactivated</h4>
            <p>
                Your account has been deactivated by a tool administrator. The reason given is given below:
            </p>
            <p class="card card-body prewrap">{$deactivationReason|escape}</p>
            <p>
                If you wish to appeal this, please fill in <a href="{$baseurl}/internal.php/login/reactivate">this form</a>.
            </p>
        </div>
    </div>
{/block}
