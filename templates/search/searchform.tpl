<form action="search.php" method="get" class="form-horizontal">
  <div class="control-group">
    <label class="control-label" for="term">Search term</label>
    <div class="controls">
      <input type="text" id="term" name="term" placeholder="Search for...">
        </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="term">Search as ...</label>
    <div class="controls">
      <select name="type">
        <option value="Request">... requested username</option>
        <option value="email">... email address</option>
        {if $currentUser->isAdmin() || $currentUser->isCheckuser()}
        <option value="IP">... IP address</option>
        <option value="CIDR" disabled="disabled">... CIDR Range</option>{* not implemented yet! *}
        {else}
        <option value="IP" disabled="disabled">... IP address</option>
        <option value="CIDR" disabled="disabled">... CIDR Range</option>
        {/if}
      </select>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <i class="icon-search icon-white"></i>&nbsp;Search
    </button>
  </div>
</form>