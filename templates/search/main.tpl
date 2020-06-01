{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Search <small class="text-muted">for a request</small></h1>
            </div>
        </div>
    </div>

    <form>
        <div class="form-group row">
            <div class="col col-xl-5">
                <label class="sr-only" for="term">Search term</label>
                <input class="form-control" type="text" id="term" name="term" placeholder="Search for..." value="{$term|default:''}">
            </div>

            <div class="col-md-auto">
                <label class="sr-only" for="type">Search type</label>
                <select class="form-control" name="type" id="type">
                    <option value="name" {if $target == 'name'}selected{/if}>... by requested username</option>
                    <option value="email" {if $target == 'email'}selected{/if}>... by email address</option>
                    <option value="ip" {if $target == 'ip'}selected{/if}>... by IP address</option>
                </select>
            </div>

            <div class="col-md-auto">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-search"></i>&nbsp;Search
            </button>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-12 my-3">
                <div class="custom-control custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" id="inlineCheckbox1" name="limit" value="50" {if $limit == 50}checked{/if} />
                    <label class="custom-control-label" for="inlineCheckbox1">50 results</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" id="inlineCheckbox2" name="limit" value="100" {if $limit == 100}checked{/if} />
                    <label class="custom-control-label" for="inlineCheckbox2">100 results</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input class="custom-control-input" type="radio" id="inlineCheckbox3" name="limit" value="500" {if $limit == 500}checked{/if} />
                    <label class="custom-control-label" for="inlineCheckbox3">500 results</label>
                </div>
            </div>
        </div>
    </form>

    {if $hasResultset}
        <div class="row">
            <div class="col-md-12">
                {include file="pager.tpl"}
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p class="lead">Search results for "{$term|escape}" as {$target}...</p>
                {if $resultCount == 0}
                    {include file="alert.tpl" alertblock=false alerttype="alert-info" alertclosable=false alertheader='' alertmessage='No requests found!'}
                {else}
                    {include file="mainpage/requesttable.tpl" showStatus=true list=$requests sort=$defaultSort dir=$defaultSortDirection}
                {/if}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                {include file="pager.tpl"}
            </div>
        </div>
    {/if}
{/block}
