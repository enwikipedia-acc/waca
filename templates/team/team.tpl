<div class="page-header">
    <h1>Development Team<small> We're not all geeks!</small></h1>
</div>
<div class="row-fluid">
    <div class="span12">
        <div class="accordion" id="accordion2">
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">Active Developers</a>
                </div>
                <div id="collapseOne" class="accordion-body collapse in">
                    <div class="accordion-inner">
                        {foreach from=$developer item=devInfo key=devName}
                        {include file="team/user.tpl"}
                        {/foreach}
                    </div>
                </div>
            </div>

            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">Inactive Developers</a>
                </div>
                <div id="collapseTwo" class="accordion-body collapse">
                    <div class="accordion-inner">
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
<div class="row-fluid">
    <div class="span12">
        <p>
            ACC is kindly hosted by the Wikimedia Labs.
            Our code respository is hosted by GitHub and can be found <a href="https://github.com/enwikipedia-acc/waca/">here</a>.
        </p>
    </div>
</div>