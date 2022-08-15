{if count($nav__domainList) > 1}
    <div class="col-md-2 col-lg-2 col-xl-1 {$offset|default:''} d-none d-md-block">
        <div class="custom-control custom-switch" data-toggle="tooltip" data-html="true" title="Define this setting across all domains. This only has an effect if your account has access to multiple domains.{if !$settingAvailable && $settingState}<br /><br /><strong>This option is only configurable at a global level</strong>{/if}{if !$settingAvailable && !$settingState}<br /><br /><strong>This option is only configurable at the local level</strong>{/if}">
            <input type="checkbox" class="custom-control-input" id="{$settingName}Global" name="{$settingName}Global" {if $settingState}checked{/if} {if !$settingAvailable}disabled{/if}>
            <label class="custom-control-label" for="{$settingName}Global">Global</label>
        </div>
    </div>
{else}
    <input type="hidden" name="{$settingName}Global" value="{if $settingState}on{else}off{/if}">
{/if}