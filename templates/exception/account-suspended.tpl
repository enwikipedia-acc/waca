{extends file="base.tpl"}
{block name="content"}
    <div class="row-fluid">
        <div class="span8 offset2 alert alert-block alert-danger">
            <h4>Account suspended</h4>
            <p>
                I'm sorry, but your tool account hes been suspended by a tool administrator. The reason given is shown
                below:
            </p>
            <p class="well">{$suspendReason}</p>
            <p>
                If you wish to appeal this, please contact the tool admins.
            </p>
        </div>
    </div>
{/block}
