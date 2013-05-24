   <hr />

        <footer class="row-fluid">
            <p class="{if $onlineusers == ""}span12{else}span6{/if}"><small>Account Creation Assistance Manager (<a href="https://github.com/enwikipedia-acc/waca/tree/{$toolversion}">version {$toolversion}</a>) by <a href="{$tsurl}/team.php">The ACC development team</a> (<a href="https://github.com/enwikipedia-acc/waca/issues">Bug reports</a>).</small></p>
            {$onlineusers}
        </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="{$tsurl}/lib/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script src="{$tsurl}/lib/bootstrap-2.3.1/js/bootstrap.js" type="text/javascript"></script>

  </body>
</html>

