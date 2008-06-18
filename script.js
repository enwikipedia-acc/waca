sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

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