<h2>View template</h2>
<br />
Template ID: {$template->getId()}
<br />
Display code:  {$template->getUserCode()}
<br />
Bot code:  {$template->getBotCode()}
<br />
{displayPreview($template->getBotCode())}
<br /><a href='{$baseurl}/acc.php?action=templatemgmt'>Back</a>
