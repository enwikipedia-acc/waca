{extends file="pagebase.tpl"}
{block name="content"}
<div class="row">
    <div class="col-md-12" >
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Development Team <small class="text-muted">We're not all geeks!</small></h1>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">

        <div class="accordion" id="teamAccordion">
            <div class="card">
                <div class="card-header position-relative py-0" id="accordionHeader1">
                    <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#teamAccordion" data-target="#collapseOne">Active Developers</button>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="accordionHeader1" data-parent="#teamAccordion">
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2">
                            {foreach from=$developer item=devInfo key=devName}
                                {include file="team/user.tpl"}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header position-relative py-0" id="accordionHeader2">
                    <button class="btn btn-link stretched-link" data-toggle="collapse" data-parent="#teamAccordion" data-target="#collapseTwo">Inactive Developers</button>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="accordionHeader2" data-parent="#teamAccordion">
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2">
                            {foreach from=$inactiveDeveloper item=devInfo key=devName}
                                {include file="team/user.tpl"}
                            {/foreach}
                        </div>
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
            ACC is kindly hosted on <a href="https://wikitech.wikimedia.org/wiki/Portal:Cloud_VPS">Wikimedia Cloud VPS</a>.
            Our code repository is hosted by GitHub and can be found <a href="https://github.com/enwikipedia-acc/waca/">here</a>.
            Thanks to all those who have submitted patches who are not mentioned above!
        </p>
    </div>
</div>
{/block}
