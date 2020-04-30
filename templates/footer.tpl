   <hr />

        <footer class="row-fluid">
            <p class="{if $onlineusers == ""}span12{else}span6{/if}"><small>Account Creation Assistance Manager (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>) by <a href="{$baseurl}/team.php">The ACC development team</a> (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>).</small></p>
            {$onlineusers}
        </footer>

    </div><!--/.fluid-container-->
{if $userid != 0}

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="{$baseurl}/lib/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="{$baseurl}/lib/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  	<script src="{$baseurl}/lib/bootstrap-sortable/Scripts/bootstrap-sortable.js" type="text/javascript"></script>

    {* initialise the tooltips *}
  	<script type="text/javascript">
		$(function () {
			$("[rel='tooltip']").tooltip();
		});
  	</script>
	<script type="text/javascript">
		$(function () {
			$("[rel='popover']").popover();
		});
	</script>
	{if $tailscript}
		<script type="text/javascript">
		   {$tailscript}
		</script>
	{/if}
{/if}
    </body>
</html>

