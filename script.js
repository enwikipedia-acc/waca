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
