{include 
	file="alert.tpl" 
	alertblock="1" 
	alerttype="alert-success" 
	alertclosable="0" 
	alertheader="Saved!"
	alertmessage="Message {$message->getDescription()|escape} ({$message->getId()}) updated."
}