{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-12">
            <h2>Edit comment #{$comment->getId()}</h2>
        </div>
    </div>

    <form method="post">
        {include file="security/csrf.tpl"}

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="request">Request</label>
            </div>
            <div class="col-md-8 col-lg-4">
                <div class="form-control"> 
                    <a id="request" href="{$baseurl}/internal.php/viewRequest?id={$comment->getRequest()}">
                        {$request->getName()|escape}
                    </a>
                    {if $request->getStatus() == Waca\RequestStatus::CLOSED}
                        <span class="badge badge-danger">Closed</span>
                    {elseif $request->getStatus() == Waca\RequestStatus::OPEN}
                        <span class="badge badge-success">Open</span>
                    {else}
                        <span class="badge badge-warning">{$request->getStatus()|escape}</span>
                    {/if}
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="user">Commenting user</label>
            </div>
            <div class="col-md-8 col-lg-4">
                {if $comment->getUser() == null}
                    {if $comment->getVisibility() == 'requester'}
                    <div class="form-control">
                        <span class="badge badge-info">
                            <i class="fas fa-user"></i>&nbsp;Requester
                        </span>
                    </div>
                    {else}
                        <span class="form-control" id="user">
                            {$user->getUsername()|escape}
                        </span>
                    {/if}
                {else}
                    <a class="form-control" id="user" href="{$baseurl}/internal.php/statistics/users/detail?user={$comment->getUser()}">
                        {$user->getUsername()|escape}
                    </a>
                {/if}
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="timestamp">Timestamp</label>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="form-control" id="timestamp">{$comment->getTime()|date} <span class="text-muted">({$comment->getTime()|relativedate})</span></div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="lastedit">Last edited</label>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="form-control" id="lastedit">{$comment->getEdited()|date} <span class="text-muted">({$comment->getEdited()|relativedate})</span></div>
            </div>
        </div>

        {if $comment->getVisibility() == "requester"}
        {else}
            <div class="form-group row">
                <div class="col-md-4 col-lg-2">
                    <label class="col-form-label" for="visibility">Security</label>
                </div>
                <div class="col-md-8 col-lg-4">
                    <select name="visibility" class="form-control" id="visibility">
                        <option value="user" {if $comment->getVisibility() == "user"}selected{/if}>Standard</option>
                        <option value="admin" {if $comment->getVisibility() == "admin"}selected{/if}>Tool admins & checkusers</option>
                        <option value="checkuser" {if $comment->getVisibility() == "checkuser"}selected{/if}>CheckUsers only</option>
                    </select>
                </div>
            </div>
        {/if}

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="oldtext">Old text</label>
            </div>
            <div class="col-md-8 col-lg-10">
                <div class="form-control prewrap" id="oldtext">{$comment->getComment()|escape}</div>
            </div>
        </div>

        {if $request->getStatus() !== Waca\RequestStatus::CLOSED && $comment->getFlagged()}
            <div class="form-group row">
                <div class="offset-md-4 col-md-8 offset-lg-2 col-lg-10">
                {include file="alert.tpl"
                    alerttype="alert-warning mb-0"
                    alertmessage="Please consider if any redactable information in this comment will be useful for handling this request, and whether a redaction can wait until the request is closed."
                }
                </div>
            </div>

            
        {/if}

        <div class="form-group row">
            <div class="col-md-4 col-lg-2">
                <label class="col-form-label" for="newcomment">New text</label>
            </div>
            <div class="col-md-8 col-lg-10">
                <textarea required="required" class="form-control" rows="4" name="newcomment" id="newcomment">{$comment->getComment()|escape}</textarea>
            </div>
        </div>

        {if $comment->getFlagged() && $canUnflag}
            <div class="form-group row">
                <div class="offset-md-4 offset-lg-2 col-md-8 col-lg-10">
                    <div class="custom-control custom-switch">
                        <input class="custom-control-input" type="checkbox" id="unflag" name="unflag" />
                        <label class="custom-control-label" for="unflag">Un-flag comment</label>
                    </div>
                </div>
            </div>
        {/if}

        <input type="hidden" name="updateversion" value="{$comment->getUpdateVersion()}"/>

        <div class="form-group row">
            <div class="offset-md-4 offset-lg-2 col-md-4">
                <button type="submit" class="btn btn-block btn-primary">Save changes</button>
            </div>
        </div>

    </form>
{/block}
