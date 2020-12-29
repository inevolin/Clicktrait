
//this is used for screenshot module; so it can wait until AJAX loaded Wpb_Reps.
var WPB_REPS_LOADED_ASYNC = false;

// Only do anything if jQuery isn't defined
if (typeof jQuery == 'undefined') {
  if (typeof $ == 'function') {
    // warning, global var
    thisPageUsingOtherJSLibrary = true;
  }

  function getScript(url, success) {
    var script = document.createElement('script');
    script.src = url;
    var head = document.getElementsByTagName('head')[0],
      done = false;
    // Attach handlers for all browsers
    script.onload = script.onreadystatechange = function() {
      if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
        done = true;
        // callback function provided as param
        success();
        script.onload = script.onreadystatechange = null;
        head.removeChild(script);
      };
    };
    head.appendChild(script);
  };
  getScript('//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js', function() {
    if (typeof jQuery == 'undefined') {
      // Super failsafe - still somehow failed...
    } else {
      // jQuery loaded! Make sure to use .noConflict just in case
      console.log("jquery loaded");
     POST_JQUERY_LOADED();
       
    }
  });
} else { // jQuery was already loaded
  POST_JQUERY_LOADED();
};





/*
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
///////////////////////////// OUR STUFF ////////////////////////////
*/
function POST_JQUERY_LOADED() {


	(function ($) {
		$.fn.waitUntilExists    = function (handler, shouldRunHandlerOnce, isChild) {
		    var found       = 'found';
		    var $this       = $(this.selector);
		    var $elements   = $this.not(function () { return $(this).data(found); }).each(handler).data(found, true);
		    if (!isChild) {
		        (window.waitUntilExists_Intervals = window.waitUntilExists_Intervals || {})[this.selector] =
		            window.setInterval(function () { $this.waitUntilExists(handler, shouldRunHandlerOnce, true); }, 500);
		    }
		    else if (shouldRunHandlerOnce && $elements.length) {
		        window.clearInterval(window.waitUntilExists_Intervals[this.selector]);
		    }
		    return $this;
		}
	}(jQuery));




	(function($) {

		///////////////////////////////////////
        // for heatmaps : start
        ///////////////////////////////////////
        var getSelector = function(element, verbose) {
        	var notEmpty = function(i, s) {
		        return (s != null ? s.length : void 0) > 0;
		    };
		    var nthChild = function(elem) {
	            var child, j, len, n, parent, ref, ref1;
	            if (((elem == null) || (elem.ownerDocument == null), elem === document || elem === document.body || elem === document.head)) {
	                return "";
	            }
	            if (parent = elem != null ? elem.parentNode : void 0) {
	                n = 0;
	                ref = parent.childNodes;
	                for (j = 0, len = ref.length; j < len; j++) {
	                    child = ref[j];
	                    if ((ref1 = child.nodeName) === "#text" || ref1 === "#comment") {
	                        continue;
	                    }
	                    n += 1;
	                    if (child === elem) {
	                        return ":nth-child(" + n + ")";
	                    }
	                }
	            }
	            return elem.nodeName.toLowerCase();
	        };	        
            var hasClass, hasId, hasParent, isBody, isElement, isRoot, parentSelector, s;
            hasId = notEmpty(0, element.id);
            hasClass = notEmpty(0, element.className);
            isElement = element.nodeType === 1;
            isRoot = element.parentNode === element.ownerDocument;
            isBody = element === document.body;
            hasParent = element.parentNode != null;
            switch (true) {
                case isRoot:
                    s = "";
                    break;
                case !isElement:
                    s = "";
                    break;
                case isBody:
                    s = "body";
                    break;
                case hasId:
                    s = "#" + element.id;
                    break;
                case hasClass:
                    s = "." + element.className.split(" ").join(".").replace(/\.$/, '');
                    break;
                default:
                    s = element.nodeName.toLowerCase();
            }
            if (hasId) {
                return s;
            }
            if (verbose) {
                s += nthChild(element);
            }
            if ((!isRoot) && isElement && hasParent) {
                parentSelector = getSelector(element.parentNode);
                if (parentSelector !== "") {
                    return   getSelector(element.parentNode, verbose).replace('..','.') + " > " + s ;
                }
            }
            return s.replace('..','.');
        };
        var minifySelector = function(selector, node) {
	        var bisect, candidate, chunk, chunks, i, j, left, len, ref, result, right;
	        selector = selector.replace(/^\s+/, '');
	        if (selector.length === 0) {
	            return selector;
	        }
	        if (node == null) {
	            node = document.querySelector(selector);
	        }
	        bisect = function(a, x) {
	            var i;
	            i = a.indexOf(x);
	            return [a.slice(0, i), a.slice(i + 1, a.length)];
	        };
	        chunks = selector.split(" > ");
	        for (i = j = 0, len = chunks.length; j < len; i = ++j) {
	            chunk = chunks[i];
	            ref = bisect(chunks, chunk), left = ref[0], right = ref[1];
	            candidate = left.join(" > ") + " " + right.join(" > ");
	            candidate = candidate.replace(/^\s+/, '');
	            if (candidate.length > 0) {
	                result = document.querySelectorAll(candidate);
	                if (result.length === 1 && result[0] === node) {
	                    return minifySelector(candidate, node);
	                }
	            }
	        }
	        return selector;
	    };
        ///////////////////////////////////////
        // for heatmaps : end
        ///////////////////////////////////////

		var UNIQUE_SESSION = null;
		var TRACK_ATTR_CTA = "data-ab-cta";
		var RepsCookieName = 'ab-cookies-reps';
		var UNIQUE_SESSION_COOKIE = 'ab-cookies-ses';
		var GROUP_COOKIE = 'ab-cookies-group';
		var CAMPAIGN_COOKIE = 'ab-cookies-campaign';
		var FAILURES = 0;
		var cookiecontainer = [];
		var EVENTfirst = 0;
		var WURL = window.location.pathname;
		var FURL = location.protocol + '//' + location.host + location.pathname;
		var GETS = GetGET();
		var Wpb_Reps = null;

		var WPB_SUBMIT_URL;
		var WPB_GETVARS_URL;
		 
		WPB_SUBMIT_URL = "https://clicktrait.com/ab/api/reply.php";
		WPB_GETVARS_URL = "https://clicktrait.com/ab/api/getvars.php";
		if (window.location.href.indexOf('127.0.1.1') > 0) {
			WPB_SUBMIT_URL = "http://127.0.1.1/ab/api/reply.php";
			WPB_GETVARS_URL = "http://127.0.1.1/ab/api/getvars.php";
		}

		//this will execute after all document.ready functions
		//$( window ).load(function() {
			//LoadReps();
		//});

		//this will execute when DOM is ready and before any graphics are shown.
		$( document ).ready(function() {

			if (inIframe() || ab_editor_mode()) {
				console.log("1 editor mode");
				return;
			}
			try {
				LoadReps();
				if (ab_heatmap_mode()) { //first let A/B variations load, then add heatmap data
					ab_heatmap_continue();
				}
			} catch(e) {
				stopSpinner();
			}		
		});		


		function inIframe () {
		    try {
		        return window.self !== window.top;
		    } catch (e) {
		        return true;
		    }
		}

		function GetGET()
		{
			var y = {};
			if (location.search != "")
			{
				var x = location.search.substr(1).split("&")
				for (var i=0; i<x.length; i++)
			    {
					var z = x[i].split("=");
					y[z[0]] = z[1];
		        }
			}
			
			return y;

		}

		function LoadReps() {

			if ( Wpb_Reps == null ) {
				if($('[data-ab-p="loader"]').length > 0) {		addSpinner();	}
				
				var specGroup = getSpecificGroup_url();
				if (specGroup == null) {
					specGroup = findGroupFromCookie();
				}
				var specCampaign = getSpecificCampaign_url();
				if (specCampaign == null) {
					specCampaign = findCampaignFromCookie();
				}
				
				$.ajax( {
					url: WPB_GETVARS_URL,
					type:"GET",
					dataType:"jsonp",
					crossDomain: true,
					async: true,
					timeout: 5000,
					data: { 
						"sg":  specGroup,
						"sc":  specCampaign,
						"srv": isServerRequest() ? 1 : null,
						"ses" : UNIQUE_SESSION
					},
					success:function(rdata, textStatus, jqXHR) {
						Wpb_Reps = rdata[0];						
						if (Wpb_Reps.length == 0) {return;}
						try {
							findAndSetGroupCookie();
							findAndSetCampaignCookie();
							Continue();
						} catch (e) {}
						stopSpinner();
						WPB_REPS_LOADED_ASYNC = true;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("3 oh :(");
						stopSpinner();
					}
				});
			} else {
				stopSpinner();
			}
		}

		function findAndSetGroupCookie() {
			if (Wpb_Reps == null) {return;}		
			for (var o in Wpb_Reps) { //if group gets deleted/renamed, cookie needs to change !
				if (Wpb_Reps[o].vars instanceof Object) {
					console.log("4.1 setting group cookie " + Wpb_Reps[o].group);
					setCookie(GROUP_COOKIE,JSON.stringify(Wpb_Reps[o].group), 60); //get first occurance.
					return;
				}
			}
		}
		function findAndSetCampaignCookie() {
			if (Wpb_Reps == null) {return;}		
			for (var o in Wpb_Reps) { //if campaign gets deleted/renamed, cookie needs to change !
				if (Wpb_Reps[o].vars instanceof Object) {
					console.log("4.2 setting campaign cookie " + Wpb_Reps[o].campaign_id);
					setCookie(CAMPAIGN_COOKIE,JSON.stringify(Wpb_Reps[o].campaign_id), 60); //get first occurance.
					return;
				}
			}
		}

		function RemoveParameterFromUrl(url, parameter) {
		  return url
		    .replace(new RegExp('[?&]' + parameter + '=[^&#]*(#.*)?$'), '$1')
		    .replace(new RegExp('([?&])' + parameter + '=[^&]*&'), '$1');
		}

		function getSpecificGroup_url() {// ?ab-sg=A
			if (Object.keys(GETS).length > 0) {
			    for (var key in GETS) {	  
					if (key.indexOf('ab-sg') >= 0) {
						console.log("5.1 specific group: " + GETS[key]);
						return GETS[key];
					}
			    }
			}
			return null;
	 	}	
		function getSpecificCampaign_url() {// ?ab-sc=8
			if (Object.keys(GETS).length > 0) {
			    for (var key in GETS) {	  
					if (key.indexOf('ab-sc') >= 0) {
						console.log("5.2 specific campaign: " + GETS[key]);
						return GETS[key];
					}
			    }
			}
			return null;
	 	}	
		function isServerRequest() {// ?ab-srv=1
			if (Object.keys(GETS).length > 0) {
			    for (var key in GETS) {	  
					if (key.indexOf('ab-srv') >= 0) {
						console.log("5.3 server request");
						return true;
					}
			    }
			}
			return false;
	 	}	

		function findGroupFromCookie() {
			var tmp_cc;
			try {
				tmp_cc = $.parseJSON(getCookie(GROUP_COOKIE));
			}
			catch (e) {}
			if (tmp_cc != undefined) {
				return tmp_cc;
			}
			return null;
		}
		function findCampaignFromCookie() {
			var tmp_cc;
			try {
				tmp_cc = $.parseJSON(getCookie(CAMPAIGN_COOKIE));
			}
			catch (e) {}
			if (tmp_cc != undefined) {
				return tmp_cc;
			}
			return null;
		}

		function Continue() {

			UNIQUE_SESSION = GenerateUserSession();
			EVENTfirst = $.now();
			Wpb_AB_load();

			//non-CTA clicks (for heatmaps)
			$('*:not(['+TRACK_ATTR_CTA+'])').on('click', function(e) {	
				e.preventDefault();				
				e.stopPropagation();					
				e.cancelBubble = true;
				CapturedEvent($(this), e);
				SubmitData();
				var dis = $(this);
			  	SubmitProceed(dis, e);
			});
			$( '['+TRACK_ATTR_CTA+']' ).mouseenter(function(e) {		
		  		CapturedEvent($(this), e);
			});
			$( '['+TRACK_ATTR_CTA+']' ).on('click', function(e) {		
				e.preventDefault();				
				e.stopPropagation();					
				e.cancelBubble = true;
			  	CapturedEvent($(this), e);
			  	SubmitData();
			  	var dis = $(this);
			  	SubmitProceed(dis, e);
			  
			});
		    $(window).bind('scrollstop', function(ev){
		    	
		        var time = curdate();
				$( '['+TRACK_ATTR_CTA+']' ).withinviewport().each(function() {
					var e = new Object();
					e.type = 'scroll_stop';
					CapturedEvent($(this), e, time);
				});
		    });				    
			
		}

		function curdate()
		{
			var now = new Date();
			var next = new Date(now);
			next.setDate(now.getDate());
			return next;
		}

		function getRandomInt(min, max) {
		    return Math.floor(Math.random() * (max - min + 1)) + min;
		}


		function Wpb_AB_load() {
			if (Wpb_Reps !== null) {

				//rotate vars first (CTA can be part of a VAR, otherwise purpose lost)
				$.each(Wpb_Reps, function(index, obj) {				
					if (obj.vars != undefined) {//VAR
						$.each(obj['vars'], function(index, vr) {								
							ReplaceAB(decodeURIComponent(vr.val), vr.id);
						});
					}			
				});

				//assign CTAs
				$.each(Wpb_Reps, function(index, obj) {
					if (obj.vars == undefined) { //CTA
						var selector = obj.id;
						var nxt = $( getElementByXpath(selector) );
						nxt.attr(TRACK_ATTR_CTA, selector);
					}
				});
				
				$( '['+TRACK_ATTR_CTA+']' ).waitUntilExists(function() {
			    	onload();
				});		
			} else {
				onload();
			}
		}

		function ReplaceAB(newValue, selector) {
			var ret_full = null;//we replace entire element without inserting previous innerHtml.
			var ret_tag = null; //we replace tag only and insert previous innerHtml into new html().
		 	ret_tag = ret_full = $( newValue );		
		 	$(selector).waitUntilExists(function() {	 		
				var nxt = getElementByXpath(selector);
				nxt = $( nxt );
				CopyEventHandlers(nxt, ret_full);
				if (nxt.attr(TRACK_ATTR_CTA)) { //copy CTA attribute, in case CTA is also VAR.
					ret_full.attr(TRACK_ATTR_CTA, nxt.attr(TRACK_ATTR_CTA));
				}
				nxt.replaceWith( ret_full );	
			});		    	
		}

		function onload() {
			var e = new Object();
			e.type = "onload";
			CapturedEvent($('html'), e);
		}

		function CopyEventHandlers(ori, _new) {
		// replaceWith removes all EventHandlers of that element
		// (except if onclick is defined / 'live' method JQuery).
		// So we copy original element's eventHandlers to the new A/B element.
			try {
				$.each($._data(ori.get(0), 'events'), function() {
					$.each(this, function() {
						$(_new).bind(this.type, this.handler);
					});
				});
			}
			catch(exc) {}
		}

		function getElementByXpath(path) {
			var el = $(path);
			
			if ( el == null ) {
				var path2 = path.replace(FURL, '');
				el = $(path);
			} else {
				return el;
			}

			if ( el == null ) {
				var path2 = path.replace(FURL, '.');
				el = $(path);
			} else {
				return el;
			}

			if ( el == null ) {
				var path2 = path.replace(FURL, './');
				el = $(path);
			} else {
				return el;
			}

			if ( el == null ) {
				var path2 = path.replace(FURL, '/');
				el = $(path);
			} else {
				return el;
			}
			
			return null;

		}

		function setCookie(c_name,value,exdays)
		{
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value =
				escape(value) + ((exdays==null) ? "" : ("; expires="+exdate.toUTCString())) + ";";
			document.cookie = c_name + "=" + c_value;
		}

		function getCookie(c_name)
		{
			var i,x,y,ARRcookies=document.cookie.split(";");
			for (i=0;i<ARRcookies.length;i++)
			{
				x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
				y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
				x = x.trim();
				if (x==c_name)
				{
					return unescape(y);
				}
			}
		}

		function SubmitProceed(dis, e) {			
			setTimeout(function(){
			  	if (e.target.nodeName == "A") {  		
			  		if (dis.attr('href') !== "" && dis.attr('href')[0] !== '#') {
			  			document.location = dis.attr('href');
			  		}
			  	} else if (e.target.nodeName == "INPUT"){
			  		dis.parents('form:first').submit();
			  	} else {//if (e.target.nodeName == "IMG"){
			  		if (dis.parents('a:first').length > 0 && dis.parents('a:first').attr('href') !== "" && dis.parents('a:first').attr('href')[0] !== '#') {
			  			document.location = dis.parents('a:first').attr('href');
			  		}
			  	}
		  	}, 500);
		}

		function GenerateUserSession() {
			var _cookiecontainer = null;
			var cc = null;
			var uses = null;

			try {
				_cookiecontainer = $.parseJSON(getCookie(UNIQUE_SESSION_COOKIE)); //UNCOMMENT LATER!
			}
			catch (e) {}
			
			if (_cookiecontainer === null) {
				uses = getRandomInt(1,600000) + "" + curdate();		
				cc = { sesid : uses};
				setCookie(UNIQUE_SESSION_COOKIE,JSON.stringify(cc),180);
			}
			else {
				uses = _cookiecontainer.sesid
			}
			return uses;
			
		}

		function CapturedEvent(dis, e, time)
		{
			if (isServerRequest()) {return;} //do not submit any data if custom URL.
			var o = new Object();
				o.event = e.type;		
				o.EVENTid = $.now() - EVENTfirst;//time == undefined ? curdate() : time;
				o.element = dis.attr( TRACK_ATTR_CTA ) != undefined? dis.attr( TRACK_ATTR_CTA ) : "undefined"; //undefined is used for heatmaps; CTA clicks have their ID.
				o.nodeName = dis.prop("nodeName");

				//for heatmaps
				if (dis.selector != "html") {
					var t = dis[0];
					var selector = getSelector(t,false); //target , verbose
		            var count = $(selector).length;
		            if (count > 1) { selector = getSelector(t, true); }
		            selector = minifySelector(selector);
					o.selector = selector;	

					var pos = new Object();
					pos.offsetX = e.pageX - $(e.target).offset().left;//e.offsetX;
					pos.offsetY = e.pageY - $(e.target).offset().top;//e.offsetY;
					pos.el_width = $(e.target).width();
					pos.el_height = $(e.target).height();
					o.pos = pos;		
				} else {
					o.selector = 'html';
				}				
				SaveDataToSessionStorage(o);
		}
		function SaveDataToSessionStorage(data)
		{
		    var a = null;
		    a = JSON.parse(sessionStorage.getItem('wpb_arr')) || [];
			a.push(data);
			a = $.grep(a,function(n){ return(n) }); //remove null elements
			sessionStorage.setItem('wpb_arr', JSON.stringify(a));
		}
		function NewSession()
		{
			for (var obj in sessionStorage) {
		  		if (sessionStorage.hasOwnProperty(obj) && obj == "wpb_arr") {
		    		sessionStorage.removeItem(obj);
		  		}
		    }
		    var a = [];	    
			a.push(JSON.parse(sessionStorage.getItem('wpb_arr')));
			a = $.grep(a,function(n){ return(n) }); //remove null elements
			sessionStorage.setItem('wpb_arr', JSON.stringify(a));
		}	

		var tmr = window.setInterval(function() {
			SubmitData();
		}, 3000);

		function SubmitData()
		{	
			var test_new_events = JSON.parse(sessionStorage.getItem('wpb_arr'));
		    if (test_new_events!=null && test_new_events.length > 0)
			{
				var data = {
					data : JSON.parse(sessionStorage.getItem('wpb_arr'))
					, SESSION : UNIQUE_SESSION
					, group: findGroupFromCookie()
					, campaign: findCampaignFromCookie()
					, REFERRER : document.referrer.length > 0 ? document.referrer : 'null'
				};

				$.ajax( {
					url: WPB_SUBMIT_URL,
					type:"GET",
					dataType:"jsonp",
					data: data,
					crossDomain: true,
					async: true,
					success:function(rdata, textStatus, jqXHR) {
						console.log("6 success");
						FAILURES = 0;
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("7 failure");
						FAILURES++;
						if (FAILURES > 10) {
							clearInterval(tmr);
						}
						else if (FAILURES >= 5) {
							NewSession();	
						}
					}
				});
				/*
					We assume data gets delivered successfully.
					Otherwise it may re-transmit same data on new session,
					because timer ticks before success occurs.
				*/
				NewSession();
			}
		}



		function addSpinner() {
			$('head').append('<style type="text/css">\
					.no-js #loader { display: none;  }\
					.js #loader { display: block; position: absolute; left: 100px; top: 0; }\
					.se-pre-con {\
						position: fixed;\
						left: 0px;\
						top: 0px;\
						width: 100%;\
						height: 100%;\
						z-index: 9999;\
						background: #EEE;}</style>'
						);
			
		   $('body').prepend('<div class="se-pre-con" style="padding-left:48%;padding-top:100px;">loading...</div>');
		}
		function stopSpinner() {
			setTimeout(function(){
				$(".se-pre-con").fadeOut("slow");
			},300);

		}




			//////////////////////////////////////////////////
			/////////////////// HEATMAP STUFF
			//////////////////////////////////////////////////
			function ab_heatmap_continue() {
			    /*
				 * heatmap.js v2.0.2 | JavaScript Heatmap Library
				 *
				 * Copyright 2008-2016 Patrick Wied <heatmapjs@patrick-wied.at> - All rights reserved.
				 * Dual licensed under MIT and Beerware license 
				 *
				 * :: 2016-02-04 21:25
				 */
				(function(a,b,c){if(typeof module!=="undefined"&&module.exports){module.exports=c()}else if(typeof define==="function"&&define.amd){define(c)}else{b[a]=c()}})("h337",this,function(){var a={defaultRadius:40,defaultRenderer:"canvas2d",defaultGradient:{.25:"rgb(0,0,255)",.55:"rgb(0,255,0)",.85:"yellow",1:"rgb(255,0,0)"},defaultMaxOpacity:1,defaultMinOpacity:0,defaultBlur:.85,defaultXField:"x",defaultYField:"y",defaultValueField:"value",plugins:{}};var b=function h(){var b=function d(a){this._coordinator={};this._data=[];this._radi=[];this._min=0;this._max=1;this._xField=a["xField"]||a.defaultXField;this._yField=a["yField"]||a.defaultYField;this._valueField=a["valueField"]||a.defaultValueField;if(a["radius"]){this._cfgRadius=a["radius"]}};var c=a.defaultRadius;b.prototype={_organiseData:function(a,b){var d=a[this._xField];var e=a[this._yField];var f=this._radi;var g=this._data;var h=this._max;var i=this._min;var j=a[this._valueField]||1;var k=a.radius||this._cfgRadius||c;if(!g[d]){g[d]=[];f[d]=[]}if(!g[d][e]){g[d][e]=j;f[d][e]=k}else{g[d][e]+=j}if(g[d][e]>h){if(!b){this._max=g[d][e]}else{this.setDataMax(g[d][e])}return false}else{return{x:d,y:e,value:j,radius:k,min:i,max:h}}},_unOrganizeData:function(){var a=[];var b=this._data;var c=this._radi;for(var d in b){for(var e in b[d]){a.push({x:d,y:e,radius:c[d][e],value:b[d][e]})}}return{min:this._min,max:this._max,data:a}},_onExtremaChange:function(){this._coordinator.emit("extremachange",{min:this._min,max:this._max})},addData:function(){if(arguments[0].length>0){var a=arguments[0];var b=a.length;while(b--){this.addData.call(this,a[b])}}else{var c=this._organiseData(arguments[0],true);if(c){this._coordinator.emit("renderpartial",{min:this._min,max:this._max,data:[c]})}}return this},setData:function(a){var b=a.data;var c=b.length;this._data=[];this._radi=[];for(var d=0;d<c;d++){this._organiseData(b[d],false)}this._max=a.max;this._min=a.min||0;this._onExtremaChange();this._coordinator.emit("renderall",this._getInternalData());return this},removeData:function(){},setDataMax:function(a){this._max=a;this._onExtremaChange();this._coordinator.emit("renderall",this._getInternalData());return this},setDataMin:function(a){this._min=a;this._onExtremaChange();this._coordinator.emit("renderall",this._getInternalData());return this},setCoordinator:function(a){this._coordinator=a},_getInternalData:function(){return{max:this._max,min:this._min,data:this._data,radi:this._radi}},getData:function(){return this._unOrganizeData()}};return b}();var c=function i(){var a=function(a){var b=a.gradient||a.defaultGradient;var c=document.createElement("canvas");var d=c.getContext("2d");c.width=256;c.height=1;var e=d.createLinearGradient(0,0,256,1);for(var f in b){e.addColorStop(f,b[f])}d.fillStyle=e;d.fillRect(0,0,256,1);return d.getImageData(0,0,256,1).data};var b=function(a,b){var c=document.createElement("canvas");var d=c.getContext("2d");var e=a;var f=a;c.width=c.height=a*2;if(b==1){d.beginPath();d.arc(e,f,a,0,2*Math.PI,false);d.fillStyle="rgba(0,0,0,1)";d.fill()}else{var g=d.createRadialGradient(e,f,a*b,e,f,a);g.addColorStop(0,"rgba(0,0,0,1)");g.addColorStop(1,"rgba(0,0,0,0)");d.fillStyle=g;d.fillRect(0,0,2*a,2*a)}return c};var c=function(a){var b=[];var c=a.min;var d=a.max;var e=a.radi;var a=a.data;var f=Object.keys(a);var g=f.length;while(g--){var h=f[g];var i=Object.keys(a[h]);var j=i.length;while(j--){var k=i[j];var l=a[h][k];var m=e[h][k];b.push({x:h,y:k,value:l,radius:m})}}return{min:c,max:d,data:b}};function d(b){var c=b.container;var d=this.shadowCanvas=document.createElement("canvas");var e=this.canvas=b.canvas||document.createElement("canvas");var f=this._renderBoundaries=[1e4,1e4,0,0];var g=getComputedStyle(b.container)||{};e.className="heatmap-canvas";this._width=e.width=d.width=b.width||+g.width.replace(/px/,"");this._height=e.height=d.height=b.height||+g.height.replace(/px/,"");this.shadowCtx=d.getContext("2d");this.ctx=e.getContext("2d");e.style.cssText=d.style.cssText="position:absolute;left:0;top:0;";c.style.position="relative";c.appendChild(e);this._palette=a(b);this._templates={};this._setStyles(b)}d.prototype={renderPartial:function(a){if(a.data.length>0){this._drawAlpha(a);this._colorize()}},renderAll:function(a){this._clear();if(a.data.length>0){this._drawAlpha(c(a));this._colorize()}},_updateGradient:function(b){this._palette=a(b)},updateConfig:function(a){if(a["gradient"]){this._updateGradient(a)}this._setStyles(a)},setDimensions:function(a,b){this._width=a;this._height=b;this.canvas.width=this.shadowCanvas.width=a;this.canvas.height=this.shadowCanvas.height=b},_clear:function(){this.shadowCtx.clearRect(0,0,this._width,this._height);this.ctx.clearRect(0,0,this._width,this._height)},_setStyles:function(a){this._blur=a.blur==0?0:a.blur||a.defaultBlur;if(a.backgroundColor){this.canvas.style.backgroundColor=a.backgroundColor}this._width=this.canvas.width=this.shadowCanvas.width=a.width||this._width;this._height=this.canvas.height=this.shadowCanvas.height=a.height||this._height;this._opacity=(a.opacity||0)*255;this._maxOpacity=(a.maxOpacity||a.defaultMaxOpacity)*255;this._minOpacity=(a.minOpacity||a.defaultMinOpacity)*255;this._useGradientOpacity=!!a.useGradientOpacity},_drawAlpha:function(a){var c=this._min=a.min;var d=this._max=a.max;var a=a.data||[];var e=a.length;var f=1-this._blur;while(e--){var g=a[e];var h=g.x;var i=g.y;var j=g.radius;var k=Math.min(g.value,d);var l=h-j;var m=i-j;var n=this.shadowCtx;var o;if(!this._templates[j]){this._templates[j]=o=b(j,f)}else{o=this._templates[j]}var p=(k-c)/(d-c);n.globalAlpha=p<.01?.01:p;n.drawImage(o,l,m);if(l<this._renderBoundaries[0]){this._renderBoundaries[0]=l}if(m<this._renderBoundaries[1]){this._renderBoundaries[1]=m}if(l+2*j>this._renderBoundaries[2]){this._renderBoundaries[2]=l+2*j}if(m+2*j>this._renderBoundaries[3]){this._renderBoundaries[3]=m+2*j}}},_colorize:function(){var a=this._renderBoundaries[0];var b=this._renderBoundaries[1];var c=this._renderBoundaries[2]-a;var d=this._renderBoundaries[3]-b;var e=this._width;var f=this._height;var g=this._opacity;var h=this._maxOpacity;var i=this._minOpacity;var j=this._useGradientOpacity;if(a<0){a=0}if(b<0){b=0}if(a+c>e){c=e-a}if(b+d>f){d=f-b}var k=this.shadowCtx.getImageData(a,b,c,d);var l=k.data;var m=l.length;var n=this._palette;for(var o=3;o<m;o+=4){var p=l[o];var q=p*4;if(!q){continue}var r;if(g>0){r=g}else{if(p<h){if(p<i){r=i}else{r=p}}else{r=h}}l[o-3]=n[q];l[o-2]=n[q+1];l[o-1]=n[q+2];l[o]=j?n[q+3]:r}k.data=l;this.ctx.putImageData(k,a,b);this._renderBoundaries=[1e3,1e3,0,0]},getValueAt:function(a){var b;var c=this.shadowCtx;var d=c.getImageData(a.x,a.y,1,1);var e=d.data[3];var f=this._max;var g=this._min;b=Math.abs(f-g)*(e/255)>>0;return b},getDataURL:function(){return this.canvas.toDataURL()}};return d}();var d=function j(){var b=false;if(a["defaultRenderer"]==="canvas2d"){b=c}return b}();var e={merge:function(){var a={};var b=arguments.length;for(var c=0;c<b;c++){var d=arguments[c];for(var e in d){a[e]=d[e]}}return a}};var f=function k(){var c=function h(){function a(){this.cStore={}}a.prototype={on:function(a,b,c){var d=this.cStore;if(!d[a]){d[a]=[]}d[a].push(function(a){return b.call(c,a)})},emit:function(a,b){var c=this.cStore;if(c[a]){var d=c[a].length;for(var e=0;e<d;e++){var f=c[a][e];f(b)}}}};return a}();var f=function(a){var b=a._renderer;var c=a._coordinator;var d=a._store;c.on("renderpartial",b.renderPartial,b);c.on("renderall",b.renderAll,b);c.on("extremachange",function(b){a._config.onExtremaChange&&a._config.onExtremaChange({min:b.min,max:b.max,gradient:a._config["gradient"]||a._config["defaultGradient"]})});d.setCoordinator(c)};function g(){var g=this._config=e.merge(a,arguments[0]||{});this._coordinator=new c;if(g["plugin"]){var h=g["plugin"];if(!a.plugins[h]){throw new Error("Plugin '"+h+"' not found. Maybe it was not registered.")}else{var i=a.plugins[h];this._renderer=new i.renderer(g);this._store=new i.store(g)}}else{this._renderer=new d(g);this._store=new b(g)}f(this)}g.prototype={addData:function(){this._store.addData.apply(this._store,arguments);return this},removeData:function(){this._store.removeData&&this._store.removeData.apply(this._store,arguments);return this},setData:function(){this._store.setData.apply(this._store,arguments);return this},setDataMax:function(){this._store.setDataMax.apply(this._store,arguments);return this},setDataMin:function(){this._store.setDataMin.apply(this._store,arguments);return this},configure:function(a){this._config=e.merge(this._config,a);this._renderer.updateConfig(this._config);this._coordinator.emit("renderall",this._store._getInternalData());return this},repaint:function(){this._coordinator.emit("renderall",this._store._getInternalData());return this},getData:function(){return this._store.getData()},getDataURL:function(){return this._renderer.getDataURL()},getValueAt:function(a){if(this._store.getValueAt){return this._store.getValueAt(a)}else if(this._renderer.getValueAt){return this._renderer.getValueAt(a)}else{return null}}};return g}();var g={create:function(a){return new f(a)},register:function(b,c){a.plugins[b]=c}};return g});
			    
			    var hmp_data = null;

			    var specGroup = getSpecificGroup_url();
				if (specGroup == null) {
					specGroup = findGroupFromCookie();
				}
				var specCampaign = getSpecificCampaign_url();
				if (specCampaign == null) {
					specCampaign = findCampaignFromCookie();
				}

			    $.ajax( {
					url: WPB_GETVARS_URL,
					type:"GET",
					dataType:"jsonp",
					crossDomain: true,
					async: true,
					timeout: 5000,
					data: { 
						"sg":  specGroup,
						"sc":  specCampaign,
						"hmp": '1',
					},
					success:function(rdata, textStatus, jqXHR) {
						hmp_data = rdata[0];
						if (hmp_data.length == 0) {return;}
						try {
							add_data();
						} catch (e) {}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("759 oh :(");
					}
				});
				function getRandomizer(bottom, top) {
				        return Math.floor( Math.random() * ( 1 + top - bottom ) ) + bottom;
				}

			    function add_data() {
				    var heatmapInstance = h337.create({
			            container: document.querySelector('body')
			        });

			        var points = [];
			        var max = 0;
			        
			        			      
			        for (var i = 0; i < hmp_data.length; i++) {
			        	var obj = hmp_data[i];
			        	var el = $(obj.selector);
			        	
			        	var point = {
				          x: Math.round(obj.pos.offsetX/obj.pos.el_width * el.width() + el.offset().left),
				          y: Math.round(obj.pos.offsetY/obj.pos.el_height * el.height() + el.offset().top),
				          value: ( obj.selector_clicks / hmp_data.length),
				          radius: 40
				        };

				        var m_l = +($('body').css('margin-left')).replace("px","");
				        var m_t = +($('body').css('margin-top')).replace("px","");
				        

				        point.x = Math.round(point.x - m_l,2);
				        point.y = Math.round(point.y - m_t,2);

				        if (point.x > el.offset().left + el.width()) {
				        	point.x = el.offset().left + getRandomizer(0,el.width());
				        }
				        if (point.y > el.offset().top + el.height()) {
				        	point.y = el.offset().top + getRandomizer(0,el.height());
				        }

	
				        max = Math.max(max, point.value);
				        points.push(point);
			        }
			        var data = { 
			          max: max, 
			          data: points 
			        };			        
			        $(".heatmap-canvas").css({ opacity: 0.7 });			        
			        heatmapInstance.setData(data);
			        
		       	}
				
		   	}
		   	//////////////////////////////////////////////////
		   	//////////////////////////////////////////////////


	})(jQuery);















	/*
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	////////////EXTERNAL STUFF////////////////
	*/

	/*

		WPB-SCROLLING

	*/
	(function($) {
		var wpb_special = jQuery.event.special,
			uid1 = 'D' + (+new Date()),
			uid2 = 'D' + (+new Date() + 1);
		wpb_special.scrollstart = {
			setup: function() {
				var timer,
					handler = function(evt) {
						var _self = this,
							_args = arguments;
						if (timer) {
							clearTimeout(timer);
						} else {
							evt.type = 'scrollstart';
							if (typeof($.event.handle) === 'undefined') {
								$.event.dispatch.apply(_self, _args);
							} else {
								$.event.handle.apply(_self, _args);
							}
						}
						timer = setTimeout(function() {
							timer = null;
						}, wpb_special.scrollstop.latency);
					};
				jQuery(this).bind('scroll', handler).data(uid1, handler);
			},
			teardown: function() {
				jQuery(this).unbind('scroll', jQuery(this).data(uid1));
			}
		};
		wpb_special.scrollstop = {
			latency: 300,
			setup: function() {
				var timer,
					handler = function(evt) {
						var _self = this,
							_args = arguments;
						if (timer) {
							clearTimeout(timer);
						}
						timer = setTimeout(function() {
							timer = null;
							evt.type = 'scrollstop';
							if (typeof($.event.handle) === 'undefined') {
								$.event.dispatch.apply(_self, _args);
							} else {
								$.event.handle.apply(_self, _args);
							}
						}, wpb_special.scrollstop.latency);
					};
				jQuery(this).bind('scroll', handler).data(uid2, handler);
			},
			teardown: function() {
				jQuery(this).unbind('scroll', jQuery(this).data(uid2));
			}
		};
	})(jQuery);

	/**
	 * Within Viewport
	 *
	 * @description Determines whether an element is completely within the browser viewport
	 * @author      Craig Patik, http://patik.com/
	 * @version     1.0.0
	 * @date        2015-08-02
	 */
	(function(root, name, factory) {
		// AMD
		if (typeof define === 'function' && define.amd) {
			define([], factory);
		}
		// Node and CommonJS-like environments
		else if (typeof module !== 'undefined' && typeof exports === 'object') {
			module.exports = factory();
		}
		// Browser global
		else {
			root[name] = factory();
		}
	}(this, 'withinviewport', function() {
		var canUseWindowDimensions = window.innerHeight !== undefined; // IE 8 and lower fail this
		/**
		 * Determines whether an element is within the viewport
		 * @param  {Object}  elem       DOM Element (required)
		 * @param  {Object}  options    Optional settings
		 * @return {Boolean}            Whether the element was completely within the viewport
		 */
		var withinviewport = function withinviewport(elem, options) {
			var result = false;
			var metadata = {};
			var config = {};
			var settings;
			var isWithin;
			var elemBoundingRect;
			var sideNamesPattern;
			var sides;
			var side;
			var i;
			// If invoked by the jQuery plugin, get the actual DOM element
			if (typeof jQuery !== 'undefined' && elem instanceof jQuery) {
				elem = elem.get(0);
			}
			if (typeof elem !== 'object' || elem.nodeType !== 1) {
				throw new Error('First argument must be an element');
			}
			// Look for inline settings on the element
			if (elem.getAttribute('data-withinviewport-settings') && window.JSON) {
				metadata = JSON.parse(elem.getAttribute('data-withinviewport-settings'));
			}
			// Settings argument may be a simple string (`top`, `right`, etc)
			if (typeof options === 'string') {
				settings = {
					sides: options
				};
			} else {
				settings = options || {};
			}
			// Build configuration from defaults and user-provided settings and metadata
			config.container = settings.container || metadata.container || withinviewport.defaults.container || window;
			config.sides = settings.sides || metadata.sides || withinviewport.defaults.sides || 'all';
			config.top = settings.top || metadata.top || withinviewport.defaults.top || 0;
			config.right = settings.right || metadata.right || withinviewport.defaults.right || 0;
			config.bottom = settings.bottom || metadata.bottom || withinviewport.defaults.bottom || 0;
			config.left = settings.left || metadata.left || withinviewport.defaults.left || 0;
			// Use the window as the container if the user specified the body or a non-element
			if (config.container === document.body || !config.container.nodeType === 1) {
				config.container = window;
			}
			// Element testing methods
			isWithin = {
				// Element is below the top edge of the viewport
				top: function _isWithin_top() {
					return elemBoundingRect.top >= config.top;
				},
				// Element is to the left of the right edge of the viewport
				right: function _isWithin_right() {
					var containerWidth;
					if (canUseWindowDimensions || config.container !== window) {
						containerWidth = config.container.innerWidth;
					} else {
						containerWidth = document.documentElement.clientWidth;
					}
					// Note that `elemBoundingRect.right` is the distance from the *left* of the viewport to the element's far right edge
					return elemBoundingRect.right <= containerWidth - config.right;
				},
				// Element is above the bottom edge of the viewport
				bottom: function _isWithin_bottom() {
					var containerHeight;
					if (canUseWindowDimensions || config.container !== window) {
						containerHeight = config.container.innerHeight;
					} else {
						containerHeight = document.documentElement.clientHeight;
					}
					// Note that `elemBoundingRect.bottom` is the distance from the *top* of the viewport to the element's bottom edge
					return elemBoundingRect.bottom <= containerHeight - config.bottom;
				},
				// Element is to the right of the left edge of the viewport
				left: function _isWithin_left() {
					return elemBoundingRect.left >= config.left;
				},
				// Element is within all four boundaries
				all: function _isWithin_all() {
					// Test each boundary in order of most efficient and most likely to be false so that we can avoid running all four functions on most elements
					// Top: Quickest to calculate + most likely to be false
					// Bottom: Note quite as quick to calculate, but also very likely to be false
					// Left and right are both equally unlikely to be false since most sites only scroll vertically, but left is faster
					return (isWithin.top() && isWithin.bottom() && isWithin.left() && isWithin.right());
				}
			};
			// Get the element's bounding rectangle with respect to the viewport
			elemBoundingRect = elem.getBoundingClientRect();
			// Test the element against each side of the viewport that was requested
			sideNamesPattern = /^top$|^right$|^bottom$|^left$|^all$/;
			// Loop through all of the sides
			sides = config.sides.split(' ');
			i = sides.length;
			while (i--) {
				side = sides[i].toLowerCase();
				if (sideNamesPattern.test(side)) {
					if (isWithin[side]()) {
						result = true;
					} else {
						result = false;
						// Quit as soon as the first failure is found
						break;
					}
				}
			}
			return result;
		};
		// Default settings
		withinviewport.prototype.defaults = {
			container: document.body,
			sides: 'all',
			top: 0,
			right: 0,
			bottom: 0,
			left: 0
		};
		withinviewport.defaults = withinviewport.prototype.defaults;
		/**
		 * Optional enhancements and shortcuts
		 *
		 * @description Uncomment or comment these pieces as they apply to your project and coding preferences
		 */
		// Shortcut methods for each side of the viewport
		// Example: `withinviewport.top(elem)` is the same as `withinviewport(elem, 'top')`
		withinviewport.prototype.top = function _withinviewport_top(element) {
			return withinviewport(element, 'top');
		};
		withinviewport.prototype.right = function _withinviewport_right(element) {
			return withinviewport(element, 'right');
		};
		withinviewport.prototype.bottom = function _withinviewport_bottom(element) {
			return withinviewport(element, 'bottom');
		};
		withinviewport.prototype.left = function _withinviewport_left(element) {
			return withinviewport(element, 'left');
		};
		return withinviewport;
	}));
	/**
	 * Within Viewport jQuery Plugin
	 *
	 * @description Companion plugin for withinviewport.js - determines whether an element is completely within the browser viewport
	 * @author      Craig Patik, http://patik.com/
	 * @version     1.0.0
	 * @date        2015-08-02
	 https://github.com/patik/within-viewport
	 */
	(function($) {
		/**
		 * $.withinviewport()
		 * @description          jQuery method
		 * @param  {Object}      [settings] optional settings
		 * @return {Collection}  Contains all elements that were within the viewport
		 */
		$.fn.withinviewport = function(settings) {
			var opts;
			var elems;
			if (typeof settings === 'string') {
				settings = {
					sides: settings
				};
			}
			opts = $.extend({}, settings, {
				sides: 'all'
			});
			elems = [];
			this.each(function() {
				if (withinviewport(this, opts)) {
					elems.push(this);
				}
			});
			return $(elems);
		};
		// Main custom selector
		$.extend($.expr[':'], {
			'within-viewport': function(element) {
				return withinviewport(element, 'all');
			}
		});
		/**
		 * Optional enhancements and shortcuts
		 *
		 * @description Uncomment or comment these pieces as they apply to your project and coding preferences
		 */
		// Shorthand jQuery methods
		$.fn.withinviewporttop = function(settings) {
			var opts;
			var elems;
			if (typeof settings === 'string') {
				settings = {
					sides: settings
				};
			}
			opts = $.extend({}, settings, {
				sides: 'top'
			});
			elems = [];
			this.each(function() {
				if (withinviewport(this, opts)) {
					elems.push(this);
				}
			});
			return $(elems);
		};
		$.fn.withinviewportright = function(settings) {
			var opts;
			var elems;
			if (typeof settings === 'string') {
				settings = {
					sides: settings
				};
			}
			opts = $.extend({}, settings, {
				sides: 'right'
			});
			elems = [];
			this.each(function() {
				if (withinviewport(this, opts)) {
					elems.push(this);
				}
			});
			return $(elems);
		};
		$.fn.withinviewportbottom = function(settings) {
			var opts;
			var elems;
			if (typeof settings === 'string') {
				settings = {
					sides: settings
				};
			}
			opts = $.extend({}, settings, {
				sides: 'bottom'
			});
			elems = [];
			this.each(function() {
				if (withinviewport(this, opts)) {
					elems.push(this);
				}
			});
			return $(elems);
		};
		$.fn.withinviewportleft = function(settings) {
			var opts;
			var elems;
			if (typeof settings === 'string') {
				settings = {
					sides: settings
				};
			}
			opts = $.extend({}, settings, {
				sides: 'left'
			});
			elems = [];
			this.each(function() {
				if (withinviewport(this, opts)) {
					elems.push(this);
				}
			});
			return $(elems);
		};
		// Custom jQuery selectors
		$.extend($.expr[':'], {
			'within-viewport-top': function(element) {
				return withinviewport(element, 'top');
			},
			'within-viewport-right': function(element) {
				return withinviewport(element, 'right');
			},
			'within-viewport-bottom': function(element) {
				return withinviewport(element, 'bottom');
			},
			'within-viewport-left': function(element) {
					return withinviewport(element, 'left');
				}
				// Example custom selector:
				//,
				// 'within-viewport-top-left-45': function (element) {
				//     return withinviewport(element, {sides:'top left', top: 45, left: 45});
				// }
		});
		// Legacy support for camelCase naming
		// DEPRECATED: will be removed in v1.0
		$.fn.withinViewportTop = function(settings) {
			try {
				console.warn('DEPRECATED: use lowercase `withinviewporttop()` instead');
			} catch (e) {}
			return $.fn.withinviewporttop(settings);
		};
		$.fn.withinViewportRight = function(settings) {
			try {
				console.warn('DEPRECATED: use lowercase `withinviewportright()` instead');
			} catch (e) {}
			return $.fn.withinviewportright(settings);
		};
		$.fn.withinViewportBottom = function(settings) {
			try {
				console.warn('DEPRECATED: use lowercase `withinviewportbottom()` instead');
			} catch (e) {}
			return $.fn.withinviewportbottom(settings);
		};
		$.fn.withinViewportLeft = function(settings) {
			try {
				console.warn('DEPRECATED: use lowercase `withinviewportleft()` instead');
			} catch (e) {}
			return $.fn.withinviewportleft(settings);
		};
	}(jQuery));











	/*

	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	///////////////////////// EDITOR ////////////////////////
	*/

	function ab_GetGET() {
	        var y = {};
        if (location.search != "") {
            var x = location.search.substr(1).split("&")
            for (var i = 0; i < x.length; i++) {
                var z = x[i].split("=");
                y[z[0]] = z[1];
            }
        }
        return y;
    }
	function ab_editor_mode() {	    
	    var editor = false;
	    var GETS = ab_GetGET();
	    if (Object.keys(GETS).length > 0) {
	        for (var key in GETS) {
	            if (key.indexOf('ab-editor') >= 0) {
	                console.log("editor mode enabled");
	                editor = true;
	                break;
	            }
	        }
	    }
	    return editor;
	}
	function ab_heatmap_mode() {	    
	    var hmp = false;
	    var GETS = ab_GetGET();
	    if (Object.keys(GETS).length > 0) {
	        for (var key in GETS) {
	            if (key.indexOf('ab-hmp') >= 0) {
	                console.log("heatmap mode enabled");
	                hmp = true;
	                break;
	            }
	        }
	    }
	    return hmp;
	}



	(function($) {
	    if (!ab_editor_mode()) {	    	
	        return;
	    }
	    var slice = [].slice;
	    var cancel, disable, enable, firstChild, getSelector, hideOverlay, hovered, keyMap, log, nextSibling, nodeContains, notEmpty, onClick, onKeyDown, onKeyUp, onMouseMove, overlay, overlayPos, prevSibling, selectElement, setOverlayText, showOverlay, waiting;
	    log = function() {
	        var args;
	        args = 1 <= arguments.length ? slice.call(arguments, 0) : [];
	        args.unshift("dom-selector:");
	        return console.log.apply(console, args);
	    };
	    waiting = null;
	    overlay = $("<div id='dom-selector-overlay' style='display:none'><p class='selector-hint' style='margin-bottom:5px'><strong>Left click for A/B, Right click for CTA.</strong></p><p class='current-selector' style='color:#226'></p></div>");
	    overlayPos = 'up';
	    $(document).ready(function() {
	        return overlay.appendTo("body");
	    });
	    showOverlay = function(x, y, w, h) {
	        var commonStyles;
	        commonStyles = {
	            display: "block",
	            color: "#222",
	            font: "10pt / 1 sans-serif",
	            verticalAlign: 'baseline',
	            textAlign: "center"
	        };
	        return overlay.css($.extend({
	            position: "fixed",
	            top: parseInt(y) + "px",
	            left: parseInt(x) + "px",
	            width: parseInt(w) + "px",
	            height: "auto",
	            minHeight: parseInt(h) + "px",
	            padding: "10px 5px",
	            border: "1px solid silver",
	            borderRadius: "5px",
	            boxShadow: "1px 1px 2px silver",
	            opacity: 0.9,
	            zIndex: 99999999,
	            background: "#ffc"
	        }, commonStyles)).find('p').css($.extend({
	            padding: 0,
	            margin: "0 0 5px 0"
	        }, commonStyles)).find('p.current-selector').css({
	            color: '#226'
	        });
	    };
	    hideOverlay = function() {
	        return overlay.css({
	            display: "none"
	        });
	    };
	    setOverlayText = function(selector) {
	        return $("div#dom-selector-overlay .current-selector").text(selector);
	    };
	    notEmpty = function(i, s) {
	        return (s != null ? s.length : void 0) > 0;
	    };
	    getSelector = (function() {
	        var nthChild;
	        nthChild = function(elem) {
	            var child, j, len, n, parent, ref, ref1;
	            if (((elem == null) || (elem.ownerDocument == null), elem === document || elem === document.body || elem === document.head)) {
	                return "";
	            }
	            if (parent = elem != null ? elem.parentNode : void 0) {
	                n = 0;
	                ref = parent.childNodes;
	                for (j = 0, len = ref.length; j < len; j++) {
	                    child = ref[j];
	                    if ((ref1 = child.nodeName) === "#text" || ref1 === "#comment") {
	                        continue;
	                    }
	                    n += 1;
	                    if (child === elem) {
	                        return ":nth-child(" + n + ")";
	                    }
	                }
	            }
	            return elem.nodeName.toLowerCase();
	        };
	        return function(element, verbose) {
	            var hasClass, hasId, hasParent, isBody, isElement, isRoot, parentSelector, s;
	            hasId = notEmpty(0, element.id);
	            hasClass = notEmpty(0, element.className);
	            isElement = element.nodeType === 1;
	            isRoot = element.parentNode === element.ownerDocument;
	            isBody = element === document.body;
	            hasParent = element.parentNode != null;
	            switch (true) {
	                case isRoot:
	                    s = "";
	                    break;
	                case !isElement:
	                    s = "";
	                    break;
	                case isBody:
	                    s = "body";
	                    break;
	                case hasId:
	                    s = "#" + element.id;
	                    break;
	                case hasClass:
	                    s = "." + element.className.split(" ").join(".").replace(/\.$/, '');
	                    break;
	                default:
	                    s = element.nodeName.toLowerCase();
	            }
	            if (hasId) {
	                return s;
	            }
	            if (verbose) {
	                s += nthChild(element);
	            }
	            if ((!isRoot) && isElement && hasParent) {
	                parentSelector = getSelector(element.parentNode);
	                if (parentSelector !== "") {
	                    return getSelector(element.parentNode, verbose) + " > " + s;
	                }
	            }
	            return s;
	        };
	    })();
	    window.minifySelector = function(selector, node) {
	        var bisect, candidate, chunk, chunks, i, j, left, len, ref, result, right;
	        selector = selector.replace(/^\s+/, '');
	        if (selector.length === 0) {
	            return selector;
	        }
	        if (node == null) {
	            node = document.querySelector(selector);
	        }
	        bisect = function(a, x) {
	            var i;
	            i = a.indexOf(x);
	            return [a.slice(0, i), a.slice(i + 1, a.length)];
	        };
	        chunks = selector.split(" > ");
	        for (i = j = 0, len = chunks.length; j < len; i = ++j) {
	            chunk = chunks[i];
	            ref = bisect(chunks, chunk), left = ref[0], right = ref[1];
	            candidate = left.join(" > ") + " " + right.join(" > ");
	            candidate = candidate.replace(/^\s+/, '');
	            if (candidate.length > 0) {
	                result = document.querySelectorAll(candidate);
	                if (result.length === 1 && result[0] === node) {
	                    return minifySelector(candidate, node);
	                }
	            }
	        }
	        return selector;
	    };
	    hovered = {
	        element: null,
	        selector: null,
	        restore: null,
	        styles: {
	            'background': "rgba(255,102,51,.5)",
	            'background-color': "rgba(255,102,51,.5)",
	            'outline': '5px solid #FF6633'
	        },
	        unhighlight: function() {
	            var k;
	            if ((this.element != null) && (this.restore != null)) {
	                for (k in this.restore) {
	                    this.element.css(k, this.restore[k]);
	                }
	            }
	            this.element = null;
	            return this.restore = null;
	        },
	        highlight: function() {
	            var k;
	            if (this.element != null) {
	                this.restore = {};
	                for (k in this.styles) {
	                    this.restore[k] = this.element.prop("style")[k];
	                }
	                console.log(this.restore);
	                return this.element.css(this.styles);
	            }
	        },
	        update: function(target) {
	            var count, ref;
	            if (target === null || target === (void 0) || target === ((ref = this.element) != null ? ref[0] : void 0) || target === overlay[0]) {
	                return;
	            }
	            if (nodeContains(overlay[0], target)) {
	                return;
	            }
	            this.unhighlight();
	            this.element = $(target);
	            this.highlight();
	            this.selector = getSelector(target, false);
	            count = $(this.selector).length;
	            if (count > 1) {
	                this.selector = getSelector(target, true);
	            }
	            return setOverlayText(this.selector = minifySelector(this.selector));
	        }
	    };
	    nodeContains = function(node, target) {
	        var child, j, len, ref;
	        ref = node.childNodes;
	        for (j = 0, len = ref.length; j < len; j++) {
	            child = ref[j];
	            if (child === target || nodeContains(child, target)) {
	                return true;
	            }
	        }
	        return false;
	    };
	    keyMap = {
	        13: "enter",
	        37: "left",
	        38: "up",
	        39: "right",
	        40: "down"
	    };
	    cancel = function(evt) {
	        evt.preventDefault();
	        evt.stopPropagation();
	        evt.cancelBubble = true;
	        return false;
	    };
	    firstChild = function(elem) {
	        var child;
	        child = elem.childNodes[0];
	        while ((child != null) && child.nodeType !== 1) {
	            child = child.nextSibling;
	        }
	        return child;
	    };
	    nextSibling = function(elem) {
	        var next;
	        next = elem.nextSibling;
	        while ((next != null) && next.nodeType !== 1) {
	            next = next.nextSibling;
	        }
	        return next;
	    };
	    prevSibling = function(elem) {
	        var previous;
	        previous = elem.previousSibling;
	        while ((previous != null) && previous.nodeType !== 1) {
	            previous = previous.previousSibling;
	        }
	        return previous;
	    };
	    onMouseMove = function(event) {
	        var pos, top;
	        hovered.update(event.target);
	        pos = (function() {
	            switch (false) {
	                case !(event.pageY < 150):
	                    return 'down';
	                default:
	                    return 'up';
	            }
	        })();
	        if (pos === overlayPos) {
	            return;
	        }
	        top = (function() {
	            switch (pos) {
	                case 'down':
	                    return 200;
	                default:
	                    return 10;
	            }
	        })();
	        $(overlay).animate({
	            top: top + "px"
	        }, {
	            duration: 'fast'
	        });
	        return overlayPos = pos;
	    };
	    onKeyUp = function(event) {
	        var element, ref;
	        element = (ref = hovered.element) != null ? ref[0] : void 0;
	        if (element == null) {
	            return;
	        }
	        hovered.update((function() {
	            switch (keyMap[event.keyCode]) {
	                case "left":
	                    return element.parentNode;
	                case "right":
	                    return firstChild(element);
	                case "down":
	                    return nextSibling(element);
	                case "up":
	                    return prevSibling(element);
	                case "enter":
	                    return onClick(event);
	                default:
	                    return null;
	            }
	        })());
	        if (event.keyCode in keyMap) {
	            return cancel(event);
	        }
	    };
	    onKeyDown = function(event) {
	        switch (keyMap[event.keyCode]) {
	            case "left":
	            case "right":
	            case "up":
	            case "down":
	                return cancel(event);
	            default:
	                return true;
	        }
	    };
	    onClick = function(event) {
	        if (event.button == 0) {
	            if (overlay[0] === event.target || nodeContains(overlay[0], event.target)) {
	                return cancel(event);
	            }
	            if (typeof waiting === "function") {
	                waiting(hovered.element, hovered.selector, 'ab');
	            }
	            return cancel(event);
	        }
	    };
	    onClickRight = function(event) {
	        if (event.button == 2) {
	            if (overlay[0] === event.target || nodeContains(overlay[0], event.target)) {
	                return cancel(event);
	            }
	            if (typeof waiting === "function") {
	                waiting(hovered.element, hovered.selector, 'cta');
	            }
	            return cancel(event);
	        }
	    };
	    enable = function() {
	        showOverlay(10, 10, 280, 30);
	        log("Binding events");
	        return $(document.body).mousemove(onMouseMove).keyup(onKeyUp).keydown(onKeyDown).click(onClick).mousedown(onClickRight);
	    };
	    disable = function() {
	        //log("Unbinding events");
	        //hideOverlay();
	        hovered.unhighlight();
	        console.log('disable');
	        // empty style=""  sometimes, let's remove it.
	        var attr = $(hovered.selector).attr('style');
	        if (typeof attr !== typeof undefined && attr !== false) {
	            //if (attr === "") {
	                //$(hovered.selector).removeAttr('style');
	            //}
	        }
	        //return $(document.body).unbind("mousemove", onMouseMove).unbind("click", onClick).unbind("keyup", onKeyUp).unbind("keydown", onKeyDown);
	        return true;
	    };
	    selectElement = function(cb) {
	        waiting = function(selected, selector, type) {
	            disable(); //ici
	            return typeof cb === "function" ? cb(selected[0], selector, type) : void 0;
	        };
	        return enable();
	    };
	    window.DOMSelector || (window.DOMSelector = {});
	    return (window.DOMSelector.attach = function(jQ) {
	        return $ = jQ.extend(jQ, {
	            selectElement: selectElement,
	            getSelector: getSelector
	        });
	    })(window.jQuery);
	})(window.jQuery);


	;
	(function(win) {
	    if (!ab_editor_mode()) {
	        return;
	    }
	    var wa_boot = (function(win, $) {        
	        $(document).ready(function() {  
	        console.log(win);      	
	        	$('body').attr('oncontextmenu', 'return false;');
	        	window.top.postMessage({'mytype':'test'}, "*");
	            window.DOMSelector.attach($);            
	            $.selectElement(function(element, selector, type) {
	                var win = window.top;
	                var outerHtml = $('<div>').append($(selector).clone()).html();
	                var message = {
	                    type: 'dom-selector',
	                    selector: selector,
	                    'html': outerHtml,
	                    'mytype': type //cta or ab
	                }
	                if (element && element.tagName) {
	                    message.tagName = element.tagName.toString();
	                }
	                if (win.opener == null) {
	                	win.postMessage(message, "*");
	            	} else {
	            		win.opener.postMessage(message, "*");
	            	}                
	            });

	        });
	    });    
	    wa_boot(win, win.jQuery);

	})(window);

}






