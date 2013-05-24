<div class="alert {if $alertblock == true}alert-block{/if} {$alerttype}">
  {if $alertclosable == true}<button type="button" class="close" data-dismiss="alert">&times;</button>{/if}
  {if $alertheader != ""}<h4>{$alertheader}</h4>{/if}
  {$alertmessage}
</div>