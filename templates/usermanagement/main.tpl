{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User Management <small class="text-muted">Approve, suspend, promote, demote, etc.</small></h1>
                {if $showAll == false}
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a class="btn btn-sm btn-outline-secondary" href="?showAll"><i class="fas fa-eye"></i>&nbsp;Show all</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>

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
            <div class="accordion" id="userListAccordion">
                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseNew">
                            Open requests
                            {if count($newUsers) > 0 }<span class="badge badge-warning badge-pill">{count($newUsers)}</span>{/if}
                        </button>
                    </div>
                    <div id="collapseNew" class="collapse show ">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$newUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseUsers">
                            Users
                        </button>
                    </div>
                    <div id="collapseUsers" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$normalUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseAdmins">
                            Tool admins
                        </button>
                    </div>
                    <div id="collapseAdmins" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$adminUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseRoots">
                            Tool roots
                        </button>
                    </div>
                    <div id="collapseRoots" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$toolRoots}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseCheckusers">
                            Checkusers
                        </button>
                    </div>
                    <div id="collapseCheckusers" class="collapse">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$checkUsers}
                        </div>
                    </div>
                </div>

                {if $showAll == true}
                    <div class="card">
                        <div class="card-header position-relative py-0">
                            <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseSuspended">
                                Suspended accounts
                            </button>
                        </div>
                        <div id="collapseSuspended" class="collapse">
                            <div class="card-body">
                                {include file='usermanagement/userlist.tpl' userlist=$suspendedUsers}
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header position-relative py-0">
                            <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseDeclined">
                                Declined accounts
                            </button>
                        </div>
                        <div id="collapseDeclined" class="collapse">
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
