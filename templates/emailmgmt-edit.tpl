<h1>{$emailmgmtpage} Email template</h1>
<div class="row-fluid">
	<div class="span12">
		<form class="form-horizontal" method="post">
			<div class="control-group">
				<label class="control-label" for="inputName">Email template name</label>
				<div class="controls">
					<input type="text" id="inputName" name="ename" value="{$ename}"{if $userisadmin == false} disabled{/if}>
					<span class="help-block">The name of the Email template. Note that this will be used to label the relevant close button on the request zoom pages.</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="inputText">Email text</label>
				<div class="controls">
					<textarea class="input-xxlarge" id="inputText" rows="20" name="etext"{if $userisadmin == false} disabled{/if}>{$etext}</textarea>
					<span class="help-block">The text of the Email which will be sent to the requesting user.</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="inputQuestion">JavaScript question</label>
				<div class="controls">
					<input type="text" class="input-xxlarge" id="inputQuestion" name="equestion" size="75" value="{$equestion}" {if $userisadmin == false} disabled{/if}>
					<span class="help-block">Text to appear in a JavaScript popup (if enabled by the user) when they attempt to use this Email template.</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="inputCreated">Is this template used for created requests?</label>
				<div class="controls">
					<input type="checkbox"  id="inputCreated" name="ecreated"{if $userisadmin == false} disabled{/if}{if $ecreated == true} checked{/if}>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="inputActive">Is this template enabled?</label>
				<div class="controls">
					<input type="checkbox" id="inputActive" name="eactive"{if $userisadmin == false} disabled{/if}{if $eactive == 1} checked{/if}>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn" name="submit"{if $userisadmin == false} disabled{/if}>Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>
			