{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <h1>Search<small class="text-muted"> for a request</small></h1>
        </div>
    </div>
    <hr />

    <form method="post">
        {include file="security/csrf.tpl"}
        <div class="row">
            <div class="offset-md-2 col-md-8">
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="term">Search term</label>
                    <input class="col-md-9 form-control" type="text" id="term" name="term" placeholder="Search for...">
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="type">Search as ...</label>
                    <select class="col-md-9 form-control" name="type" id="type">
                        <option value="name">... requested username</option>
                        <option value="email">... email address</option>
                        <option value="ip">... IP address</option>
                    </select>
                </div>
                <div class="form-group row">
                    <button type="submit" class="offset-md-3 col-md-9 btn btn-primary btn-block">
                        <i class="fa fa-search"></i>&nbsp;Search
                    </button>
                </div>
            </div>
        </div>
    </form>
{/block}
