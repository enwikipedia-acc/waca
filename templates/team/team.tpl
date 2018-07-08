{extends file="base.tpl"}
{block name="content"}
<div class="page-header">
    <h1>Development Team<small> We're not all geeks!</small></h1>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="accordion" id="accordion1">
            <div class="card">
                <div class="card-header" id="accordianheader1">
                    <button class="btn btn-link" data-toggle="collapse" data-parent="#accordion1" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Active Developers</a>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="accordianheader1" data-parent="#accordion1">
                    <div class="card-body">
                        {foreach from=$developer item=devInfo key=devName}
                        {include file="team/user.tpl"}
                        {/foreach}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" id="accordianheader2">
                    <button class="btn btn-link" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">Inactive Developers</a>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="accordianheader2" data-parent="#accordion2">
                    <div class="card-body">
                        {foreach from=$inactiveDeveloper item=devInfo key=devName}
                        {include file="team/user.tpl"}
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-md-12">
        <p>
            ACC is kindly hosted by <a href="https://wikitech.wikimedia.org/">Wikimedia Labs</a>.
            Our code respository is hosted by GitHub and can be found <a href="https://github.com/enwikipedia-acc/waca/">here</a>.
            Thanks to all those who have submitted patches who are not mentioned above!
        </p>
    </div>
</div>
{/block}
