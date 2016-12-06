{extends file="base.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="span8 offset2 alert alert-block alert-danger">
            <h4>Account declined</h4>
            <p>
                You've requested an account for this tool successfully, but your account request has been declined by a
                tool administrator. The reason given is shown below:
            </p>
            <p class="well">{$declineReason}</p>
            <p>
                If you wish to appeal this, please contact the tool admins.
            </p>
        </div>
    </div>
{/block}
