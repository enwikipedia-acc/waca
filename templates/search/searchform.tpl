{extends file="pagebase.tpl"}
{block name="content"}
    <div class="page-header">
        <h1>Search
            <small> for a request</small>
        </h1>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <form method="post" class="form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="term">Search term</label>
                    <div class="controls">
                        <input type="text" id="term" name="term" placeholder="Search for...">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="type">Search as ...</label>
                    <div class="controls">
                        <select name="type" id="type">
                            <option value="name">... requested username</option>
                            <option value="email">... email address</option>
                            <option value="ip">... IP address</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-search icon-white"></i>&nbsp;Search
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}