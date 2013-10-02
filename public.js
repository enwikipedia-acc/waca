/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

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

var dismissMessage = function(message) {
	document.getElementById(message).style.display = "none";
};
