// prevent javascript error before the content has loaded
TB_WIDTH_SES = 0;
TB_HEIGHT_SES = 0;
var executetimesmoothbox = false;;
// add sessmoothbox to href elements that have a class of .sessmoothbox
sesJqueryObject(document).on('click','.sessmoothbox',function(event){
	event.preventDefault();
	sessmoothboxopen(this);
});
function sessmoothboxopen(obj) {  
  
	if(!sesJqueryObject('.sessmoothbox_main').length){
    
		new Element('div').setProperty('class', 'sessmoothbox_overlay').setProperty('id','sessmoothbox_overlay').injectInside(document.body);
		if(sesJqueryObject(obj).hasClass('sessocial_icon_add_btn')) {
		new Element('div').setProperty('class', 'sessmoothbox_main sessocialshare_smoothbox_main').setProperty('id','sessmoothbox_main').injectInside(document.body);
		} else {
		new Element('div').setProperty('class', 'sessmoothbox_main').setProperty('id','sessmoothbox_main').injectInside(document.body); 
		}
		$("sessmoothbox_main").innerHTML = '<div class="sessmoothbox_container" id="sessmoothbox_container"><div class="sesbasic_loading_container"></div></div>';
    if(sesJqueryObject(obj).data('addclass')){
      sesJqueryObject('#sessmoothbox_container').addClass(sesJqueryObject(obj).data('addclass'));
    }
	 loaddefaultcontent();
	}
	// display the box for the elements href
	sessmoothboxshow(obj);
	return false;
}
//esc key close
sesJqueryObject(document).on('keyup', function (e) {
		if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA' || !sesJqueryObject('#sessmoothbox_container').length)
				return true;
		//ESC key close
		if (e.keyCode === 27) { 
			sessmoothboxclose();return false; 
		}
});
sesJqueryObject(document).on('click','.sessmoothbox_main',function(e){
  if (e.target !== this)
    return;
	sessmoothboxclose();
});
function loaddefaultcontent(){
	var htmlElement = document.getElementsByTagName("html")[0];
  htmlElement.style.overflow = 'hidden';
	$("sessmoothbox_container").setStyles({
		left: ((sesJqueryObject(window).width() - 300 ) / 2) + 'px',
		top: ((sesJqueryObject(window).height() - 100 ) / 2) + 'px',
		display: "block"
	});	
}
var Sessmoothbox = {
		javascript : [],
		css : [],
}
// called when the user clicks on a sessmoothbox link
function sessmoothboxshow(obj){    
   if(obj){
		 	//initialize blank array value
			Sessmoothbox.javascript = Array();
			Sessmoothbox.css = [];
			var url = sesJqueryObject(obj).attr('href');
			if(url == 'javascript:;' || sesJqueryObject(obj).hasClass('open'))
				url = sesJqueryObject(obj).attr('data-url');
			var params = sesJqueryObject(obj).attr('rel');
			var requestSmoothbox = new Request.HTML({
      url: url,
      method: 'get',
      data: {
        format: 'html',
				params:params,
				typesmoothbox:'sessmoothbox'
      },
      evalScripts: true,
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if(sesJqueryObject(obj).hasClass('sessocial_icon_add_btn')) {
          responseHTML = responseHTML.replace('sessocialtitleofconent', document.title);
        }
				executeCssJavascriptFiles(responseHTML);
			}
    });
    requestSmoothbox.send();
  }	
}
function sessmoothboxExecuteCode(responseHTML,prevWidth){
  if(typeof sessmoothboxcallbackBefore == 'function')
		sessmoothboxcallbackBefore(responseHTML);

	responseHTML = '<a title="'+en4.core.language.translate("Close")+'" class="sessmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:sessmoothboxclose();"></a>'+responseHTML;
	sesJqueryObject('#sessmoothbox_container').html(responseHTML);	
	//execute code at run once
	if(!executetimesmoothbox){
		executetimesmoothboxTimeinterval = 10;	
	}
	setTimeout(function(){en4.core.runonce.trigger(); }, executetimesmoothboxTimeinterval);
	resizesessmoothbox(prevWidth);
  if(sesJqueryObject('.sesbasic_custom_scroll').length)
    jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
        theme:"minimal-dark"
  });
}
function sessmoothboxclose(){
	sesJqueryObject('.sessmoothbox_main').remove();
	sesJqueryObject('#sessmoothbox_overlay').remove();
	var htmlElement = document.getElementsByTagName("html")[0];
	htmlElement.style.overflow = '';
	executetimesmoothbox = false;
  sessmoothboxcallback = function () {};
  sessmoothboxcallbackBefore = function () {};
  if(typeof sessmoothboxcallbackclose == 'function')
		sessmoothboxcallbackclose();
}
function resizesessmoothbox(prevWidth){
 var linkClose = '<a title="'+en4.core.language.translate("Close")+'" class="sessmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:sessmoothboxclose();"></a>';
 sesJqueryObject('#sessmoothbox_container').prepend(linkClose);
 var windowheight = sesJqueryObject(window).height();
 var objHeight =	sesJqueryObject('#sessmoothbox_container').height();
 var windowwidth= sesJqueryObject(window).width();
 var objWidth=	sesJqueryObject('#sessmoothbox_container').width();
 if(objHeight >= windowheight){
		var top = '10'; 
 }else if(objHeight <= windowheight){
		var top = (windowheight - objHeight)/2;		 
 }
 var width = sesJqueryObject('#sessmoothbox_container').find('div').first().width();
 var	setwidth= width /2 ;
 sesJqueryObject("#sessmoothbox_container").animate({
		top: top+'px',
		width: width+'px',
		left: (((sesJqueryObject(window).width() ) / 2) - setwidth) + 'px',
 },400,function() {
    if(typeof sessmoothboxcallback == 'function')
		  sessmoothboxcallback();
    // Animation complete.
  });
}
var successLoad;
function executeCssJavascriptFiles(responseHTML){
	var jsCount = Sessmoothbox.javascript.length;
	var cssCount = Sessmoothbox.css.length;
	//store the total file so we execute all required function after css and js load.
	var totalFiles = jsCount + cssCount;
	successLoad= 0;
	var prevWidth = sesJqueryObject('#sessmoothbox_container').width();
	if(jsCount == cssCount){
		sessmoothboxExecuteCode(responseHTML,prevWidth);
	}
	//execute jsvascript files
	for(var i=0;i < jsCount;i++){
			Asset.javascript(Sessmoothbox.javascript[i], {
			onLoad: function(e) {
				successLoad++;
				if (successLoad === totalFiles)
					sessmoothboxExecuteCode(responseHTML,prevWidth);
			}});
	}
		//execute css files
	for(var i=0;i < cssCount;i++){
			Asset.css(Sessmoothbox.css[i], {
			onLoad: function() {
				successLoad++;
				if (successLoad === totalFiles)
					sessmoothboxExecuteCode(responseHTML,prevWidth);
			}});
	}
}

function sessmoothboxDialoge(html) {  
	if(!sesJqueryObject('.sessmoothbox_main').length){
		new Element('div').setProperty('class', 'sessmoothbox_overlay').setProperty('id','sessmoothbox_overlay').injectInside(document.body);
		new Element('div').setProperty('class', 'sessmoothbox_main').setProperty('id','sessmoothbox_main').injectInside(document.body); 
		$("sessmoothbox_main").innerHTML = '<div class="sessmoothbox_container" id="sessmoothbox_container"><div class="sesbasic_loading_container"></div></div>';
	 loaddefaultcontent();
     executeCssJavascriptFiles("<div class='sesbasic_smoothbox_view_number'><div class='_header'>Phone Number</div><div class='_cont'>" + html + "</div></div>");
	}
	// display the box for the elements href
	return false;
}