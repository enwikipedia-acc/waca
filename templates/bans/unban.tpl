{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ban Management <small class="text-muted">View, ban, and unban requesters</small></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3>Unbanning ban #{$ban->getId()|escape}</h3>

            <div class="card my-3">
                <div class="card-body ">
                    {include file="bans/bantarget.tpl"}
                </div>
            </div>

            <form method="post">
                {include file="security/csrf.tpl"}

                <p>Are you sure you wish to unban this ban, which
                    is {if $ban->getDuration() === null} not set to expire {else} set to expire {date("Y-m-d H:i:s", $ban->getDuration())}{/if}
                    with the following reason?</p>

                <div class="card card-body my-3">
                    {$ban->getReason()|escape}
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="unbanreason" class="col-form-label">Reason for removing ban:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control" type="text" id="unbanreason" name="unbanreason" required="required"/>
                    </div>
                </div>

                <input type="hidden" name="updateversion" value="{$ban->getUpdateVersion()}" />

                <div class="form-group row">
                    <div class="offset-sm-4 offset-md-3 offset-xl-2 col-sm-8 col-md-5 col-xl-4">
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check-circle"></i>&nbsp;Unban</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}
