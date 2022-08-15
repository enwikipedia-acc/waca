<div class="modal fade" id="modalGlobalSettings" tabindex="-1" aria-labelledby="modalGlobalSettingsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGlobalSettingsLabel">Help on global settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Some preferences can be set either globally for all domains, or locally for this domain ({$currentDomain->getLongName()|escape}).
                </p>
                <p>
                    If you want a setting to apply globally, you can mark the preference as a "global" setting.
                    If you want a setting to apply only to {$currentDomain->getLongName()|escape}, you can remove the global mark.
                </p>

                <p>
                    If a domain has no local setting, the global setting will apply.
                    When you mark a setting as global, the local setting will be deleted, and the global setting set to the provided value.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
