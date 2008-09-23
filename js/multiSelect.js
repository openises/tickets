/**
* multiSelect.js
* 
* Converts multiple select form inputs into a DHTML
* version which uses checkboxes instead. This is more
* usable, due to people not knowing how to use a multiple
* select.
* 
* The custom selects should end up the same width and height
* more or less as your existing select, so control that with
* css width and height, or the HTML "size" attribute.
* 
* Coded by Kae - kae@verens.com
* I'd appreciate any feedback.
* You have the right to include this in your sites.
* Please retain this notice.
* 
* Refactored into nice, pretty code by Richard Heyes (http://www.phpguru.org)
*/


var isMsie = document.all ? true : false;


/**
* Adds buildMultipleSelects() to the onload event.
*/
function addEvent(element, event, func) {
	if (isMsie) {
		element.attachEvent(event, func);
	
		} else if (element.addEventListener) {
		element.addEventListener(event, func, false);
		}
	}


/**
* Handles the conversion of multiple selects
*/
function buildMultipleSelects() {
	var selectObjects = document.getElementsByTagName('select');

	if (selectObjects) {
		while (selectObjects.length) {

			if (!selectObjects[0].multiple) {
				continue;
				}

			var ms = selectObjects[0];
			
			var disabled = ms.disabled ? true : false;
			var width	= ms.offsetWidth;
			var height   = ms.offsetHeight;
			
			var divElement			= document.createElement('div');
			divElement.style.overflow = 'auto';
			divElement.style.width	= width + "px";
			divElement.style.height   = height + "px";
			divElement.style.border   = "2px inset white";
			divElement.style.font = "10pt Arial";
			divElement.className	  = 'customMultipleSelect';
			
			optionObjects = ms.getElementsByTagName('option');

			for (var j=0; j<optionObjects.length; ++j) {
				var spanElement = document.createElement('div');

				spanElement.style.paddingLeft = "20px";
				spanElement.style.cursor = "default";
				spanElement.className = 'customMultipleSelect_option';
				
				addEvent(spanElement, 'onmousedown', function () {if (isMsie && event.srcElement.tagName.toLowerCase() == 'div' && !event.srcElement.firstChild.disabled) {event.srcElement.childNodes[0].checked = !event.srcElement.childNodes[0].checked;	}	})
				
				var inputElement  = document.createElement('input');
				inputElement.type = "checkbox";
			
				if (optionObjects[j].selected) {
					inputElement.checked		= true;
					inputElement.defaultChecked = true;
					}

				if (disabled) {
					inputElement.disabled = true;
					}

				inputElement.value			= optionObjects[j].value;
				inputElement.style.marginLeft = "-16px";
				inputElement.style.marginTop  = "-2px";
				inputElement.name			 = ms.name;

				var textLabel = document.createTextNode(optionObjects[j].text);

				spanElement.appendChild(inputElement);
				spanElement.appendChild(textLabel);
				divElement.appendChild(spanElement);
				}

			ms.parentNode.insertBefore(divElement, ms);
			ms.parentNode.removeChild(ms);
			}
		}
	}


addEvent(window, isMsie ? 'onload' : 'load', buildMultipleSelects);