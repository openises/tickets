var arrIFrame = Array();
var intIFrame = 0;

void function setSpan(strIFrame,strInitializationFunction) {
	var objHidden = top.frmHidden;				//	Hidden frame
	var objIFrame;								//	IFrame in hidden frame
	var objSpan;								//	Span in current document
	var reWork = new RegExp('^' + strIFrame + '$','gi');
	var strTimeout = 'setSpan(\'' + strIFrame + '\',\'' + strInitializationFunction + '\')';
	var striFrameID = 'iframe' + strIFrame;
	var strSpanID = strIFrame.toString().toLowerCase();
	var strOpenTag;
	var strCloseTag;
	var intDelay = 50;							//	setTimeout delay in milliseconds
	var blnFound = false;						//	Object found indicator

	if(typeof(top.frmHidden) != 'object')
		objHidden = window.opener.top.frmHidden;

	if(document.readyState.toString() != 'complete')
		window.setTimeout(strTimeout,intDelay);	//	Wait for document completion
	else {
		objIFrame = inlineFrame(strIFrame);

		if(objIFrame == null) {					//	IFrame pending?
			for(var i=0;i < arrIFrame.length;i++)
				if(reWork.test(arrIFrame[i])) {
					blnFound = true;

					break;
				}

			if(!blnFound) {
				objHidden.document.write('<iframe id="' + striFrameID + '" name="' + striFrameID + '" src="IFrame/' + strSpanID + '.asp"></iframe>');

				arrIFrame.push(strSpanID);		//	Indicate iframe creation

				++intIFrame;					//	Increment iframe load count

				document.body.style.cursor = 'wait';

				window.setTimeout(strTimeout,intDelay);
			}
		} else									//	Test for iframe document completion
			if(objIFrame.document.readyState.toString() != 'complete')
				window.setTimeout(strTimeout,intDelay);
			else {								//	All documents complete
				document.body.style.cursor = 'wait';

				objSpan = spanObject(document,'span' + strIFrame);
				objSpan.innerHTML = objIFrame.document.body.innerHTML;

				if((strInitializationFunction != null) && (strInitializationFunction != 'undefined'))
					eval(strInitializationFunction);

				if(intIFrame != 0)
					--intIFrame;				//	Decrement iframe load count

				if(intIFrame == 0)
					document.body.style.cursor = 'auto';
			}
	}
}

function inlineFrame(strIFrame) {
	var objHidden = top.frmHidden;				//	Hidden frame
	var objIFrame = null;						//	IFrame in hidden frame
	var reWork = new RegExp('^iframe' + strIFrame + '$','gi');

												//	Qualify fully?
	if(typeof(top.frmHidden) != 'object')
		objHidden = window.opener.top.frmHidden;

	for(var i=0;i < objHidden.frames.length;i++)
		if(reWork.test(objHidden.frames[i].name)) {
			objIFrame = objHidden.frames[i];

			break;
		}

	return objIFrame;
}

function spanObject(objDocument,strName) {
	var objSpan = null;							//	Span object
	var objBody = objDocument.body.all;			//	Document body object
	var reWork = new RegExp('^' + strName + '$','gi');

	for(var i=0;i < objBody.length;i++)
		if(reWork.test(objBody.item(i).id)) {
			objSpan = objBody.item(i);

			break;
		}

	return objSpan;
}
