{extends file="base.tpl"}
{block name="sitenotice"}
    {if ! $currentUser->isCommunityUser()}
        <div class="row">
            <!-- site notice -->
            <div class="col-md-12 sitenotice-container p-0">
                <div class="alert alert-warning sitenotice">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {$siteNoticeText}
                </div>
            </div>
        </div><!--/row-->
    {/if}

    {include file="sessionalerts.tpl"}
{/block}
