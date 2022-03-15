{extends file="publicbase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            {$renderedContent}
        </div>
    </div>
    {if $showForm}
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <form method="post">
                    <div class="form-group row">
                        <label for="inputUsername" class="col-md-4 col-form-label">Username</label>
                        <input class="form-control col-md-8" type="text" id="inputUsername" placeholder="Username" name="name" required="required" value="{$username|default:''|escape}">
                        <small class="form-text text-muted offset-md-4 col-md-8">
                            Case sensitive, first letter is always capitalized, you do not need to use all uppercase.
                            Note that this need not be your real name. Please make sure you don't leave any trailing
                            spaces or underscores on your requested username. Usernames may not consist entirely of
                            numbers, contain the following characters: <code># / | [ ] { } &lt; &gt; @ % :</code> or
                            exceed 85 characters in length.
                        </small>
                    </div>
                    <div class="form-group row">
                        <label for="inputEmail" class="col-md-4 col-form-label">Email</label>
                        <input class="form-control col-md-8" type="email" id="inputEmail" placeholder="Email" name="email" required="required" value="{$email|default:''|escape}">
                    </div>
                    <div class="form-group row">
                        <label for="inputEmailConfirm" class="col-md-4 col-form-label">Confirm Email</label>
                        <input class="form-control col-md-8" type="email" id="inputEmailConfirm" placeholder="Confirm Email" name="emailconfirm"
                               required="required">
                        <small class="form-text text-muted offset-md-4 col-md-8">
                            We need a valid email in order to send you your password and confirm your account request.
                            Without it, you will not receive your password, and will be unable to log in to your account.
                        </small>
                    </div>
                    <div class="form-group row">
                        <label for="inputComments" class="col-md-4 col-form-label">Comments</label>
                        <textarea class="form-control col-md-8" id="inputComments" rows="4" name="comments">{$comments|default:''|escape}</textarea>
                        <small class="form-text text-muted offset-md-4 col-md-8">
                            Any additional details you feel are relevant may be placed here. <strong>Please do NOT ask
                                for a specific password. One will be randomly created for you.</strong>
                        </small>
                    </div>
                    <div class="row">
                        <a href="#" class="offset-md-4 col-md-8 btn btn-primary btn-block">Send request</a>
                    </div>
                </form>
            </div>
        </div>
    {/if}
{/block}
{block name="publicfooter"}{/block}
{block name="publicheader"}{/block}