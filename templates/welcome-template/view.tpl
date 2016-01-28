{extends file="pagebase.tpl"}
{block name="content"}
    <h3>View template</h3>
    <p><strong>Template code:</strong> <code>{$template->getUserCode()|escape}</code></p>
    <div class="well well-large">
        {$templateHtml}{* Note that this is *NOT* escaped. Yes, this probably is an XSS hole. *}
    </div>
{/block}