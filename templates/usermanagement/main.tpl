{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">User Management <small class="text-muted">Approve, deactivate, promote, demote, etc.</small></h1>
                {if $showAll == false}
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a class="btn btn-sm btn-outline-secondary" href="?showAll"><i class="fas fa-eye"></i>&nbsp;Show all</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>

    <form>
        <div class="row">
            <div class="col-sm-8 col-md-6 col-lg-4 col-xl-3">
                <label class="sr-only" for="usersearch">Jump to user</label>
                <input type="text" class="form-control username-typeahead" placeholder="Jump to user" name="usersearch" id="usersearch">
            </div>
            <div class="col-sm-4 col-md-3">
                <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i>Search</button>
            </div>
        </div>
    </form>

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
                    <div id="collapseNew" class="collapse show" data-parent="#userListAccordion">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$newUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseUsers">
                            All users
                        </button>
                    </div>
                    <div id="collapseUsers" class="collapse" data-parent="#userListAccordion">
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
                    <div id="collapseAdmins" class="collapse" data-parent="#userListAccordion">
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
                    <div id="collapseRoots" class="collapse" data-parent="#userListAccordion">
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
                    <div id="collapseCheckusers" class="collapse" data-parent="#userListAccordion">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$checkUsers}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header position-relative py-0">
                        <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseStewards">
                            Stewards
                        </button>
                    </div>
                    <div id="collapseStewards" class="collapse" data-parent="#userListAccordion">
                        <div class="card-body">
                            {include file='usermanagement/userlist.tpl' userlist=$stewards}
                        </div>
                    </div>
                </div>

                {if $showAll == true}
                    <div class="card">
                        <div class="card-header position-relative py-0">
                            <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#userListAccordion" data-target="#collapseDeactivated">
                                Deactivated accounts
                            </button>
                        </div>
                        <div id="collapseDeactivated" class="collapse" data-parent="#userListAccordion">
                            <div class="card-body">
                                {include file='usermanagement/userlist.tpl' userlist=$deactivatedUsers}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/block}
