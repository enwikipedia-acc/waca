{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Multi-factor credentials</h1>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 well">
                <h4>TOTP</h4>
                <p>
                    Use a code generator app on your smartphone to generate a time-based code. Apps available include:
                    <ul>
                        <li>freeOTP (<a href="https://itunes.apple.com/us/app/freeotp/id872559395" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp" target="_blank">Android</a>)</li>
                        <li><a href="https://www.authy.com/app/" target="_blank">Authy</a></li>
                        <li>Google Authenticator (<a href="https://itunes.apple.com/gb/app/google-authenticator/id388497605?mt=8" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android</a>)</li>
                    </ul>
                </p>
                <hr />
                <p>
                    <strong>TOTP status:</strong>
                    {if $totpEnrolled}
                        <span class="label label-success">ENABLED</span>
                    {else}
                        <span class="label label-important">DISABLED</span>
                    {/if}
                </p>

                {if $totpEnrolled}
                    <a class="btn btn-block btn-danger" href="{$baseurl}/internal.php/multiFactor/disableTotp">
                        <i class="icon-white icon-remove"></i>&nbsp;Disable
                    </a>
                {else}
                    <a class="btn btn-block btn-info" href="{$baseurl}/internal.php/multiFactor/enableTotp">
                        <i class="icon-white icon-ok"></i>&nbsp;Enable
                    </a>
                {/if}
            </div>

            <div class="span4 well">
                <h4>YubiKey OTP</h4>
                <p>
                    Use a <a href="https://www.yubico.com/products/yubikey-hardware/compare-yubikeys/">YubiKey</a> in OTP mode to provide code generation.
                </p>
                <hr />
                <p>
                    <strong>YubiKey OTP status:</strong>
                    {if $yubikeyOtpEnrolled}
                        <span class="label label-success">ENABLED</span>
                    {else}
                        <span class="label label-important">DISABLED</span>
                    {/if}
                </p>

                {if $yubikeyOtpEnrolled}
                    <p><strong>YubiKey identity:</strong> {$yubikeyOtpIdentity|escape|default:'N/A'}</p>
                    <p><strong>YubiKey serial:</strong> {$yubikeyOtpIdentity|demodhex|default:'N/A'}</p>

                    <a class="btn btn-block btn-danger" href="{$baseurl}/internal.php/multiFactor/disableYubikeyOtp">
                        <i class="icon-white icon-remove"></i>&nbsp;Disable
                    </a>
                {else}
                    <a class="btn btn-block btn-info" href="{$baseurl}/internal.php/multiFactor/enableYubikeyOtp">
                        <i class="icon-white icon-ok"></i>&nbsp;Enable
                    </a>
                {/if}
            </div>

            <div class="span4 well">
                <h4>Universal Second Factor (U2F)</h4>
                <p>
                    Use a <a href="https://fidoalliance.org/about/what-is-fido/" target="_blank">FIDO U2F</a> device
                </p>
                <hr />
                <p>
                    <strong>U2F status:</strong>
                    {if $u2fEnrolled}
                        <span class="label label-success">ENABLED</span>
                    {else}
                        <span class="label label-important">DISABLED</span>
                    {/if}
                </p>

                {if $u2fEnrolled}
                    <a class="btn btn-block btn-danger" href="{$baseurl}/internal.php/multiFactor/disableU2F">
                        <i class="icon-white icon-remove"></i>&nbsp;Disable
                    </a>
                {else}
                    <a class="btn btn-block btn-info" href="{$baseurl}/internal.php/multiFactor/enableU2F">
                        <i class="icon-white icon-ok"></i>&nbsp;Enable
                    </a>
                {/if}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span4 well">
                <h4>Scratch tokens</h4>
                <p>
                    You can use emergency scratch tokens as a one-time password in case you lose the ability to generate codes normally.
                    These are one-time use, and for emergencies only - be sure to cross off each code as you use it, and regenerate them when you get low.
                </p>
                <p>
                    Regenerate your emergency scratch tokens here.
                </p>
                <hr />
                <p>
                    <strong>Scratch tokens remaining:</strong>
                    {if $scratchEnrolled}
                        {if $scratchRemaining >= 5}
                            <span class="badge badge-success">{$scratchRemaining}</span>
                        {elseif $scratchRemaining >= 3}
                            <span class="badge badge-warning">{$scratchRemaining}</span>
                        {else}
                            <span class="badge badge-important">{$scratchRemaining}</span>
                        {/if}
                    {else}
                        <span class="badge badge-important">0</span>
                    {/if}
                </p>

                <a class="btn btn-block btn-info" href="{$baseurl}/internal.php/multiFactor/scratch">
                    <i class="icon-white icon-refresh"></i>&nbsp;Regenerate
                </a>
            </div>
        </div>

    </div>
{/block}