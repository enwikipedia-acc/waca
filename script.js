function showhide(listid) {
	list = document.getElementById(listid);
	link = document.getElementById(listid + "-link");
	
	if(list.style.display == "none") {
		list.style.display = "block";
		link.innerHTML = "[hide]";
	} else {
		list.style.display = "none";
		link.innerHTML = "[show]";
	}
	
}

var cookieName = "dismissSiteNotice=";
var cookiePos = document.cookie.indexOf(cookieName);
var cookieValue = "";
var msgClose = "Dismiss";
var siteNoticeID = "57.0";
var siteNoticeValue="<ul><li style=\"font-weight:bold;\">Expect vandal requests, and drop them when you see them.</li><li>Each time you close a request, an email is sent to that user (except for drop)</li><li>If you get an 'invalid checksum' error, it means you have tried to close/defer a request more than once. You can just ignore this, and proceed back to the main page.</li><li>Be sure to check the contributions of the IP making the request, to see if there is any recent vandalism!</li><li><strong>Note that similar accounts can be created by Wikipedia admins - please click defer to be sure!</strong></li><li><strong>Remember: There's no race involved with creating accounts. Ensure you assess each request carefully</strong></li></ul>";

if (cookiePos > -1) {
	cookiePos = cookiePos + cookieName.length;
	var endPos = document.cookie.indexOf(";", cookiePos);
	if (endPos > -1) {
		cookieValue = document.cookie.substring(cookiePos, endPos);
	} else {
		cookieValue = document.cookie.substring(cookiePos);
	}
}
if (cookieValue != siteNoticeID) {
	function dismissNotice() {
		var date = new Date();
		date.setTime(date.getTime() + 7*24*60*60*1000);
		document.cookie = cookieName + siteNoticeID + "; expires="+date.toGMTString() + "; path=/";
		var element = document.getElementById('sitenotice');
		element.style.display = "none";
	}
	document.writeln('	<div id="sitenotice">');
	document.writeln('<span title="Hide sitenotice for one week" id="dismiss"><a href="javascript:dismissNotice();">['+msgClose+']</a></span>');
	document.writeln(siteNoticeValue);
	document.writeln('</div>');
}