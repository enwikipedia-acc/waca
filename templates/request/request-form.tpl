{extends file="publicbase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h2>Request an account!</h2>
            <p>
                We will need a few bits of information in order to create your account. However, please keep in mind
                that you do not need an account to read the encyclopedia or look up information - that can be done by
                anyone with or without an account. The first thing we need is a username, and secondly, a
                <strong>valid email address that we can send your password to</strong>
                (please don't use temporary inboxes, or email aliasing, as this may cause your request to be rejected).
                If you want to leave any comments, feel free to do so in the comments field below. Note that if you use
                this form, your IP address will be recorded, and displayed to
                <a href="{$baseurl}/internal.php/statistics/users">those who review account requests</a>.
                When you are done, click the "Submit" button.
            </p>

            <div class="alert alert-warning">
                <h4>Please note!</h4>
                We do not have access to existing account data. If you have lost your password, please reset it using
                <a href="https://en.wikipedia.org/wiki/Special:PasswordReset">this form</a> at wikipedia.org. If you are
                trying to 'take over' an account that already exists, please use
                <a href="http://en.wikipedia.org/wiki/WP:CHU/U">"Changing usernames/Usurpation"</a> at wikipedia.org. We
                cannot do either of these things for you.
            </div>
            <p>
                If you have not yet done so, please review the <a href="https://en.wikipedia.org/wiki/Wikipedia:Username_policy">Username Policy</a>
                before submitting a request.
            </p>
        </div>
    </div>
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
                    <button type="submit" class="offset-md-4 col-md-8 btn btn-primary btn-block">Send request</button>
                </div>
            </form>
        </div>
    </div>
{/block}
