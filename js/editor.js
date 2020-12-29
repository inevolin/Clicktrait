

var bool_all_ok = false;
var alert_forgot_script = '<strong>Oh snap!</strong> Did you perhaps forget to add this javascript code ? Reload this page once you\'re ready.';

$(document).ready(function() {
	$('#alertMsg').html(alert_forgot_script);
	if (typeof(Wpb_Reps) !== 'undefined' && typeof(Wpb_Reps.vars) !== 'undefined') {    					
		Wpb_Reps = Wpb_Reps.vars;
		console.log(Wpb_Reps);
		$(document).ready(function() {  	

	    	$.each(Wpb_Reps.CTA, function(i, obj) {	    		
	  			addCTA(obj.id, obj.imageUrl);
	    	});
		    $.each(Wpb_Reps.VAR, function(i, obj) {
				obj.vars.reverse();
				$.each(obj.vars, function(j, v) {
					if (obj.group !=='original') { //skip original values
						addEditor(v.id, decodeURIComponent(v.val), decodeURIComponent(v.originalVal), obj.group, v.imageUrl);              
					}
				});     
		    });
		    fixDropdownGroups();      

		    if (!editing_mode) { //it's just preview mode
		 		$('#editors').show();	
			    $('#divAlert').hide();
			    $('#divLoading').hide();
		 	} else { //editing mode, so the script must be live, otherwise error.
		 		ifr_loading();
		  	}
	  	});      
	} else {
		ifr_loading();
	}
}); 


console.log('editing_mode: ' + editing_mode);//false = already running ; true = may edit

var all_ok = function() {	
	/*var parentHeight = $('#wrapper').parent().height()/3*2;       
    $('#ifr').parent().height(function(i,x) {
      return x + parentHeight;
    });*/
    $('#btnSave').show();
    $('#editors').show();
    $('#divAlert').hide();
    $('#divLoading').hide();
}

var ifr_loading = function() {
	$('#ifr').load(function() {
	  	setTimeout(function(){
		  	if (!bool_all_ok && editing_mode) {
		 		$('#divAlert').show();
		 		$('#divLoading').hide();
		 	}
	 	}, 1000);
  	});
}


var handleSizingResponse = function(e) {
	if (e.data.mytype === 'ab') {
        if(e.data.selector.length > 0 && e.data.html.length > 0) {
	        var scrollTo = addEditor(e.data.selector, e.data.html, e.data.html, null, null);	
	        fixDropdownGroups();        
	        $('html, body').animate({
		        scrollTop: $(scrollTo).offset().top - $('.topbar').height()*1.2
		    }, 500);
	    }
    } else if (e.data.mytype === 'cta') {
    	if ($('[id^="CTATextBoxDiv"]').length > 0) {
    		mycounter = $('[id^="CTATextBoxDiv"]').map(function() {
				var dis = $(this);
				var id = dis.children('#cta').val();
				if (id == e.data.selector) {
					return dis.children('div').length + 1;
				}
			})[0];
		}
		if (typeof mycounter === 'undefined') {mycounter = 1;}
		if (e.data.selector.length > 0) {
			var scrollTo = addCTA(e.data.selector, null);
			$('html, body').animate({
		        scrollTop: $(scrollTo).offset().top - $('.topbar').height()*1.2
		    }, 500);
		}
    } else if (e.data.mytype === 'test') {
    	//letting us know all OK; otherwise wpb-ab-min.js missing from target site.
    	bool_all_ok = true;
    	all_ok();
    }
};

window.addEventListener('message', handleSizingResponse, false);	


//editor function
var save = function (url, pid, cid) {
	if (!hasCTA()) {
		alert("No CTA defined, use Right-Click to define CTA(s)");
		return false;
	}

	Wpb_Reps = { 'VAR' : [], 'CTA' : [] }  ;

	var OK = true;	
	$('[id^="TextBoxDiv"]').each(function() {			
		var e = $(this);
		var varid = 		e.children('#ab').val();
		var originalVal = 	encodeURIComponent( e.children('[id^="originalHtml"]').val() );
		e.children('div').each(function() {	
			var val = 			encodeURIComponent ( $(this).children('[id^="textbox"]').val());
			var group = 		$(this).children('[id^="group"]').val();			
			var vars = {
							'id' : varid,
							'val' : val,
							'originalVal' : originalVal
						};
			var arr = {
				'type'		: 'data-ab-var',
				'group'	: group,
				'vars'		: []
			};				
			if (!group) {
				alert('Empty groups found!'); OK = false; return false;
			}
			var test1 = $.grep(Wpb_Reps["VAR"], function(e){ return e.group == group; });
			if( test1.length > 0 ) {
				var test2 = $.grep(test1[0]['vars'], function(e){ return e.id == varid; });
				if (test2.length > 0) {
					alert('Duplicate group name found!'); OK = false; return false;
				} else {
					test1[0]['vars'].push(vars);
				}
			} else {
				arr['vars'].push(vars);
				Wpb_Reps["VAR"].push(arr);
			}
		});	
		//add original value to "original" group
		console.log(Wpb_Reps);
		var arr = {
				'type'		: 'data-ab-var',
				'group'	: 'original',
				'vars'		: []
			};	
		var vars = {
				'id' : varid,
				'val' : originalVal,
				'originalVal' : originalVal
		};
		arr['vars'].push(vars);
		Wpb_Reps["VAR"].push(arr);
		console.log(Wpb_Reps);

		if (!OK) {return false;}
	});
	if (!OK) {return false;}
	


	$('[id^="CTATextBoxDiv"]').each(function() {		
		var e = $(this);
		var arr = {
			'id' 		: e.children('#cta').val(),
			'type'		: 'data-ab-cta'
		};
		Wpb_Reps["CTA"].push(arr);
	});
	
	$.ajax( {
		url: window.location.href,
		type:"POST",
		dataType:"json",
		data : { 'p' : pid, 'c' : cid, 'url' : url , 'vars' : Wpb_Reps },
		crossDomain: true,
		async: false,
		success:function(rdata, textStatus, jqXHR) {
			if (rdata == "success") {
				console.log("success");
				$.Notification.notify('success','bottom left','Campaign saved!', 'Your campaign has been saved and is ready to start.<br>Go back to the <a href="'+campaigns_page_url+'">campaigns page</a> to start this campaign.');
				return true;
			} else {
				console.log("failure");
				$.Notification.notify('error','bottom left','Saving error.', 'Something went wrong. Please <a href="http://forum.clicktrait.com/topic/15-clicktrait-support/" target="_blank">contact support</a>.');			
				return false;
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(errorThrown);	
			console.log("failure");
			$.Notification.notify('failure','bottom left','Saving error.', 'Something went wrong. Please <a href="http://forum.clicktrait.com/topic/15-clicktrait-support/" target="_blank">contact support</a>.');				
			return false;		
		}
	});
}


var countMAXgroups = function () {
	//we check if each AB test has same amount of
	var ids = [];
	$('[id^="TextBoxDiv"]').each(function() {			
		var e = $(this);
		e.children('div').each(function() {		
			var varid = 		e.children('#ab').val();
			ids.push(varid);
		});
	}); 

	var unq = {};
	ids.forEach(function(entry) {
		if (unq[entry] === undefined) { unq[entry] = 0;}
		unq[entry]++;
	});

	var MAX = 0;
	$.each(unq, function(key, value) {
		if ( value > MAX ) { MAX = value; }	
	});
	return MAX;
}


var hasCTA = function () {
	var id;
	$('[id^="CTATextBoxDiv"]').each(function() {		
		var e = $(this);
		if ( e.children('#cta').length > 0) {id = true;}
	});
	return id;
}


function htmlEncode(value){
  //create a in-memory div, set it's inner text(which jQuery automatically encodes)
  //then grab the encoded contents back out.  The div never exists on the page.
  return $('<div/>').text(value).html();
}

var copyvar = function (e) {
	var rt = e.parent().parent();
	addEditor(
		rt.children('#ab').val(),
		htmlEncode(e.parent().children('[id=textbox]').val()),
		htmlEncode(e.parent().children('[id=originalHtml]').val()),
		null,
		null
	);
	fixDropdownGroups();
	return false;
}


var addEditor = function (xp, html, originalHtml, group, imageUrl) {	
	var returnVal = null;
	var found = 0;
	$('[id^="TextBoxDiv"]').each(function() {
		var dis = $(this);
		var id = dis.children('#ab').val();
		if (id == xp) {
			var aa =	'<div style="float:left;margin:0px 0px 30px;padding:10px;">'
      						+ '<textarea style="width:500px;height:50px;" name="textbox" id="textbox" ' +(editing_mode ?  '' :'readonly disabled ')+ ' >' + html + '</textarea>'
      						+ '<br />group: <select id="group" name="group"  ' +(editing_mode ?  '' :'readonly disabled ')+ (group == null ? '/>' : '><option value="'+group+'" selected>'+group+'</option></select>')
      						+ (editing_mode ? '&nbsp;&nbsp;<a href="" onclick="return removevar($(this))" style="color:red;font-size:11px;">remove</a>' : '')
      						+ (editing_mode ? '&nbsp;<a href="#" onclick="copyvar($(this)); return false;" style="color:green;font-size:11px;">duplicate</a>' : '')
      						+ ( '&nbsp;<a href="'+(purl+'?ab-sg='+group+'&ab-sc='+cid+'&ab-srv=1')+'" id="preview_link" onclick="save(purl, pid, cid); return set_prev_url(this,purl,'+group+',cid);" style="color:blue;font-size:11px;" target="_blank">preview</a>' )

      					+ '</div>';
			$(aa).insertBefore( dis.children('div:first') );
			found = 1;
			returnVal = $(dis.children('div:first'));
		}
	});

	if (found == 0) {
		var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv').css('margin-top', '20px').css('background-color', '#FFF').attr("class", "card-box");
        var aa = 	 '<h2 style="font-weight:bold;">A/B test</h2><input style="display:none;" id="abgroup" name="abgroup" type="checkbox" CHECKED>'
        			+ '<label for="ab" style="display:none;width:10%">Element selector: </label><input style="display:none;width:85%;font-size:18px;" id="ab" name="ab" type="text" value="' + xp + '">'
        			+ '<label style="vertical-align:top; margin-left:20px;">Original value:</label><br><textarea id="originalHtml" name="originalHtml" style="width:50%; height:auto;margin-left:20px;" readonly disabled>' + originalHtml + '</textarea>'
        			+ '<br style="margin-bottom:10px;" /><hr>'
        			+ '<div style="float:left;margin-left:20px;padding:0px;">'
      					+ '<textarea style="width:500px; height:50px;" name="textbox" id="textbox" '+(editing_mode ?  '' :'readonly disabled ')+' >' + html + '</textarea>'
      					+ '<br />Group: <select id="group" name="group" '+(editing_mode ?  '' :'readonly disabled ')+ (group == null ? '/>' : '><option value="'+group+'" selected>'+group+'</option></select>')
      					+ (editing_mode ? '&nbsp;&nbsp;<a href="" onclick="return removevar($(this))" style="color:red;font-size:11px;">remove</a>' : '')
      					+ (editing_mode ? '&nbsp;<a href="#" onclick="copyvar($(this)); return false;" style="color:green;font-size:11px;">duplicate</a>' : '')
      					+ (  '&nbsp;<a href="'+(purl+'?ab-sg='+group+'&ab-sc='+cid+'&ab-srv=1')+'" id="preview_link" onclick="save(purl, pid, cid); return set_prev_url(this,purl,'+group+',cid);" style="color:blue;font-size:11px;" target="_blank">preview</a>' )
      					+ '<br />'
      				+ '</div>'
      				+ '<br style="clear:both;" />'	 ;     			
		newTextBoxDiv.before().html(aa);  
		newTextBoxDiv.prependTo("#editors");
		returnVal = $("#editors");
	}		

	return returnVal;
}

function set_prev_url(dis,purl,group,cid) {
	if (group == null) {
		group =  ($(dis).parent()).find('#group').val();
		$(dis).attr('href', (purl+'?ab-sg='+group+'&ab-sc='+cid+'&ab-srv=1'));
		console.log(group);
		if (group != null) {return true;} else {return false;}
	}
    return true;
}


var fixDropdownGroups = function () {
	var count = countMAXgroups();
	$("[id=group]").each(function() {
		var e = $(this);
		var selected = e.val();
		e.empty();
		for(var i = 1; i<= count; i++) {			
			e.append($('<option>', {
			    value: i,
			    text: i
			}));
		}
		e.val(selected);
	});
}

var addCTA = function (xp, imageUrl) {
	var returnVal = null;
	var found = 0;

	$('[id^="CTATextBoxDiv"]').each(function() {
		var dis = $(this);
		var id = dis.children('#cta').val();
		if (id == xp) {
			console.log("already active CTA");
			found = 1;
			returnVal = $(this);
		}
	});
	if (found == 0) {
		var newTextBoxDiv = $(document.createElement('div')).attr("id", 'CTATextBoxDiv').css('margin-top', '20px').css('background-color', '#FFF').attr("class", "card-box");
	    var aa = 	 '<h2 style="font-weight:bold;">CTA</h2><input style="display:none;" id="trackevent" name="trackevent" type="checkbox" style="margin-right:15px;" CHECKED>'
	    			+ '<label for="ab" style="display:none;width:10%">Element selector: </label><input style="display:none;width:85%;font-size:18px;" id="cta" name="cta" type="text" value="' + xp + '">'
	    			+ '<span style="margin-left:20px;"><strong>Element:</strong>   ' + xp + '</span>'
	    			+ (imageUrl == null ? '' : '<br /><br /><img style="margin-left:20px;" src="./screens/uploads/'+imageUrl+'"/>')
	    			+ '<div style="margin:0px 0px 0px;padding:10px;">'
	  					+ (editing_mode ? '<a href="" onclick="return removevar($(this))" style="color:red;font-size:11px;">remove</a>' : '')
	  				+ '</div>'
	  				+ '<br style="clear:both;" />'	 ;     			
		newTextBoxDiv.before().html(aa);  
		returnVal = newTextBoxDiv.prependTo("#editors");
	}
	return returnVal;
}


var removevar = function (e) {
	if ( e.parent().parent().children('div').length == 1) {
		e.parent().parent().remove();
	} else {
		e.parent().remove();
	}
	fixDropdownGroups();
	return false;
}



function ifr_plus(e) {
	var scrollOffset = window.scrollY;

	$('#ifr').height($('#ifr').height()+100);
	
	$('html, body').animate({
        scrollTop: scrollOffset
    }, "fast");
}

function ifr_minus(e) {
	var scrollOffset = window.scrollY;
	
	$('#ifr').height($('#ifr').height()-100 <= 10 ? $('#ifr').height() : $('#ifr').height()-100);
	
	$('html, body').animate({
        scrollTop: scrollOffset
    }, "fast");
}

