{extends file="base.tpl"}
{block name="sitenotice"}
    {if ! $currentUser->isCommunityUser()}
        <div class="row-fluid">
            <!-- site notice -->
            <div class="span12">
                <div class="alert alert-block">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {$siteNoticeText}
                </div>
            </div>
        </div><!--/row-->
    {/if}

    {if count($alerts) > 0}
        {foreach $alerts as $a}
            {$a->getAlertBox()}
        {/foreach}
    {/if}
{/block}