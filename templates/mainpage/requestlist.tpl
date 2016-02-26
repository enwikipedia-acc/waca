<!-- requestlist.tpl -->
{if $requests.total > $requestLimitShowOnly}
	{include file="alert.tpl" alertblock="0" alerttype="alert-error" alertclosable="0" alertheader="Miser mode:"
		  alertmessage="Not all requests are shown for speed. Only {$requestLimitShowOnly} of {$requests.total} are shown here."}
{/if}
{if count($requests.requests) > 0}
	{include file="mainpage/requesttable.tpl" requests=$requests.requests userlist=$requests.userlist}
{else}
	<em>No requests at this time</em>
{/if}