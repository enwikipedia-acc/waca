{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Enable Multi-factor credentials</h1>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 offset4 well">
                <form class="container-fluid" style="margin-top:20px;" method="post" id="u2fEnroll">
                    {include file="security/csrf.tpl"}
                    <div class="row-fluid">
                        <h4>Register your device</h4>
                    </div>

                    <hr />

                    <div style="text-align: center">
                        <p>Please press the button on your U2F device</p>
                        <p>If your U2F device doesn't have a button, please remove and insert it</p>

                        {include file="icons/yubikey.tpl"}
                    </div>

                    <input type="hidden" name="u2fData" id="u2fData" />
                    <input type="hidden" name="u2fRequest" id="u2fRequest" />
                    <input type="hidden" name="stage" value="enroll">
                </form>
            </div>
        </div>
    </div>
{/block}