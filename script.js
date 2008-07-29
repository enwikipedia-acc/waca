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