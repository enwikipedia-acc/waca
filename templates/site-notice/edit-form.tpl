{extends file="pagebase.tpl"}
{block name="content"}
    <div>
        <h2>Edit site notice</h2>
        <form method="post">
            {include file="security/csrf.tpl"}
            <div class="form-group">
                <label for="mailtext">Content</label>
                <textarea name="mailtext" id="mailtext" rows="20"
                          class="form-control">{$message->getContent()|escape}</textarea>
            </div>

            <input type="hidden" name="updateversion" value="{$message->getUpdateVersion()|escape}" />

            <div class="form-group">
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i>&nbsp;Save</button>
            </div>
        </form>
    </div>
{/block}
