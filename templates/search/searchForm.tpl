{extends file="pagebase.tpl"}
{block name="content"}
    <div class="jumbotron">
        <h1>Search</h1>
        <p> for a request</p>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form method="post">
                {include file="security/csrf.tpl"}
                <div class="form-group">
                    <label for="term">Search term</label>
                    <input class="form-control" type="text" id="term" name="term" placeholder="Search for...">
                </div>
                <div class="form-group">
                    <label for="type">Search as ...</label>
                    <select class="form-control" name="type" id="type">
                        <option value="name">... requested username</option>
                        <option value="email">... email address</option>
                        <option value="ip">... IP address</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i>&nbsp;Search
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}
