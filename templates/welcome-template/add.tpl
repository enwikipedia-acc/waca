﻿{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <h2>Create new template</h2>

        <form method="post" class="form-horizontal">
            {include file="security/csrf.tpl"}
            <div class="control-group">
                <label class="control-label" for="usercode">Display code:</label>
                <div class="controls">
                    <input type="text" class="input-xxlarge" name="usercode" value="" id="usercode"
                           required="required"/>
                    <span class="help-block">This will be displayed to the user when choosing a template</span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="botcode">Bot code:</label>
                <div class="controls">
                    <textarea class="input-xxlarge" rows="10" name="botcode" id="botcode"
                              required="required"></textarea>
        <span class="help-block">This is what will be placed on the page by the bot. <code>$username</code> will be replaced with the account creator's username, and <code>$signature</code> with their signature and a timestamp. Please don't use <code>~~~~</code>
         here as that will use the bot's signature.</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="icon-white icon-ok"></i>&nbsp;Save changes
                </button>
            </div>
        </form>
    </div>
{/block}