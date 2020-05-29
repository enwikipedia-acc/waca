{extends file="base.tpl"}
{block name="sitenotice"}
    {if $siteNoticeText !== ''}
        <div class="row">
            <!-- site notice -->
            <div class="col-md-12 sitenotice-container p-0 {$siteNoticeState|default:''}" data-sitenotice="{$siteNoticeVersion}">
                <div class="alert alert-info sitenotice">
                    <button type="button" class="close sitenotice-dismiss">&times;</button>
                    {$siteNoticeText}
                </div>
            </div>
        </div><!--/row-->
    {/if}

    {include file="sessionalerts.tpl"}
{/block}
