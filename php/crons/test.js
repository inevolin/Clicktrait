function pad(num, size) {
  var s = num + "";
  while (s.length < size) s = "0" + s;
  return s;
}
try {
  var system = require('system');
  var args = ['test.js', 'https://clicktrait.com', 'demo.jpg' , '/home/clicktrait/public_html/ab/php/crons/'];
  if (args.length !== 4) {
    console.log(JSON.stringify(' arguments required : 3, arguments specified: ' + (args.length - 1)));
    phantom.exit();
  } else {
    var url = args[1];
    var div = 'html';
    var dir = args[3];
    var page = require('webpage').create();
    page.settings.userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64)  AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36';
    page.viewportSize = {
      width: 1600,
      height: 900
    };
    msgs='';//use somewhere if needed.
    page.onConsoleMessage = function(msg) {
      msgs += JSON.stringify(msg) + '\n';
    }
    page.onError = function(msg, trace) {
      //console.log(JSON.stringify("ERR 1 - " + msg + "  --- " + JSON.stringify(trace)));
      //phantom.exit();
    };

    phantom.onError = function(msg, trace) {
      console.log(JSON.stringify("ERR 2 - " + msg));
      phantom.exit();
    };
    //console.log(JSON.stringify(div));phantom.exit();
    //page.settings.resourceTimeout = 10000; //we will use timeout command in linux

    function waitForGlobal(key, page, expiry, callback) {
      var result = page.evaluate(function() {
        return WPB_REPS_LOADED_ASYNC;
      });
      // if desired element found then call callback after 50ms
      if (result) {
        window.setTimeout(function() {
          callback(true);
        }, 50);
        return;
      }
      // determine whether timeout is triggered
      var finish = (new Date()).getTime();
      if (finish > expiry) {
        callback(false);
        return;
      }
      // haven't timed out, haven't found object, so poll in another 100ms
      setTimeout(function() {
        waitForGlobal(key, page, expiry, callback);
      }, 1000);
    }

    function onPageReady() {
      try {
        
        page.injectJs('./jquery.min.js');
        
        	var key = 'html';
          var uniqueName = args[2];
          var globalVar = page.evaluate(function(div) {
            return WPB_REPS_LOADED_ASYNC;
          }, key);
          waitForGlobal(key, page, (new Date()).getTime() + 5000, function(status) {
            if (status) {
              var bb = page.evaluate(function(key) {

                  //prevent black/transparant BG issue
                  var style = document.createElement('style'),
                  text = document.createTextNode('body { background: #fff }');
                  style.setAttribute('type', 'text/css');
                  style.appendChild(text);
                  document.head.insertBefore(style, document.head.firstChild);
                  //end

                return $(key)[0].getBoundingClientRect();
              }, key);
              
              page.clipRect = {
                top: bb.top,
                left: bb.left,
                width: bb.width,
                height: bb.height
              };
              var filename = dir + uniqueName;
              console.log(filename);
              var test = page.render(filename);
              console.log(JSON.stringify("OK"));
              if (!test) {
                console.log(JSON.stringify("ERR 3 - did you chmod uploads?"));
              }
              phantom.exit();
            } else {
              console.log(JSON.stringify("ERR 4 - did you chmod uploads?"));
              phantom.exit();
            }
          });
        
      } catch (era) {
        console.log(JSON.stringify("ERR 5 - " + era));
        phantom.exit();
      }
    }
    page.open(url, function(status) {
      function checkReadyState() {
        setTimeout(function() {
          var readyState = page.evaluate(function() {
            return document.readyState;
          });
          if ("complete" === readyState) {
            window.setTimeout(function () {
              onPageReady(status);
             }, 1000); //give it some time to do Jquery/JS stuff (effects, if any)
          } else {
            checkReadyState();
          }
        });
      };
      checkReadyState();
    });
  }
} catch (err) {
  console.log(JSON.stringify("ERR 6 -" + err));
  phantom.exit();
}