/*
2/22/11 initial version - origimnal by pradeep, at http://www.go4expert.com/forums/showthread.php?t=357
*/
function autoCompleteDB() {
	this.aNames=new Array();
	}

 autoCompleteDB.prototype.assignArray=function(aList) {
	this.aNames=aList;
	};

 autoCompleteDB.prototype.getMatches=function(str,aList,maxSize) {
	/* debug */ //alert(maxSize+"ok getmatches");
	var ctr=0;
	for(var i in this.aNames) {
		 if(this.aNames[i].toLowerCase().indexOf(str.toLowerCase())==0) { /*looking for case insensitive matches */
		  aList.push(this.aNames[i]);
		  ctr++;
	  	}
	   if(ctr==(maxSize-1))			/* counter to limit no of matches to maxSize */
		  break;
		}
	};

 function autoComplete(aNames,oText,oDiv,maxSize)  {
	this.oText=oText;
	this.oDiv=oDiv;
	this.maxSize=maxSize;
	this.cur=-1;

	//alert(oText+","+this.oDiv);	/*debug here */

	this.db=new autoCompleteDB();
	this.db.assignArray(aNames);

	oText.onkeyup=this.keyUp;
	oText.onkeydown=this.keyDown;
	oText.autoComplete=this;
	oText.onblur=this.hideSuggest;
	}

 autoComplete.prototype.hideSuggest=function() {
	this.autoComplete.oDiv.style.visibility="hidden";
	};

 autoComplete.prototype.selectText=function(iStart,iEnd) {
	if(this.oText.createTextRange) {				/* For IE */
	   var oRange=this.oText.createTextRange();
	   oRange.moveStart("character",iStart);
	   oRange.moveEnd("character",iEnd-this.oText.value.length);
	   oRange.select();
		}
	else if(this.oText.setSelectionRange) {			/* For Mozilla */
	   this.oText.setSelectionRange(iStart,iEnd);
		}
	this.oText.focus();
	};

 autoComplete.prototype.textComplete=function(sFirstMatch) {
	if(this.oText.createTextRange || this.oText.setSelectionRange)  {
	   var iStart=this.oText.value.length;
	   this.oText.value=sFirstMatch;
	   this.selectText(iStart,sFirstMatch.length);
		}
	};

 autoComplete.prototype.keyDown=function(oEvent)  {
	oEvent=window.event || oEvent;
	iKeyCode=oEvent.keyCode;

	switch(iKeyCode)  {
	   case 38:		//up arrow
		  this.autoComplete.moveUp();
		  break;
	   case 40:		//down arrow
		  this.autoComplete.moveDown();
		  break;
	   case 13:		//return key
		  window.focus();
		  break;
		}
	};

 autoComplete.prototype.moveDown=function() {
	if(this.oDiv.childNodes.length>0 && this.cur<(this.oDiv.childNodes.length-1))  {
	   ++this.cur;
	   for(var i=0;i<this.oDiv.childNodes.length;i++)   {
		  if(i==this.cur)		  {
			 this.oDiv.childNodes[i].className="over";
			 this.oText.value=this.oDiv.childNodes[i].innerHTML;
		 	}
		  else {
			 this.oDiv.childNodes[i].className="";
		 	}
	  	}
		}
	};

 autoComplete.prototype.moveUp=function()  {
	if(this.oDiv.childNodes.length>0 && this.cur>0)  {
	   --this.cur;
	   for(var i=0;i<this.oDiv.childNodes.length;i++)	{
		  if(i==this.cur)	{
			 this.oDiv.childNodes[i].className="over";
			 this.oText.value=this.oDiv.childNodes[i].innerHTML;
		 	}
		  else {
			 this.oDiv.childNodes[i].className="";
		 	}
	  	}
		}
	};

 autoComplete.prototype.keyUp=function(oEvent)  {
	oEvent=oEvent || window.event;
	var iKeyCode=oEvent.keyCode;
	if(iKeyCode==8 || iKeyCode==46)	{
	   this.autoComplete.onTextChange(false); /* without autocomplete */
		}
 else if (iKeyCode < 32 || (iKeyCode >= 33 && iKeyCode <= 46) || (iKeyCode >= 112 && iKeyCode <= 123))   {
	   //ignore
		}
	else  {
	   this.autoComplete.onTextChange(true); /* with autocomplete */
		}
	};

 autoComplete.prototype.positionSuggest=function(){		/* to calculate the appropriate poistion of the dropdown */
	var oNode=this.oText;
	var x=0,y=oNode.offsetHeight;

	while(oNode.offsetParent && oNode.offsetParent.tagName.toUpperCase() != 'BODY')   {
	   x+=oNode.offsetLeft;
	   y+=oNode.offsetTop;
	   oNode=oNode.offsetParent;
		}

	x+=oNode.offsetLeft;
	y+=oNode.offsetTop;

	this.oDiv.style.top=y+"px";
	this.oDiv.style.left=x+"px";
	}

 autoComplete.prototype.onTextChange=function(bTextComplete) {
	var txt=this.oText.value;
	var oThis=this;
	this.cur=-1;

	if(txt.length>0)	{
	   while(this.oDiv.hasChildNodes())
		  this.oDiv.removeChild(this.oDiv.firstChild);

	   var aStr=new Array();
	   this.db.getMatches(txt,aStr,this.maxSize);
	   if(!aStr.length) {this.hideSuggest ;return	}
	   if(bTextComplete) this.textComplete(aStr[0]);
	   this.positionSuggest();

	   for(i in aStr)	   {
		  var oNew=document.createElement('div');
		  this.oDiv.appendChild(oNew);
		  oNew.onmouseover=
		  oNew.onmouseout=
		  oNew.onmousedown=function(oEvent)   {
			 oEvent=window.event || oEvent;
			 oSrcDiv=oEvent.target || oEvent.srcElement;

			 //debug :window.status=oEvent.type;
			 if(oEvent.type=="mousedown")	   {
				oThis.oText.value=this.innerHTML;
				}
			 else if(oEvent.type=="mouseover")  {
				this.className="over";
				}
			 else if(oEvent.type=="mouseout")   {
				this.className="";
				}
			 else  {
				this.oText.focus();
				}
		 	};
		  oNew.innerHTML=aStr[i];
	  	}

	   this.oDiv.style.visibility="visible";
		}
	else {
	   this.oDiv.innerHTML="";
	   this.oDiv.style.visibility="hidden";
		}
	};
