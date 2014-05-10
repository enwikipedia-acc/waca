<div class="row-fluid">
  <h2>{if $readonly}View{else}Edit{/if} message</h2>
  {if $readonly}
    <div class="form-horizontal">
  {else}
    <form action="{$baseurl}/acc.php?action=messagemgmt&amp;edit={$message->getId()}&amp;submit=1" method="post" class="form-horizontal">
  {/if}
    <div class="control-group">
      <label for="maildesc" class="control-label" >Description</label>
      <div class="controls">
        <input type="text" name="maildesc" id="maildesc" value="{$message->getDescription()|escape}" class="span4" {if $readonly}disabled="disabled"{/if}/>
      </div>
    </div>

    <div class="control-group">
      <label for="mailtext" class="control-label">Update Counter</label>
      <div class="controls">
        <span class="uneditable-input span1">{$message->getUpdateCounter()}</span>
      </div>
    </div>

    <div class="control-group">
      <label for="mailtext" class="control-label">Content</label>
      <div class="controls">
        <textarea name="mailtext" id="mailtext" rows="20" class="input-block-level" {if $readonly}disabled="disabled"{/if}>{$message->getContent()|escape}</textarea>
      </div>
    </div>

    <div class="form-actions">
      <a href="{$baseurl}/acc.php?action=messagemgmt" class="btn">Cancel</a>
      {if !$readonly}
        <button type="submit" class="btn btn-success">
          <i class="icon-white icon-ok"></i>&nbsp;Save
        </button>
      {/if}
    </div>
  {if $readonly}
    </div>
  {else}
    </form>
  {/if}
</div>