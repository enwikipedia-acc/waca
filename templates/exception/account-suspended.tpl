{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-8 offset-md-2 alert alert-block alert-danger">
            <h4>Account suspended</h4>
            <p>
                I'm sorry, but your tool account has been suspended by a tool administrator. The reason given is shown
                below:
            </p>
            <p class="card card-body prewrap">{$suspendReason|escape}</p>
            <p>
                If you wish to appeal this, please contact the tool admins.
            </p>
        </div>
    </div>
{/block}
