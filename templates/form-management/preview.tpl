{extends file="publicbase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            {$renderedContent}
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <form method="post">
                <div class="form-group row">
                    <label for="inputUsername" class="col-md-4 col-form-label">Username</label>
                    <input class="form-control col-md-8" type="text" id="inputUsername" placeholder="Username" name="name" disabled>
                    <small class="form-text text-muted offset-md-4 col-md-8">{$username}</small>
                </div>
                <div class="form-group row">
                    <label for="inputEmail" class="col-md-4 col-form-label">Email</label>
                    <input class="form-control col-md-8" type="email" id="inputEmail" placeholder="Email" name="email" disabled>
                </div>
                <div class="form-group row">
                    <label for="inputEmailConfirm" class="col-md-4 col-form-label">Confirm Email</label>
                    <input class="form-control col-md-8" type="email" id="inputEmailConfirm" placeholder="Confirm Email" name="emailconfirm" disabled>
                    <small class="form-text text-muted offset-md-4 col-md-8">{$email}</small>
                </div>
                <div class="form-group row">
                    <label for="inputComments" class="col-md-4 col-form-label">Comments</label>
                    <textarea class="form-control col-md-8" id="inputComments" rows="4" name="comments" disabled></textarea>
                    <small class="form-text text-muted offset-md-4 col-md-8">{$comment}</small>
                </div>
                <div class="row">
                    {include file="request/legal-info.tpl"}
                </div>
                <div class="row">
                    <div class="offset-md-4 col-md-8 btn btn-primary btn-block disabled">Send request</div>
                </div>
            </form>
        </div>
    </div>
{/block}
{block name="publicfooter"}{/block}
