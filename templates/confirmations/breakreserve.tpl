<div class="row-fluid">
	<div class="span12">
		<p>Are you sure you wish to break {$reservedUser->getUsername()|escape}'s reservation?</p>

	</div>
</div>

<div class="row-fluid">
  <a class="btn btn-warning btn-block offset3 span3" href="{$baseurl}/acc.php?action=breakreserve&resid={$request->getId()}&confirm=1">Yes</a>
  <a class="btn btn-block span3" href="{$baseurl}/acc.php?action=zoom&id={$request->getId()}">No</a>
</div>


