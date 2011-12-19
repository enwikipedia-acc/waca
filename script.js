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

/*
 * Comment submission check by Manish
 */
var isCommenting=false;  //flag to see if the 'leave pg' action was a commenting action or not

function bypassCommentBlock(){
	isCommenting=true; // set flag true if leave pg was a commenting action
}

function checkComment(){
	if(isCommenting){
		return; //If commenting, bypass the dialog
	}
  	if(document.forms[0].elements[1].value==""){
		return; //If nothing in comment box, bypass dialog
  	}else{
		return "There is an unsubmitted comment, do you still want to leave?";
		//If there's something in the box, give a dialog
	}
}

//Implement only on zoom pages:
var cmtLoadedTimer= setInterval(cmtBlockHook,1000);
function cmtBlockHook(){
	if(document.readyState=="complete"){
		clearInterval(cmtLoadedTimer);
	}
	else{
		return;
	}
	if(document.getElementById("content")){
		if(document.getElementById("content").childNodes[0]){
			var heading=document.getElementById("content").childNodes[0].innerHTML;
			if(heading.indexOf("Details for Request #")!=-1){
				document.forms[0].onsubmit=bypassCommentBlock;
				window.onbeforeunload = checkComment;
			}
		}
	}
}


function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\\\s)'+searchClass+'(\\\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}


//Script portion of abort action capability
//confirmReqCloseQuestions is set through messagemanagement (message 32)
function abortChecker(){
var reqCloseActions=getElementsByClass("request-done")
var abcdefg; //Dummy to check undefined... JS is wierd in this manner
if(getElementsByClass('request-req-create')[0]!==abcdefg){
	//Tag on the Create! link into the array if it exists
	reqCloseActions[reqCloseActions.length]=getElementsByClass('request-req-create')[0]
}
for(var k in reqCloseActions){
var token=reqCloseActions[k].href;

reqCloseActions[k].href="javascript:void(0);"; //So as to avoid icky situations with Javascript event bubbling
reqCloseActions[k].token=token+""; //Store the link in a hidden attribute
reqCloseActions[k].onclick=function(evt){
if(confirmReqCloseQuestions[this.innerHTML]&&(confirmReqCloseQuestions[this.innerHTML]!="")){
	if(confirm(confirmReqCloseQuestions[this.innerHTML])){ 
		if(this.target=="_blank"){
			window.open(this.token); //Might anger popup blockers, but otherwise clicking on the create! link navigates away from the ts page, and it's rather annoying to get back. Right-clicking is better, but if you have just commented or something you get stuck again.
		}else{
			document.location=this.token;
		}

	}

}else{ document.location=this.token; }	

}
}
}