<div class="page-header">
	<h1>
		Email Management
		<small>
			{$emailmgmtpage} Email template
		</small>
	</h1>
</div>

<div class="row-fluid">
	<div class="span12">
		<form class="form-horizontal" method="post">

			<div class="control-group">
				<label class="control-label" for="inputName">Email template name</label>
				<div class="controls">
					<input type="text" id="inputName" name="name" value="{$emailTemplate->getName()|escape}"{if !$currentUser->isAdmin()} disabled{/if} />
					<span class="help-block">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</span>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="inputText">Email text</label>
				<div class="controls">
					<textarea class="input-xxlarge" id="inputText" rows="20" name="text"{if !$currentUser->isAdmin()} disabled{/if}>{$emailTemplate->getText()|escape}</textarea>
					<span class="help-block">The text of the Email which will be sent to the requesting user.</span>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="inputQuestion">JavaScript question</label>
				<div class="controls">
					<input type="text" class="input-xxlarge" id="inputQuestion" name="jsquestion" size="75" value="{$emailTemplate->getJsquestion()|escape}" {if !$currentUser->isAdmin()} disabled{/if} />
					<span class="help-block">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</span>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="inputDefaultAction">Default action</label>
				<div class="controls">
					<select class="input-large" id="inputDefaultAction" name="defaultaction" {if !$currentUser->isAdmin() || $id == $createdid} disabled{/if}>
						<option value="" {if $emailTemplate->getDefaultAction() == ""}selected="selected"{/if}>No default</option>
						<optgroup label="Close request...">
							<option value="created" {if $emailTemplate->getDefaultAction() == "created"}selected="selected"{/if}>Close request as created</option>
							<option value="not created" {if $emailTemplate->getDefaultAction() == "not created"}selected="selected"{/if}>Close request as NOT created</option>
						</optgroup>
						<optgroup label="Defer to...">
							{foreach $requeststates as $state}
							<option value="{$state@key}" {if $emailTemplate->getDefaultAction() == $state@key}selected="selected"{/if}>Defer to {$state.deferto|capitalize}</option>
							{/foreach}
						</optgroup>
					</select>
					<span class="help-block">The default action to take on custom close. This is also used for populating decline and created dropdowns</span>
				</div>
			</div>

			<div class="control-group">
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="inputActive" name="active"{if !$currentUser->isAdmin() || $id == $createdid} disabled{/if}{if {$emailTemplate->getActive()}} checked{/if} />
						Enabled
					</label>
				</div>
			</div>

			<div class="control-group">
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="inputPreloadonly" name="preloadonly"{if !$currentUser->isAdmin() || $id == $createdid} disabled{/if}{if {$emailTemplate->getPreloadOnly()}} checked{/if} />
						Available for preload only
					</label>
				</div>
			</div>

			<div class="form-actions">
				<a class="btn" href="{$baseurl}/acc.php?action=emailmgmt">Cancel</a>
				<button type="submit" class="btn btn-primary" name="submit"{if !$currentUser->isAdmin()} disabled{/if}>
					<i class="icon-white icon-ok"></i>&nbsp;Save
				</button>
			</div>

		</form>
	</div>
</div>
