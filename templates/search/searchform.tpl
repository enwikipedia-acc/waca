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
        <option value="IP">... IP address</option>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">
      <i class="icon-search icon-white"></i>&nbsp;Search
    </button>
  </div>
</form>
