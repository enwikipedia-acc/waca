{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row-fluid">
        <h2>Edit site notice</h2>
        <form method="post" class="form-horizontal">
            {include file="security/csrf.tpl"}

            <div class="control-group">
                <label for="mailtext" class="control-label">Content</label>
                <div class="controls">
                    <textarea name="mailtext" id="mailtext" rows="20"
                              class="input-block-level">{$message->getContent()|escape}</textarea>
                </div>
            </div>

            <input type="hidden" name="updateversion" value="{$message->getUpdateVersion()|escape}" />

            <div class="form-actions">
                <button type="submit" class="btn btn-success"><i class="icon-white icon-ok"></i>&nbsp;Save</button>
            </div>
        </form>
    </div>
{/block}