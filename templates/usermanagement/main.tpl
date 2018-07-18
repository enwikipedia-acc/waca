{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>User Management</h1>
        <p> Approve, suspend, promote, demote, etc.&nbsp;
            <a class="btn btn-primary btn-sm" href="?showAll"><i class="fas fa-eye"></i>&nbsp;Show all</a>
        </p>
    </div>
    {include file='alert.tpl' alertblock=true alertclosable=false
    alertheader='This interface is NOT a toy.'
    alertmessage='If it says you can do it, you can do it. Please use this responsibly.'
    alerttype='alert-warning'}
    <div class="row">
        <form method="get" class="col-md-8">
            <div class="form-row">
                <input type="text" class="form-control col-md-9" placeholder="Jump to user"
                       data-provide="typeahead" data-items="10" name="usersearch">
                <button type="submit" class="btn col-md-3">Search</button>
            </div>
        </form>
    </div>
    <hr />
    <div class="row">
        <div class="col-md-12">
            <div class="accordion" id="accordion2">
                <div class="card">
                    <div class="card-header">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseOne">Open requests</a>
                    </div>
                    <div id="collapseOne" class="collapse in">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$newUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseTwo">Users</a>
                    </div>
                    <div id="collapseTwo" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$normalUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseThree">Admins</a>
                    </div>
                    <div id="collapseThree" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$adminUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseSeven">Tool roots</a>
                    </div>
                    <div id="collapseSeven" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$toolRoots}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseFour">Tool Checkuser access</a>
                    </div>
                    <div id="collapseFour" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$checkUsers}
                        </div>
                    </div>
                </div>
                {if $showAll == true}
                    <div class="card">
                        <div class="card-header">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#collapseFive">Suspended accounts</a>
                        </div>
                        <div id="collapseFive" class="collapse">
                            <div class="card-body">
                                {include file='usermanagement/userlist.tpl' userlist=$suspendedUsers}
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#collapseSix">Declined accounts</a>
                        </div>
                        <div id="collapseSix" class="collapse">
                            <div class="card-body">
                                {include file='usermanagement/userlist.tpl' userlist=$declinedUsers}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/block}
