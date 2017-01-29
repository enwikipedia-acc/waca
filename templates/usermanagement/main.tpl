{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>User Management
            <small> Approve, suspend, promote, demote, etc.&nbsp;<a class="btn btn-primary" href="?showAll"><i
                            class="icon-white icon-eye-open"></i>&nbsp;Show all</a></small>
        </h1>
    </div>
    {include file='alert.tpl' alertblock=true alertclosable=false
    alertheader='This interface is NOT a toy.'
    alertmessage='If it says you can do it, you can do it. Please use this responsibly.'
    alerttype='alert-warning'}
    <div class="row-fluid">
        <form class="form-search" method="get">
            <input type="text" class="input-large username-typeahead" placeholder="Jump to user"
                   data-provide="typeahead" data-items="10" name="usersearch">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <div class="accordion" id="accordion2">
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseOne">Open requests</a>
                    </div>
                    <div id="collapseOne" class="accordion-body collapse in">
                        <div class="accordion-inner">
                            {include file='usermanagement/userlist.tpl' userlist=$newUsers}
                        </div>
                    </div>
                </div>

                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseTwo">Users</a>
                    </div>
                    <div id="collapseTwo" class="accordion-body collapse">
                        <div class="accordion-inner">
                            {include file='usermanagement/userlist.tpl' userlist=$normalUsers}
                        </div>
                    </div>
                </div>

                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseThree">Admins</a>
                    </div>
                    <div id="collapseThree" class="accordion-body collapse">
                        <div class="accordion-inner">
                            {include file='usermanagement/userlist.tpl' userlist=$adminUsers}
                        </div>
                    </div>
                </div>

                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseSeven">Tool roots</a>
                    </div>
                    <div id="collapseSeven" class="accordion-body collapse">
                        <div class="accordion-inner">
                            {include file='usermanagement/userlist.tpl' userlist=$toolRoots}
                        </div>
                    </div>
                </div>

                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapseFour">Tool Checkuser access</a>
                    </div>
                    <div id="collapseFour" class="accordion-body collapse">
                        <div class="accordion-inner">
                            {include file='usermanagement/userlist.tpl' userlist=$checkUsers}
                        </div>
                    </div>
                </div>
                {if $showAll == true}
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#collapseFive">Suspended accounts</a>
                        </div>
                        <div id="collapseFive" class="accordion-body collapse">
                            <div class="accordion-inner">
                                {include file='usermanagement/userlist.tpl' userlist=$suspendedUsers}
                            </div>
                        </div>
                    </div>
                    <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#collapseSix">Declined accounts</a>
                        </div>
                        <div id="collapseSix" class="accordion-body collapse">
                            <div class="accordion-inner">
                                {include file='usermanagement/userlist.tpl' userlist=$declinedUsers}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/block}
