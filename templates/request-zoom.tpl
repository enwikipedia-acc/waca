<!-- tpl:request-zoom.tpl -->
<div class="row-fluid">
  <!-- page header -->
  <div class="span12">
    <h2>Details for Request #{$request->getId()}:</h2>
  </div>
</div><!--/row-->   

{if $request->getEmailConfirm() != "Confirmed" && $request->getEmailConfirm() != "" && (!$ecoverride) && $ecenable}
{include file="alert.tpl" alertblock="1" alerttype="alert-error" alertclosable="0" alertheader="Email confirmation required."
alertmessage="The email address has not yet been confirmed for this request, so it can not yet be closed or viewed."}
{else}
{include file="zoom-parts/zoom-base.tpl"}
{/if}
