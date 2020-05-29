﻿{extends file="base.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h1>Register for tool access</h1>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-block alert-info">
                <h4>Signing up for Wikipedia? You're not in the right place!</h4>
                <p class="mb-0">
                    This form is for requesting access to this tool's management interface (used by existing Wikipedians to help
                    you get an account). If you want to request an account for Wikipedia, then
                    <a class="btn btn-outline-primary btn-sm" href="{$baseurl}/index.php">click here</a>
                </p>
            </div>
            {include file="sessionalerts.tpl"}
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form method="post">
                {include file="security/csrf.tpl"}

                <input type="hidden" name="welcomeenable" value="false"/>
                <input type="hidden" name="template" value="welcome" />
                <input type="hidden" name="sig" value=""/>

                <div class="form-group row">
                    <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                        <label for="name" class="col-form-label">Desired username:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control" id="name" type="text" name="name" required="required" value="{$tplUsername|default:""|escape}"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                        <label for="pass" class="col-form-label">Choose a password:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control password-strength" id="pass" type="password" name="pass" required="required" aria-describedby="passHelp"/>
                        <div class="progress password-strength-progress">
                            <div class="progress-bar" id="password-strength-bar"></div>
                        </div>
                        <span class="form-text text-danger" id="password-strength-warning"></span>
                        <small id="passHelp" class="form-text text-muted">
                            Please <strong>do not</strong> use the same password you use on Wikipedia!
                        </small>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                        <label for="pass2" class="col-form-label">Confirm password:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control" id="pass2" type="password" name="pass2" required="required"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                        <label for="email" class="col-form-label">E-mail Address:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control" id="email" type="text" name="email" required="required" value="{$tplEmail|default:""|escape}"/>
                        <small class="form-text text-muted">This is used to send you automatic notifications about events involving your account in the tool, including sending password reset emails.</small>
                    </div>
                </div>


                {if ! $useOAuthSignup}
                    <fieldset>
                        <legend>You on Wikipedia</legend>

                        <div class="form-group row">
                            <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                                <label for="wname" class="col-form-label">Wikipedia username:</label>
                            </div>
                            <div class="col-sm-8 col-md-5 col-xl-4">
                                <input class="form-control" id="wname" type="text" name="wname" required="required" value="{$tplWikipediaUsername|default:""|escape}"/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                                <label for="conf_revid" class="col-form-label">Confirmation revision ID:</label>
                            </div>
                            <div class="col-sm-6 col-md-3 col-xl-3">
                                <input class="form-control" id="conf_revid" type="text" name="conf_revid" required="required" aria-describedby="confRevHelp"  value="{$tplConfRevId|default:""|escape}"/>
                                <small id="confRevHelp" class="form-text text-muted">
                                    This is just to confirm it is you requesting this account. We will check that the account you've
                                    specified above is the one you've used here.
                                </small>
                            </div>
                            <div class="col-sm-2 col-xl-1">
                                <!--suppress HtmlUnknownAnchorTarget -->
                                <a href="#modalDiffHelp" role="button" class="btn btn-block btn-outline-info" data-toggle="modal">Help!</a>
                            </div>
                        </div>
                    </fieldset>
                {/if}

                <div class="form-group row">
                    <div class="offset-md-2 offset-xl-3 col-sm-4 col-md-3 col-xl-2">
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" id="guidelines" type="checkbox" name="guidelines" required="required"/>
                            <label class="custom-control-label" for="guidelines">
                                I have read and understand the
                                <a href="http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">interface guide</a>.
                            </label>
                        </div>
                    </div>
                </div>

                {if $useOAuthSignup}
                    <div class="row">
                        <div class="offset-sm-4 offset-md-5 col-sm-8 col-md-5 col-xl-4">
                            <div class="alert alert-info">
                                <strong>Heads up!</strong>
                                <p>
                                    After you click the Signup button, you will be redirected to Wikipedia, and be
                                    prompted to allow this tool access to your Wikipedia account.
                                </p>
                                <p class="mb-0">
                                    Please see the guide for more information including information on the specific
                                    grants we are requesting.
                                </p>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="form-group row">
                    <div class="offset-sm-4 offset-md-5 col-sm-8 col-md-5 col-xl-4">
                        <button type="submit" class="btn btn-primary btn-block">Signup</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {include file="modals/register-diff-help.tpl"}
{/block}
