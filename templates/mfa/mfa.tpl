{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Multi-factor credentials</h1>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
        <div class="col">
            <div class="card mb-4 {if $totpEnrolled}border-success{/if}">
                <div class="card-body">
                    <h4 class="card-title">TOTP</h4>
                    <p class="card-text lead">
                        Use a code generator app on your smartphone to generate a time-based code.
                    </p>
                    <p class="card-text">Apps available include:</p>
                    <ul class="card-text">
                        <li>freeOTP (<a href="https://itunes.apple.com/us/app/freeotp/id872559395" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp" target="_blank">Android</a>)</li>
                        <li><a href="https://www.authy.com/app/" target="_blank">Authy</a></li>
                        <li>Google Authenticator (<a href="https://itunes.apple.com/gb/app/google-authenticator/id388497605?mt=8" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android</a>)</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <p>
                        <strong>TOTP status:</strong>
                        {if $totpEnrolled}
                            <span class="badge badge-success">ENABLED</span>
                        {else}
                            <span class="badge badge-danger">DISABLED</span>
                        {/if}
                    </p>

                    {if $totpEnrolled}
                        <a class="btn btn-block btn-outline-danger" href="{$baseurl}/internal.php/multiFactor/disableTotp">
                            <i class="icon-white icon-remove"></i>&nbsp;Disable
                        </a>
                    {else}
                        {if $allowedTotp}
                            <a class="btn btn-block btn-secondary" href="{$baseurl}/internal.php/multiFactor/enableTotp">
                                <i class="icon-white icon-ok"></i>&nbsp;Enable
                            </a>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card mb-4 {if $yubikeyOtpEnrolled}border-success{/if}">
                <div class="card-body">
                    <h4 class="card-title">YubiKey OTP</h4>
                    <p class="card-text lead">
                        Use a <a href="https://www.yubico.com/products/yubikey-hardware/compare-yubikeys/">YubiKey</a> in OTP mode to provide code generation.
                    </p>
                </div>
                <div class="card-footer">
                    <p>
                        <strong>YubiKey OTP status:</strong>
                        {if $yubikeyOtpEnrolled}
                            <span class="badge badge-success">ENABLED</span>
                        {else}
                            <span class="badge badge-danger">DISABLED</span>
                        {/if}
                    </p>

                    {if $yubikeyOtpEnrolled}
                        <p><strong>YubiKey identity:</strong> {$yubikeyOtpIdentity|escape|default:'N/A'}</p>
                        <p><strong>YubiKey serial:</strong> {$yubikeyOtpIdentity|demodhex|default:'N/A'}</p>

                        <a class="btn btn-block btn-outline-danger" href="{$baseurl}/internal.php/multiFactor/disableYubikeyOtp">
                            <i class="icon-white icon-remove"></i>&nbsp;Disable
                        </a>
                    {else}
                        {if $allowedYubikey}
                            <a class="btn btn-block btn-secondary" href="{$baseurl}/internal.php/multiFactor/enableYubikeyOtp">
                                <i class="icon-white icon-ok"></i>&nbsp;Enable
                            </a>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card mb-4 {if $webAuthnEnrolled}border-success{/if}">
                <div class="card-body">
                    <h4 class="card-title">WebAuthn</h4>
                    <p class="card-text lead">
                        Use your computer or a USB hardware token to authenticate.
                    </p>
                    <p>
                        Use a USB hardware token such FIDO2 U2F token or your computer's underlying authentication system, including options such as:
                    </p>
                    <ul>
                        <li>YubiKeys or other U2F tokens</li>
                        <li>Windows Hello</li>
                        <li>Android lockscreen</li>
                        <li>TouchID or FaceID</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <p>
                        <strong>WebAuthn status:</strong>
                        {if $webAuthnEnrolled}
                            <span class="badge badge-success">ENABLED</span>
                        {else}
                            <span class="badge badge-danger">DISABLED</span>
                        {/if}
                    </p>

                    {if $webAuthnEnrolled}
                    <hr />

                    <strong>Enrolled tokens:</strong>
                    <table class="table table-striped">
                        <thead>
                        <tr><th>Token</th><th>Last Used</th><td></td></tr>
                        </thead>
                        <tbody>
                        {foreach from=$webAuthnTokens item=token}
                            <tr>
                                <th>{$token.tokenName|escape}</th>
                                <td>
                                    {if $token.lastUsed === null}
                                        <span class="text-muted">never used</span>
                                    {else}
                                        <span data-toggle="tooltip" data-placement="top" title="{$token.lastUsed|unixtime|date}">{$token.lastUsed|unixtime|relativedate}</span>
                                    {/if}
                                </td>
                                <td class="table-button-cell">
                                    <form action="{$baseurl}/internal.php/multiFactor/disableWebAuthn" method="post">
                                        <input type="hidden" name="publicKeyId" value="{$token.publicKeyId|escape}" />
                                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    {/if}

                    {if $webAuthnEnrolled}
                        <a class="btn btn-block btn-outline-secondary"
                           href="{$baseurl}/internal.php/multiFactor/enableWebAuthn">
                            <i class="icon-white icon-ok"></i>&nbsp;Enroll another authenticator
                        </a>
                    {else}
                        {if $allowedWebAuthn}
                            <a class="btn btn-block btn-secondary"
                               href="{$baseurl}/internal.php/multiFactor/enableWebAuthn">
                                <i class="icon-white icon-ok"></i>&nbsp;Enable
                            </a>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Scratch tokens</h4>
                    <p class="card-text lead">
                        Regenerate your emergency scratch tokens.
                    </p>
                    <p class="card-text">
                        You can use emergency scratch tokens as a one-time password in case you lose the ability to generate codes normally.
                    </p>
                    <p class="card-text">
                        These are one-time use, and <strong class="text-danger">for emergencies only</strong> - be sure to cross off each code as you use it, and regenerate them when you get low.
                    </p>
                </div>
                <div class="card-footer">
                    <p>
                        <strong>Scratch tokens remaining:</strong>
                        {if $scratchEnrolled}
                            {if $scratchRemaining >= 5}
                                <span class="badge badge-success badge-pill">{$scratchRemaining}</span>
                            {elseif $scratchRemaining >= 3}
                                <span class="badge badge-warning badge-pill">{$scratchRemaining}</span>
                            {else}
                                <span class="badge badge-danger badge-pill">{$scratchRemaining}</span>
                            {/if}
                        {else}
                            <span class="badge badge-danger badge-pill">0</span>
                        {/if}
                    </p>

                    <a class="btn btn-block {if $scratchRemaining >= 5}btn-outline-secondary{else}btn-primary{/if}" href="{$baseurl}/internal.php/multiFactor/scratch">
                        <i class="icon-white icon-refresh"></i>&nbsp;Regenerate
                    </a>
                </div>
            </div>
        </div>

    </div>
{/block}
