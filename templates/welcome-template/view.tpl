{extends file="pagebase.tpl"}
{block name="content"}
    <h3>View template</h3>
    <p><strong>Template code:</strong> <code>{$template->getBotCode()|escape}</code></p>
    <p>Please note, this is only an approximation of the rendering of the template. It should only be used for verifying the text of the template is correct with the correct substitutions, not for verifying correct appearance and layout.</p>
    <div class="card card-body">
        {$templateHtml}{* Note that this is *NOT* escaped. Yes, this probably is an XSS hole. *}
    </div>
{/block}
