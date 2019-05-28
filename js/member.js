var geo_provider = 0;
var cell1 = 0;
var cell2 = 0;
var cell3 = 0
var cell4 = 0;
var cell5 = 0;
var cell6 = 0;
var cell7 = 0;
var cell8 = 0;
var cell9 = 0;
var cell10 = 0;
var theResponder = 0;
var divTag = false;
var listHeight;
var colwidth;
var listwidth;
var otherlistwidth;
var celwidth;
var icons=[];
icons[0] = 1;	// blue
icons[1] = 2;	// yellow
icons[2] =  3;	// red

var baseIcon = L.Icon.extend({options: {shadowUrl: './our_icons/shadow.png',
	iconSize: [20, 32],	shadowSize: [37, 34], iconAnchor: [10, 31],	shadowAnchor: [10, 32], popupAnchor: [0, -20]
	}
	});
var baseFacIcon = L.Icon.extend({options: {iconSize: [28, 28], iconAnchor: [14, 29], popupAnchor: [0, -20]
	}
	});
var baseSqIcon = L.Icon.extend({options: {iconSize: [20, 20], iconAnchor: [10, 21], popupAnchor: [0, -20]
	}
	});
var basecrossIcon = L.Icon.extend({options: {iconSize: [40, 40], iconAnchor: [20, 41], popupAnchor: [0, -41]
	}
	});


function isFloat(n){
    return n != "" && !isNaN(n) && Math.round(n) != n;
	}

String.prototype.rpad = function(padString, length) {
	var str = this;
	while (str.length < length)
		str = str + padString;
	return str;
	}
	
String.prototype.trunc = String.prototype.trunc ||
      function(n){
          return this.length>n ? this.substr(0,n-1)+'&hellip;' : this;
      };
	
function pad(width, string, padding) { 
	return (width <= string.length) ? string : pad(width, string + padding, padding)
	}
	
function do_view(the_id, the_url, the_function) {	
	if(starting) {return;}					// dbl-click catcher
	starting=true;
	var url = the_url + "?all_id=" + escape(the_id);	//
	newwindow_view=window.open(url, "View",  "titlebar, location=0, resizable=1, scrollbars, height=600,width=900,status=0,toolbar=0,menubar=0,location=0, left=100,top=100,screenX=100,screenY=100");
	if (isNull(newwindow_view)) {
		alert ("View " + the_function + " operation requires popups to be enabled -- please adjust your browser options.");
		return;
		}
	newwindow_view.focus();
	starting = false;
	}		// end function do mail_win()
	
function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
	if((quick) || (!markers[id]) || (internet == 0)) {
		document.view_form.id.value=id;
		document.view_form.view.value="true";
		document.view_form.submit();
		} else {
		get_popup(id);
		}
	return false;
	}
	
function myvclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_vehicle.php", "Vehicles");
		} else {
		go_there("member.php?e_vehicle=true&mem_id=" + id + "&all_id=" + id);
		}
	}
	
function mytpclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_training.php", "Training");
		} else {	
		go_there("member.php?e_training=true&mem_id=" + id + "&all_id=" + id);
		}
	}
	
function myevclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_event.php", "Training");
		} else {	
		go_there("member.php?e_event=true&mem_id=" + id + "&all_id=" + id);
		}
	}

function myeclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_equipment.php", "Equipment");
		} else {	
		go_there("member.php?e_equipment=true&mem_id=" + id + "&all_id=" + id);
		}
	}

function mycclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_capability.php", "Capabilities");
		} else {	
		go_there("member.php?e_capability=true&mem_id=" + id + "&all_id=" + id);
		}
	}

function myclclick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_clothing.php", "Clothing");
		} else {	
		go_there("member.php?e_clothing=true&mem_id=" + id + "&all_id=" + id);
		}
	}

function myficlick(id, mem_id) {
	if(window.theForm == "view") {
		do_view(id, "./forms/view_file.php", "Files");
		} else {	
		go_there("member.php?e_files=true&mem_id=" + mem_id + "&all_id=" + id);
		}
	}

function get_popup(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/member_popup.php?id=' + id + '&version=' + randomnumber+'&q='+window.sess_id;
	sendRequest (url,theCB, "");
	function theCB(req) {
		var thePopup = JSON.decode(req.responseText);
		setTimeout(function() {
			popupInfo = thePopup[0];
			map.panTo(markers[id].getLatLng());
			markers[id].bindPopup(popupInfo).openPopup();
			}, 200)
		}
	}

function addMarker(lat, lon, info, iconurl) {
	if((isFloat(lat)) && (isFloat(lon))) {
		if(marker) { map.removeLayer(marker); }
		icon = new baseIcon({iconUrl: iconurl});	
		marker = L.marker([lat, lon], {icon: icon});
		marker.addTo(map);
		marker.bindPopup(info).openPopup();
		}
	}	

function createMemMarker(lat, lon, info, color, stat, theid, sym, category, region, tip) {
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconStr = sym;
		var iconurl = "./our_icons/gen_icon.php?blank=" + escape(window.icons[color]) + "&text=" + iconStr;	
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, riseOnHover: true, riseOffset: 30000});
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			get_popup(theid);
			});	
		marker.id = color;
		marker.category = category;
		marker.region = region;		
		marker.stat = stat;
		markers[theid] = marker;
		markers[theid][lat] = lat;
		markers[theid][lon] = lon;
		var point = new L.LatLng(lat, lon);
		if(window.in_local_bool == "1") {
			var southWest = L.latLng(theBounds[3], theBounds[0]);
			var northEast = L.latLng(theBounds[1], theBounds[2]);
			var maxBounds = L.latLngBounds(southWest, northEast);
			if(maxBounds.contains(point)) {
				bounds.extend(point);
				}
			} else {
			bounds.extend(point);				
			}
		map.fitBounds(bounds);
		return marker;
		} else {
		return false;
		}
	}
	
function createdummyMemMarker(lat, lon, info, color, image_file, tip, theid){
	if((isFloat(lat)) && (isFloat(lon))) {
		var iconurl = image_file;	
		icon = new baseIcon({iconUrl: iconurl});	
		var marker = L.marker([lat, lon], {icon: icon, title: tip, riseOnHover: true, riseOffset: 30000}).bindPopup(info);
		marker.on('popupclose', function(e) {
			map.setView(mapCenter, mapZoom);
			});
		marker.on('click', function(e) {
			get_popup(theid);
			});	
		marker.id = color;
		markers[theid] = marker;
		markers[theid][lat] = lat;
		markers[theid][lon] = lon;
		return marker;
		} else {
		return false;
		}
	}

function set_memb_headers(id, header_text, the_bull) {
	if(id == "h0") {
		window.h0_text = header_text + the_bull;
		window.h1_text = window.textName;		
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;		
		} else if(id == "h1") {
		window.h0_text = window.textID;			
		window.h1_text = header_text + the_bull;
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h2") {
		window.h0_text = window.textID;			
		window.h1_text = window.textName;
		window.h2_text = header_text + the_bull;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h3") {
		window.h0_text = window.textID;			
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = header_text + the_bull;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h4") {
		window.h0_text = window.textID;		
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = header_text + the_bull;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h5") {
		window.h0_text = window.textID;		
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = header_text + the_bull;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h6") {
		window.h0_text = window.textID;		
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = header_text + the_bull;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h7") {
		window.h0_text = window.textID;		
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = header_text + the_bull;
		window.h8_text = window.textJoined;
		window.h9_text = window.textAsof;	
		} else if(id == "h8") {
		window.h0_text = window.textID;
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = header_text + the_bull;
		window.h9_text = window.textAsof;	
		} else if(id == "h9") {
		window.h0_text = window.textID;
		window.h1_text = window.textName;	
		window.h2_text = window.textSurname;
		window.h3_text = window.textTeamID;
		window.h4_text = window.textCity;
		window.h5_text = window.textType;
		window.h6_text = window.textStatus;
		window.h7_text = window.textContact;
		window.h8_text = window.textJoined;
		window.h9_text = header_text + the_bull;
		}
	}
	
function do_memb_sort(id, field, header_text) {
	window.changed_memb_sort = true;
	window.memb_last_display = 0;
	if(window.memb_field == field) {
		if(window.memb_direct == "ASC") {
			window.memb_direct = "DESC"; 
			var the_bull = "&#9660"; 
			window.memb_header = header_text;
			window.memb_field = field;
			set_memb_headers(id, header_text, the_bull);
			} else if(window.memb_direct == "DESC") { 
			window.memb_direct = "ASC"; 
			var the_bull = "&#9650"; 
			window.memb_header = header_text; 
			window.memb_field = field;
			set_memb_headers(id, header_text, the_bull);
			}
		} else {
		window.memb_header = header_text;
		$(id).innerHTML = memb_header;
		window.memb_field = field;
		window.memb_direct = "ASC";
		window.memb_id = id;
		var the_bull = "&#9650";
		set_memb_headers(id, header_text, the_bull);
		}
	load_memberlist(field, memb_direct);
	return true;
	}

function load_memberlist(sort, dir) {
	if(sort != window.memb_field) {
		window.memb_field = sort;
		}
	if(dir != window.memb_direct) {
		memb_direct = dir;
		}
	if($('the_list').innerHTML == "") {
		$('the_list').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}

	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/main_memberlist.php?sort='+memb_field+'&dir='+ memb_direct+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,memberlist_cb, "");		
	function memberlist_cb(req) {
//		alert(req.responseText);
		var i = 1;
		var member_number = 0;	
		var memb_arr = JSON.decode(req.responseText);
		if((memb_arr[0]) && (memb_arr[0][0] == 0)) {
			for(var key in markers) {
				if(markers[key]) {map.removeLayer(markers[key]);}
				}
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Members to view.........</marquee>";
			$('the_list').innerHTML = outputtext;
			window.latest_member = 0;
			} else {
			var outputtext = "<TABLE id='respondertable' class='fixedheadscrolling scrollable' style='width: " + window.inner_listwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH id='h0' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'teamno', '" + textID + "')\">" + window.h0_text + "</TH>";
			outputtext += "<TH id='h1' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'firstname', '" + textName + "')\">" + window.h1_text + "</TH>";
			outputtext += "<TH id='h2' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'surname', '" + textSurname + "')\">" + window.h2_text + "</TH>";
			outputtext += "<TH id='h3' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'teamno', '" + textTeamID + "')\">" + window.h3_text + "</TH>";
			outputtext += "<TH id='h4' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'city', '" + textCity + "')\">" + window.h4_text + "</TH>";
			outputtext += "<TH id='h5' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'membertype', '" + textType + "')\">" + window.h5_text + "</TH>";
			outputtext += "<TH id='h6' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\"  onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'status', '" + textStatus + "')\">" + window.h6_text + "</TH>";
			outputtext += "<TH id='h7' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'contact', '" + textContact + "')\">" + window.h7_text + "</TH>";
			outputtext += "<TH id='h8' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'joined', '" + textJoined + "')\">" + window.h8_text + "</TH>";
			outputtext += "<TH id='h9' class='plain_listheader text' onMouseOver=\"do_hover_listheader(this.id);\" onMouseOut=\"do_plain_listheader(this.id);\" onClick=\"do_memb_sort(this.id, 'updated', '" + textAsof + "')\">" + window.h9_text + "</TH>";
			outputtext += "<TH id='h10' class='plain_listheader text' >" + pad(5, " ", "\u00a0") + "</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in memb_arr) {
				if(key != 0) {
					var member_no = memb_arr[key][0];
					if(memb_arr[key][10] != "") {
						var theMailBut = pad(12, "<DIV style='text-align: center; display: inline;' TITLE = 'click to email member " + memb_arr[key][1] + " " + memb_arr[key][3] + "' onclick = 'do_mail_win(\"" + member_no + "\");'><CENTER><IMG SRC='mail.png' BORDER=0/></CENTER></DIV>", "\u00a0");
						} else {
						var theMailBut = pad(12, "", "\u00a0");
						}
					outputtext += "<TR id='" + memb_arr[key][20] + member_no +"' CLASS='" + colors[i%2] +"' style='width: " + window.leftcolwidth + "px;'>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + pad(6, htmlentities(memb_arr[key][20], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + pad(10, htmlentities(memb_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + pad(10, htmlentities(memb_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + pad(8, memb_arr[key][4], "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + pad(4, htmlentities(memb_arr[key][6], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left'> " + pad(20, memb_arr[key][13], "\u00a0") + " </TD>";
					outputtext += "<TD class='plain_list text text_left'> " + pad(20, memb_arr[key][15], "\u00a0") + " </TD>";
					outputtext += "<TD class='plain_list text text_left'>" + pad(15, theMailBut, "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + memb_arr[key][16] + "</TD>";
					outputtext += "<TD class='plain_list text text_left' onClick='myclick(" + member_no + ");'>" + memb_arr[key][18] + "</TD>";
					outputtext += "<TD>" + pad(3, " ", "\u00a0") + "</TD>";
					outputtext += "</TR>";
					infowindowtext = "";					
					if($('map_canvas')) {						
						if((isFloat(memb_arr[key][8])) && (isFloat(memb_arr[key][9])) && (memb_arr[key][8] != 999999) && (memb_arr[key][9] != 999999)) {
							var marker = createMemMarker(memb_arr[key][8], memb_arr[key][9], infowindowtext, 1, 0, member_no, memb_arr[key][20], memb_arr[key][20], 0, memb_arr[key][19]); // 7/28/10, 3/15/11, 12/23/13
							marker.addTo(map);
							} else {
							var marker = createdummyMemMarker(parseFloat(window.def_lat), parseFloat(window.def_lng), infowindowtext, 1, "./our_icons/question1.png", memb_arr[key][19], member_no);
							if(marker) {marker.addTo(map);}
							}
						}
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_list').innerHTML = "";
				$('the_list').innerHTML = outputtext;
				var resptbl = document.getElementById('respondertable');
				if(resptbl) {
					var headerRow = resptbl.rows[0];
					var tableRow = resptbl.rows[1];
					if(tableRow) {
						for (var i = 0; i < tableRow.cells.length; i++) {
							if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth + "px";}
							}
						} else {
						var cellwidthBase = window.inner_listwidth / 40;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						cell5 = cellwidthBase * 4;
						cell6 = cellwidthBase * 4;
						cell7 = cellwidthBase * 4;
						cell8 = cellwidthBase * 4;
						cell9 = cellwidthBase * 4;
						cell10 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						headerRow.cells[4].style.width = cell5 + "px";							
						headerRow.cells[5].style.width = cell6 + "px";						
						headerRow.cells[6].style.width = cell7 + "px";
						headerRow.cells[7].style.width = cell7 + "px";
						headerRow.cells[8].style.width = cell8 + "px";
						headerRow.cells[9].style.width = cell9 + "px";
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = resptbl.insertRow(1);
						theRow.style.height = "20px";
						for (var i = 0; i < tableRow.cells.length; i++) {
							var theCell = theRow.insertCell(i);
							theCell.innerHTML = " ";
							}
						}
					}
				},500);
			}
		}				// end function memberlist_cb()
	}				// end function load_memberlist()

function memberlist_setwidths() {
	var viewableRow = 1;
	var resptbl = document.getElementById('respondertable');
	var headerRow = resptbl.rows[0];
	for (i = 1; i < resptbl.rows.length; i++) {
		if(!isViewable(resptbl.rows[i])) {
			} else {
			viewableRow = i;
			break;
			}
		}
	if(i != resptbl.rows.length) {
		var tableRow = resptbl.rows[viewableRow];
		for (var i = 0; i < tableRow.cells.length; i++) {
			if(tableRow.cells[i] && headerRow.cells[i]) {headerRow.cells[i].style.width = tableRow.cells[i].clientWidth + "px";}
			}
		} else {
		var cellwidthBase = window.inner_listwidth / 40;
		cell1 = cellwidthBase * 4;
		cell2 = cellwidthBase * 4;
		cell3 = cellwidthBase * 4;
		cell4 = cellwidthBase * 4;
		cell5 = cellwidthBase * 4;
		cell6 = cellwidthBase * 4;
		cell7 = cellwidthBase * 4;
		cell8 = cellwidthBase * 4;
		cell9 = cellwidthBase * 4;
		headerRow.cells[0].style.width = cell1 + "px";
		headerRow.cells[1].style.width = cell2 + "px";
		headerRow.cells[2].style.width = cell3 + "px";
		headerRow.cells[3].style.width = cell4 + "px";
		headerRow.cells[4].style.width = cell5 + "px";
		headerRow.cells[5].style.width = cell6 + "px";
		headerRow.cells[6].style.width = cell7 + "px";
		headerRow.cells[7].style.width = cell7 + "px";
		headerRow.cells[8].style.width = cell8 + "px";
		}
	if(getHeaderHeight(headerRow) >= listheader_height) {
		var theRow = resptbl.insertRow(1);
		theRow.style.height = "20px";
		for (var i = 0; i < tableRow.cells.length; i++) {
			var theCell = theRow.insertCell(i);
			theCell.innerHTML = " ";
			}
		}
	}
	
function load_vehiclelist(id) {
	if($('the_vehlist').innerHTML == "") {
		$('the_vehlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_vehicle_list.php?member='+id+'&version='+randomnumber+'&q='+ sess_id;
	sendRequest (url,vehiclelist_cb, "");		
	function vehiclelist_cb(req) {
		var i = 1;
		var veh_arr = JSON.decode(req.responseText);
		if((veh_arr[0]) && (veh_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Vehicles to show.........</marquee>";
			$('the_vehlist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='vehtbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Make</TH>";
			outputtext += "<TH class='plain_listheader text'>Model</TH>";
			outputtext += "<TH class='plain_listheader text'>Year</TH>";
			outputtext += "<TH class='plain_listheader text'>Colour</TH>";
			outputtext += "<TH class='plain_listheader text'>Reg Number</TH>";
			outputtext += "<TH class='plain_listheader text'>Type</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in veh_arr) {
				if(key != 0) {
					var veh_no = veh_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'><b>" + pad(8, htmlentities(veh_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'>" + pad(8, htmlentities(veh_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'>" + pad(8, htmlentities(veh_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'>" + pad(8, htmlentities(veh_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'>" + pad(8, htmlentities(veh_arr[key][5], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myvclick(" + veh_no + ", " + id + ");'>" + pad(8, htmlentities(veh_arr[key][6], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_vehlist').innerHTML = "";
				$('the_vehlist').innerHTML = outputtext;
				var vehtbl = document.getElementById('vehtbl');
				if(vehtbl) {
					var headerRow = vehtbl.rows[0];
					var tableRow = vehtbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						if(tableRow.cells[4] && headerRow.cells[4]) {headerRow.cells[4].style.width = tableRow.cells[4].clientWidth - 1 + "px";}
						if(tableRow.cells[5] && headerRow.cells[5]) {headerRow.cells[5].style.width = tableRow.cells[5].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 24;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						cell5 = cellwidthBase * 4;
						cell6 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";
						headerRow.cells[4].style.width = cell5 + "px";		
						headerRow.cells[5].style.width = cell6 + "px";								
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = vehtbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						var no5 = theRow.insertCell(4);
						var no6 = theRow.insertCell(5);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						no5.innerHTML = " ";
						no6.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function vehiclelist_cb()
	}				// end function load_vehiclelist()
	
function load_traininglist(id) {
	if($('the_traininglist').innerHTML == "") {
		$('the_traininglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_training_list.php?member='+id+'&version='+randomnumber+'&q='+ sess_id;
	sendRequest (url,traininglist_cb, "");		
	function traininglist_cb(req) {
		var i = 1;
		var tra_arr = JSON.decode(req.responseText);
		if((tra_arr[0]) && (tra_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Training to show.........</marquee>";
			$('the_traininglist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='tratbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Completed</TH>";
			outputtext += "<TH class='plain_listheader text'>Due</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in tra_arr) {
				if(key != 0) {
					var tra_no = tra_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='mytpclick(" + tra_no + ", " + id + ");'><b>" + pad(8, htmlentities(tra_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='mytpclick(" + tra_no + ", " + id + ");'>" + pad(8, htmlentities(tra_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mytpclick(" + tra_no + ", " + id + ");'>" + pad(8, htmlentities(tra_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mytpclick(" + tra_no + ", " + id + ");'>" + pad(8, htmlentities(tra_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_traininglist').innerHTML = "";
				$('the_traininglist').innerHTML = outputtext;
				var tratbl = document.getElementById('tratbl');
				if(tratbl) {
					var headerRow = tratbl.rows[0];
					var tableRow = tratbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 16;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = tratbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function traininglist_cb()
	}				// end function load_traininglist()
	
function load_eventlist(id) {
	if($('the_eventlist').innerHTML == "") {
		$('the_eventlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_event_list.php?member='+id+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,eventlist_cb, "");		
	function eventlist_cb(req) {
		var i = 1;
		var eve_arr = JSON.decode(req.responseText);
		if((eve_arr[0]) && (eve_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Events to show.........</marquee>";
			$('the_eventlist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='eventbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Start Date</TH>";
			outputtext += "<TH class='plain_listheader text'>End Date</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in eve_arr) {
				if(key != 0) {
					var eve_no = eve_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='myevclick(" + eve_no + ", " + id + ");'><b>" + pad(8, htmlentities(eve_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='myevclick(" + eve_no + ", " + id + ");'>" + pad(8, htmlentities(eve_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myevclick(" + eve_no + ", " + id + ");'>" + pad(8, htmlentities(eve_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myevclick(" + eve_no + ", " + id + ");'>" + pad(8, htmlentities(eve_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_eventlist').innerHTML = "";
				$('the_eventlist').innerHTML = outputtext;
				var eventbl = document.getElementById('eventbl');
				if(eventbl) {
					var headerRow = eventbl.rows[0];
					var tableRow = eventbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 16;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = eventbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function eventlist_cb()
	}				// end function load_eventlist()
	
function load_equipmentlist(id) {
	if($('the_equipmentlist').innerHTML == "") {
		$('the_equipmentlist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_equipment_list.php?member='+id+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,equipmentlist_cb, "");		
	function equipmentlist_cb(req) {
		var i = 1;
		var equ_arr = JSON.decode(req.responseText);
		if((equ_arr[0]) && (equ_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Equipment to show.........</marquee>";
			$('the_equipmentlist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='equiptbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Condition</TH>";
			outputtext += "<TH class='plain_listheader text'>Issued</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in equ_arr) {
				if(key != 0) {
					var equip_no = equ_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='myeclick(" + equip_no + ", " + id + ");'><b>" + pad(8, htmlentities(equ_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='myeclick(" + equip_no + ", " + id + ");'>" + pad(8, htmlentities(equ_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myeclick(" + equip_no + ", " + id + ");'>" + pad(8, htmlentities(equ_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myeclick(" + equip_no + ", " + id + ");'>" + pad(8, htmlentities(equ_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_equipmentlist').innerHTML = "";
				$('the_equipmentlist').innerHTML = outputtext;
				var equiptbl = document.getElementById('equiptbl');
				if(equiptbl) {
					var headerRow = equiptbl.rows[0];
					var tableRow = equiptbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 16;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";						
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = equiptbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function equipmentlist_cb()
	}				// end function load_equipmentlist()
	
function load_capabilitieslist(id) {
	if($('the_capabilitieslist').innerHTML == "") {
		$('the_capabilitieslist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_capabilities_list.php?member='+id+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,capabilitieslist_cb, "");		
	function capabilitieslist_cb(req) {
		var i = 1;
		var capab_arr = JSON.decode(req.responseText);
		if((capab_arr[0]) && (capab_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Capabilities to show.........</marquee>";
			$('the_capabilitieslist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='capabtbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Updated</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in capab_arr) {
				if(key != 0) {
					var capab_no = capab_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='mycclick(" + capab_no + ", " + id + ");'><b>" + pad(8, htmlentities(capab_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='mycclick(" + capab_no + ", " + id + ");'>" + pad(8, htmlentities(capab_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='mycclick(" + capab_no + ", " + id + ");'>" + pad(8, htmlentities(capab_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_capabilitieslist').innerHTML = "";
				$('the_capabilitieslist').innerHTML = outputtext;
				var capabtbl = document.getElementById('capabtbl');
				if(capabtbl) {
					var headerRow = capabtbl.rows[0];
					var tableRow = capabtbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 12;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = capabtbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function capabilitieslist_cb()
	}				// end function load_capabilitieslist()
	
function load_clothinglist(id) {
	if($('the_clothinglist').innerHTML == "") {
		$('the_clothinglist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_clothing_list.php?member='+id+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,clothinglist_cb, "");		
	function clothinglist_cb(req) {
		var i = 1;
		var cloth_arr = JSON.decode(req.responseText);
		if((cloth_arr[0]) && (cloth_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Clothing to show.........</marquee>";
			$('the_clothinglist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='clothtbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Size</TH>";
			outputtext += "<TH class='plain_listheader text'>Issued</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in cloth_arr) {
				if(key != 0) {
					var cloth_no = cloth_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='myclclick(" + cloth_no + ", " + id + ");'><b>" + pad(8, htmlentities(cloth_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='myclclick(" + cloth_no + ", " + id + ");'>" + pad(8, htmlentities(cloth_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myclclick(" + cloth_no + ", " + id + ");'>" + pad(8, htmlentities(cloth_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myclclick(" + cloth_no + ", " + id + ");'>" + pad(8, htmlentities(cloth_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_clothinglist').innerHTML = "";
				$('the_clothinglist').innerHTML = outputtext;
				var clothtbl = document.getElementById('clothtbl');
				if(clothtbl) {
					var headerRow = clothtbl.rows[0];
					var tableRow = clothtbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 16;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = clothtbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function clothinglist_cb()
	}				// end function load_clothinglist()
	
function load_member_filelist(id) {
	if($('the_filelist').innerHTML == "") {
		$('the_filelist').innerHTML = "<CENTER><IMG src='./images/owmloading.gif'></CENTER>";
		}
	var randomnumber=Math.floor(Math.random()*99999999);
	var url = './ajax/m_file_list.php?member='+id+'&version='+randomnumber+'&q='+sess_id;
	sendRequest (url,filelist_cb, "");		
	function filelist_cb(req) {
		var i = 1;
		var file_arr = JSON.decode(req.responseText);
		if((file_arr[0]) && (file_arr[0][0] == 0)) {
			var outputtext = "<marquee direction='left' style='font-size: 1.5em; font-weight: bold;'>......No Files to show.........</marquee>";
			$('the_filelist').innerHTML = outputtext;
			} else {
			var outputtext = "<TABLE id='filetbl' class='fixedheadscrolling scrollable' style='width: " + window.rightcolwidth + "px;'>";
			outputtext += "<thead>";
			outputtext += "<TR style='width: 100%;'>";
			outputtext += "<TH class='plain_listheader text'>Name</TH>";
			outputtext += "<TH class='plain_listheader text'>Description</TH>";
			outputtext += "<TH class='plain_listheader text'>Size</TH>";
			outputtext += "<TH class='plain_listheader text'>Uploaded</TH>";
			outputtext += "</TR>";
			outputtext += "</thead>";
			outputtext += "<tbody>";
			for(var key in file_arr) {
				if(key != 0) {
					var file_no = file_arr[key][0];
					outputtext += "<TR CLASS='" + colors[i%2] +"' style='width: 100%;'>";
					outputtext += "<TD class='plain_list text' onClick='myficlick(" + file_no + ", " + id + ");'><b>" + pad(8, htmlentities(file_arr[key][1], 'ENT_QUOTES'), "\u00a0") + "</b></TD>";
					outputtext += "<TD class='plain_list text' onClick='myficlick(" + file_no + ", " + id + ");'>" + pad(8, htmlentities(file_arr[key][2], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myficlick(" + file_no + ", " + id + ");'>" + pad(8, htmlentities(file_arr[key][3], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "<TD class='plain_list text' onClick='myficlick(" + file_no + ", " + id + ");'>" + pad(8, htmlentities(file_arr[key][4], 'ENT_QUOTES'), "\u00a0") + "</TD>";
					outputtext += "</TR>";
					i++;
					}
				}
			outputtext += "</tbody>";
			outputtext += "</TABLE>";
			setTimeout(function() {
				$('the_filelist').innerHTML = "";
				$('the_filelist').innerHTML = outputtext;
				var filetbl = document.getElementById('filetbl');
				if(filetbl) {
					var headerRow = filetbl.rows[0];
					var tableRow = filetbl.rows[1];
					if(tableRow) {
						if(tableRow.cells[0] && headerRow.cells[0]) {headerRow.cells[0].style.width = tableRow.cells[0].clientWidth - 1 + "px";}
						if(tableRow.cells[1] && headerRow.cells[1]) {headerRow.cells[1].style.width = tableRow.cells[1].clientWidth - 1 + "px";}
						if(tableRow.cells[2] && headerRow.cells[2]) {headerRow.cells[2].style.width = tableRow.cells[2].clientWidth - 1 + "px";}
						if(tableRow.cells[3] && headerRow.cells[3]) {headerRow.cells[3].style.width = tableRow.cells[3].clientWidth - 1 + "px";}
						} else {
						var cellwidthBase = window.rightcolwidth / 16;
						cell1 = cellwidthBase * 4;
						cell2 = cellwidthBase * 4;
						cell3 = cellwidthBase * 4;
						cell4 = cellwidthBase * 4;
						headerRow.cells[0].style.width = cell1 + "px";
						headerRow.cells[1].style.width = cell2 + "px";
						headerRow.cells[2].style.width = cell3 + "px";
						headerRow.cells[3].style.width = cell4 + "px";
						}
					if(getHeaderHeight(headerRow) >= listheader_height) {
						var theRow = filetbl.insertRow(1);
						theRow.style.height = "20px";
						var no1 = theRow.insertCell(0);
						var no2 = theRow.insertCell(1);
						var no3 = theRow.insertCell(2);
						var no4 = theRow.insertCell(3);
						no1.innerHTML = " ";
						no2.innerHTML = " ";
						no3.innerHTML = " ";
						no4.innerHTML = " ";
						}
					}
				},500);
			}
		}				// end function filelist_cb()
	}				// end function load_filelist()
	
function newGetAddress(latlng, currform) {
	control.options.geocoder.reverse(latlng, 20, function(results) {
		if(window.geo_provider == 0){
			if(results) {var r1 = results[0]; var r = r1['properties']['address'];} else {var r = {city: '', suburb: '', locality: '', house_number: '', road: '', state: '', properState: '', country: ''} }
			} else if(window.geo_provider == 1) {
			if(results) {var r = results[0];} else {var r = {city: '', suburb: '', locality: '', house_number: '', road: '', state: '', properState: '', country: ''} }
			} else if(window.geo_provider == 2) {
			if(results) {var r1 = results[0]; var r = {city: r1.city, house_number: "", road: r1.street, properState: r1.state};} else {var r = {city: '', suburb: '', locality: '', house_number: '', road: '', state: '', properState: '', country: ''} }
			}
		var lat = parseFloat(latlng.lat.toFixed(6));
		var lng = parseFloat(latlng.lng.toFixed(6));
		var theCity = "";
		if(!r.city) {
			if(r.suburb && (r.suburb != "")) {
				theCity = r.suburb;
				} else if(r.locality && (r.locality != "")) {
				theCity = r.locality;
				} else {
				theCity = "";
				}
			} else {
			theCity = r.city;
			}
		if(!r.state) {
			if(r.county) {
				var state = r.county;
				} else {
				var state = "";
				}
			} else {
			var state = r.state;
			}
		if (r) {
			switch(currform) {
				case "a":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_add_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.res_add_Form.frm_state.value = theState;
					document.res_add_Form.frm_lat.value = lat; 
					document.res_add_Form.frm_lng.value = lng; 
					document.res_add_Form.show_lat.value = lat; 
					document.res_add_Form.show_lng.value = lng;
					if(window.loc == 0) { document.res_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.res_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.res_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_add_Form.frm_street.focus();	
					break;

				case "e":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.res_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.res_edit_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.res_edit_Form.frm_state.value = theState;
					document.res_edit_Form.frm_lat.value = lat; 
					document.res_edit_Form.frm_lng.value = lng; 
					document.res_edit_Form.show_lat.value = lat; 
					document.res_edit_Form.show_lng.value = lng;
					if(window.loc == 0) { document.res_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.res_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.res_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.res_edit_Form.frm_street.focus();					
					break;
					
				case "wa":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_add_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_add_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.loc_add_Form.frm_state.value = theState;
					document.loc_add_Form.frm_lat.value = lat; 
					document.loc_add_Form.frm_lng.value = lng; 
					document.loc_add_Form.show_lat.value = lat; 
					document.loc_add_Form.show_lng.value = lng;
					if(window.loc == 0) { document.loc_add_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.loc_add_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.loc_add_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_add_Form.frm_street.focus();	
					break;

				case "we":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theStreet1 = (number != "") ? number + " " : "";
					var theStreet2 = (street != "") ? street : "";
					document.loc_edit_Form.frm_street.value = theStreet1 + theStreet2;
					document.loc_edit_Form.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.loc_edit_Form.frm_state.value = theState;
					document.loc_edit_Form.frm_lat.value = lat; 
					document.loc_edit_Form.frm_lng.value = lng; 
					document.loc_edit_Form.show_lat.value = lat; 
					document.loc_edit_Form.show_lng.value = lng;
					if(window.loc == 0) { document.loc_edit_Form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.loc_edit_Form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.loc_edit_Form.frm_ngs.value=LLtoUTM(lat, lng, 5); }	
					document.loc_edit_Form.frm_street.focus();					
					break;
					
				case "c":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theState = (state != "") ? states_arr[state] : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (r.state != "") ? r.state : "";					
					document.c.frm_address.value = address1 + address2 + address3 + address4;
					document.c.frm_lat.value = lat; 
					document.c.frm_lng.value = lng; 
					document.c.frm_address.focus();
					break;
					
				case "u":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var theState = (state != "") ? states_arr[state] : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street + ", " : "";
					var address3 = (theCity != "") ? theCity + ", " : "";
					var address4 = (r.state != "") ? r.state : "";					
					document.u.frm_address.value = address1 + address2 + address3 + address4;
					document.u.frm_lat.value = lat; 
					document.u.frm_lng.value = lng; 
					document.u.frm_address.focus();
					break;
					
				case "ni":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street : "";
					document.add.frm_street.value = address1 + address2;
					document.add.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.add.frm_state.value = theState;
					document.add.frm_lat.value = lat; 
					document.add.frm_lng.value = lng; 
					document.add.show_lat.value = lat; 
					document.add.show_lng.value = lng; 
					document.add.frm_street.focus();
					if(window.loc == 0) { document.add.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.add.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.add.frm_ngs.value=LLtoUTM(lat, lng, 5); }
					break;
					
				case "ei":
					var street = (r.road) ? r.road : "";
					var number = (r.house_number) ? r.house_number : "";
					var address1 = (number != "") ? number + " " : "";
					var address2 = (street != "") ? street : "";
					document.edit.frm_street.value = address1 + address2;
					document.edit.frm_city.value = theCity;
					var theState = (state != "") ? states_arr[state] : "";
					if(r.properState) { theState = r.properState;}
					document.edit.frm_state.value = theState;
					document.edit.frm_lat.value = lat; 
					document.edit.frm_lng.value = lng; 
					document.edit.show_lat.value = lat; 
					document.edit.show_lng.value = lng; 
					document.edit.frm_street.focus();
					if(window.loc == 0) { document.edit.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
					if(window.loc == 1) { document.edit.frm_osgb.value=LLtoOSGB(lat, lng, 5); }
					if(window.loc == 2) { document.edit.frm_utm.value=LLtoUTM(lat, lng, 5); }							
					break;
					
				default:
					alert ("596: error");
				}		// end switch()
			var theContent1 = (number != "") ? number + " ": "";
			var theContent2 = (street != "") ? street + ", ": "";
			var theContent3 = (theCity != "") ? theCity + ", ": "";
			var theContent4 = (theState != "") ? theState + ", ": "";
			var theContent = theContent1 + theContent2 + theContent3 + theContent4;
			popup
				.setLatLng(latlng)
				.setContent(theContent)
				.openOn(map);
			}
		});
	}
	

function bytesToSize(bytes, precision) {   
	var kilobyte = 1024;
	var megabyte = kilobyte * 1024;
	var gigabyte = megabyte * 1024;
	var terabyte = gigabyte * 1024;
	if ((bytes >= 0) && (bytes < kilobyte)) {
		return bytes + ' B';
		} else if ((bytes >= kilobyte) && (bytes < megabyte)) {
		return (bytes / kilobyte).toFixed(precision) + ' KB';
		} else if ((bytes >= megabyte) && (bytes < gigabyte)) {
		return (bytes / megabyte).toFixed(precision) + ' MB';
		} else if ((bytes >= gigabyte) && (bytes < terabyte)) {
		return (bytes / gigabyte).toFixed(precision) + ' GB';
		} else if (bytes >= terabyte) {
		return (bytes / terabyte).toFixed(precision) + ' TB';
		} else {
		return bytes + ' B';
		}
	}


function checkFile(inputFile, uploadType) {
	if (inputFile.files) {
		if(inputFile.files[0].size > window.maxfile) {
			alert("Your file is " + bytesToSize(inputFile.files[0].size, 2) + " the maximum size is " + bytesToSize(window.maxfile, 2) + "\nPlease chose a smaller file.");
			inputFile.value = null; // Clear the field.
			return;
			}
		var testArr = inputFile.files[0].name.split('.');
		var theExtension = testArr.slice(-1).pop();
		theExtension = theExtension.toLowerCase();
		pictureTypesArray = ["bmp","png","jpg"];
		fileTypesArray = ["bmp","jpg","pdf","doc","docx","xls","xlsx","rtf","odf"];
		if(uploadType == "picture") {
			if(pictureTypesArray.indexOf(theExtension) == -1) {
				alert("Invalid File Type.\nAllowed file types are .bmp, .png and .jpg");
				inputFile.value = null; // Clear the field.
				}
			} else {
			if(fileTypesArray.indexOf(theExtension) == -1) {
				alert("Invalid File Type.\nAllowed file types are .pdf, .doc, .docx, .rtf, .xls and .xlsx");
				inputFile.value = null; // Clear the field.
				}				
			}
		}
	}
	
function hide_unit_contact_info() {
	// dummy
	}
	
function show_member_contact_info() {
	if($('members_row')) {$('members_row').style.display='';}
	if($('members_info_row')) {$('members_info_row').style.display='';}
	}
	
function get_member_contact_details(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/get_member_contactdetails.php?id=' + id + '&version=' + randomnumber;
	sendRequest (url, member_cb, "");
	function member_cb(req) {
		var member_details=JSON.decode(req.responseText);
		var outputstring = "<TABLE>";
		for(var key in member_details) {
			outputstring += "<TR>";
			outputstring += "<TD class='td_label text'>" + key + "</TD><TD class='td_data_text'>" + member_details[key] + "</TD></TR>";
			}
		outputstring += "</TABLE>";
		$('member_info_div').innerHTML = outputstring;
		get_member_full_details(id);
		}
	}
	
function get_member_full_details(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/get_member_fulldetails.php?id=' + id + '&version=' + randomnumber;
	sendRequest (url, member_cb, "");
	function member_cb(req) {
		var member_details=JSON.decode(req.responseText);
		var outputstring = "<TABLE>";
		var theClass = 'even'; 
		for(var key in member_details) {
			outputstring += "<TR class=" + theClass + ">";
			outputstring += "<TD class='td_label text'>" + key + "</TD><TD class='td_data_text'>" + member_details[key] + "</TD></TR>";
			theClass = (theClass == 'even') ? 'odd' : 'even';
			}
		outputstring += "</TABLE>";
		$('memberdetails').innerHTML = outputstring;
		$('memberview').style.display = 'inline';
		}
	}
	
function get_contact_via(id) {
	var randomnumber=Math.floor(Math.random()*99999999);
	var url ='./ajax/get_contact_via.php?id=' + id + '&version=' + randomnumber;
	sendRequest (url, contact_cb, "");
	function contact_cb(req) {
		var contact_via=JSON.decode(req.responseText);
		var outputstring = "<TABLE>";
		var theClass = 'even'; 
		for(var key in member_details) {
			outputstring += "<TR class=" + theClass + ">";
			outputstring += "<TD class='td_label text'>" + key + "</TD><TD class='td_data_text'>" + member_details[key] + "</TD></TR>";
			theClass = (theClass == 'even') ? 'odd' : 'even';
			}
		outputstring += "</TABLE>";
		$('memberdetails').innerHTML = outputstring;
		$('memberview').style.display = 'inline';
		}
	}
