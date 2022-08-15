	var dataCommentSes = '';
	// store the default browser URL for change state after closing image viewer
	var defaultHashURL = '';
	var requestPhotoSesalbumURL;
	defaultHashURL = document.URL;
	var firstStartPoint = canPaginateAllPhoto = 0;
	firstStartPointModule = 0;
	var height;
	var width;
	var getTagData;
	var mediaTags ;
   var offsetY = window.pageYOffset;
	function makeLayoutForImageViewer(){
		if(sesJqueryObject('#ses_media_lightbox_container').length)
			return;
		sesJqueryObject('<div id="ses_media_lightbox_container" style="display:block" class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg" id="overlayViewer"></div><div class="pswp__scroll-wrap" id="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div> <div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter" style="display:none"><!-- pagging --></div><a class="pswp__button pswp__button--close" title="Close (Esc)"></a><a class="pswp__button pswp__button--share" title="Share"></a><a class="pswp__button sesalbum_toogle_screen"  href="javascript:;" onclick="toogle()" title="Toggle Fullscreen"></a><a class="pswp__button pswp__button--info pswp__button--info-show" style="display:none" id="pswp__button--info-show-lightbox" title="Show Info"></a><a class="pswp__button pswp__button--zoom" id="pswp__button--zoom" title="Zoom in/out"></a><div class="pswp__top-bar-action"><div class="pswp__top-bar-albumname" style="display:none">In <a href="javascript:;">Album Name</a></div><div class="pswp__top-bar-tag" style="display:none"><a href="javascript:;">Add Tag</a></div><div class="pswp__top-bar-share" style="display:none"><a href="javascript:;">Share</a></div><div class="pswp__top-bar-like" style="display:none"><a href="javascript:;">Like</a></div><div class="pswp__top-bar-more" style="display:none"><a href="javascript:;">Options<i class="fa fa-angle-down"></i></a><div class="pswp__top-bar-more-tooltip" style="display:none"><a href="javascript:;">Download</a><a href="javascript:;">Make Profile Picture</a></div></div></div><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="overlay-model-class pswp__share-modal--fade-in" style="display:none"></div><div class="pswp__loading-indicator"><div class="pswp__loading-indicator__line"></div></div><div id="nextprevbttn"><a class="pswp__button pswp__button--arrow--left"  id="closeViewer" title="Previous (arrow left)"></a><a class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></a></div><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div><div class="ses_media_lightbox_slideshow_options sesbasic_bxs"><a href="javascript:;" id="sesalbum_slideshow_playpause"><i class="fa fa-play"></i><span>Play</span></a><a href="javascript:;" id="sesalbum_slideshow_stop"><i class="fa fa-stop"></i></a></div></div><div id="all_photo_container" style="display:none"></div><div id="last-element-content" style="display:none;"></div></div>').appendTo('body');	
	}
	sesJqueryObject(document).on('click','.seslightbox_image_open',function(e){
			var image = sesJqueryObject(this).find('img').attr('src');
			if(image)
				openDirectImageLightbox(image);
	});
	function openDirectImageLightbox(imageURL){
		makeLayoutForImageViewer();
		var img = document.createElement('img');
		img.onload = function(){
		 width = this.width;
		 height = this.height;
		 openPhotoSwipe(imageURL,width,height);
		//check function call from image viewer or direct
		sesJqueryObject('.pswp__top-bar-action').css('display','none');
		sesJqueryObject('.pswp__button pswp__button--info').css('display','none');
		sesJqueryObject('#nav-btn-next').css('display','none');
		sesJqueryObject('#nav-btn-prev').css('display','none');
		}
		img.src = imageURL;	
	}
  function getRequestedAlbumPhotoForImageViewer(imageURL,requestedURL,forceAllPhoto,moduleData,sesModuleData){
	makeLayoutForImageViewer();
  offsetY = window.pageYOffset;
  sesJqueryObject('html').css('position','fixed').css('width','100%').css('overflow','hidden');
  sesJqueryObject('html').css('top', -offsetY + 'px');
	sesJqueryObject('#ses_media_lightbox_container').css('display','block');
	var img = document.createElement('img');
	img.onload = function(){
	//sesJqueryObject('#ses_media_lightbox_container').show();
	 width = this.width;
	 height = this.height;
	 openPhotoSwipe(imageURL,width,height);
  //check function call from image viewer or direct
	if(!dataCommentSes){
		dataCommentSes = sesJqueryObject('.layout_core_comments').html();
		sesJqueryObject('.layout_core_comments').html('');
		getTagData = sesJqueryObject('#media_photo_div').find('*[id^="tag_"]');
		sesJqueryObject('#media_photo_div').find('*[id^="tag_"]').remove();
		mediaTags =	sesJqueryObject('#media_tags').html();
		sesJqueryObject('#media_tags').html('');
	}
	sesJqueryObject('.pswp__preloader').addClass('pswp__preloader--active');
	sesJqueryObject('.pswp__top-bar-action').css('display','none');
	sesJqueryObject('#nav-btn-next').css('display','none');
	sesJqueryObject('#nav-btn-prev').css('display','none');
	var urlChangeState = requestedURL.replace('image-viewer-detail','view');
	urlChangeState = urlChangeState.replace('third-party-imageview-integration','view');
	history.pushState(null, null, urlChangeState);
	if(firstStartPoint == 0 && feedPhoto){
		//sesJqueryObject('#gallery-img').css('display','none');
	}else{
		sesJqueryObject('#gallery-img').attr('src',imageURL);
	}
	getImageViewerObjectData(imageURL,requestedURL,forceAllPhoto,firstStartPoint,moduleData,sesModuleData);	
	}
	img.src = imageURL;	
}
sesJqueryObject(document).on("click", '.seslightbox_no_prop', function (e) {
	e.preventDefault();
});
sesJqueryObject(document).on('click','.optionOpenImageViewerLightbox',function(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	if(sesJqueryObject(this).hasClass('active')){
		sesJqueryObject(this).removeClass('active');
		sesJqueryObject('.pswp__top-bar-more-tooltip').css('display','none');
		sesJqueryObject(".overlay-model-class").css('display','none');
	}else{
		sesJqueryObject(this).addClass('active');
		sesJqueryObject('.pswp__top-bar-more-tooltip').css('display','block');
		sesJqueryObject(".overlay-model-class").css('display','block');
	}	
});
sesJqueryObject(document).on('click','#pswp__button--info-show-lightbox', function(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
		if(sesJqueryObject('#pswp__button--info-show-lightbox').hasClass('active')){
      sesJqueryObject("#pswp__button--info-show-lightbox").removeClass('active');
      sesJqueryObject("#pswp__scroll-wrap").removeClass('pswp_info_panel_open');
      sesJqueryObject("#pswp__scroll-wrap").addClass('pswp_info_panel_close');
    }else{
      sesJqueryObject("#pswp__scroll-wrap").addClass('pswp_info_panel_open');
      sesJqueryObject("#pswp__button--info-show-lightbox").addClass('active');
      sesJqueryObject("#pswp__scroll-wrap").removeClass('pswp_info_panel_close');
    }
			setTimeout(function(){ gallery.updateSize(true); }, 510);
});
 var gallery;
 var openPhotoSwipe = function(imageUrl,width,height) {
    var pswpElement = document.querySelectorAll('.pswp')[0];
    // build items array
    var items = [
        {
            src: imageUrl,
            w: width,
            h: height
        }
    ];
    // define options (if needed)
		/*
			options.mainClass = 'pswp--minimal--dark';
			options.barsSize = {top:0,bottom:0};
			options.captionEl = false;
			options.fullscreenEl = false;
			options.shareEl = false;
			options.bgOpacity = 0.85;
			options.tapToClose = true;
			timeToIdle: 4000;
			options.tapToToggleControls = false;
		*/
    var options = {
        history: false,
        focus: false,
				tapToClose: false,
				shareEl: false,
				closeOnScroll:false,
				clickToCloseNonZoomable: false,
        showAnimationDuration: 0,
        hideAnimationDuration: 0,
				closeOnVerticalDrag : false,
    };
		
    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.init();
		// before close
		gallery.listen('close', function() {
			closeFunctionCall();
		});
		// before destroy event
		gallery.listen('destroy', function() {
			closeFunctionCall();
		});
};          
function closeFunctionCall(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
  sesJqueryObject('html').css('position','auto').css('overflow','auto');
  sesJqueryObject(window).scrollTop(offsetY);
  if(sesJqueryObject('.emoji_content').css('display') == 'block')
    sesJqueryObject('.exit_emoji_btn').click();
	index = 0;
	sesaIndex=0;
	if(dataCommentSes)
		sesJqueryObject('.layout_core_comments').html(dataCommentSes);
		history.pushState(null, null, defaultHashURL);
		clearInterval(slideShowInterval);
		firstStartPoint = 0;
		dataCommentSes = '';
		slideshow = false;
		sesLightbox = false;
		firstStartPointModule = 0;
		if(getTagData != ''){
			sesJqueryObject('#media_photo_next').after(getTagData);	
		}
		if(mediaTags != ''){
			sesJqueryObject('#media_tags').html(mediaTags);		
		}	
		sesJqueryObject('#ses_media_lightbox_container').remove();
		sesJqueryObject('#ses_media_lightbox_container_video').remove();
}
// fullscreen code
function changeImageViewerResolution(type){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	if(type == 'fullscreen'){
		sesJqueryObject('#ses_media_lightbox_container').addClass('fullscreen');
	}else{
		sesJqueryObject('#ses_media_lightbox_container').removeClass('fullscreen');
	}
	return true;
}

//http://johndyer.name/native-fullscreen-javascript-api-plus-jquery-plugin/
var is_fullscreen = 0;
(function() {
	var 
		SESfullScreenApi = { 
			supportsFullScreen: false,
			isFullScreen: function() { return false; }, 
			requestFullScreen: function() {}, 
			cancelFullScreen: function() {},
			fullScreenEventName: '',
			prefix: ''
		},
		browserPrefixes = 'webkit moz o ms khtml'.split(' ');
	// check for native support
	if (typeof document.cancelFullScreen != 'undefined') {
		SESfullScreenApi.supportsFullScreen = true;
	} else {	 
		// check for fullscreen support by vendor prefix
		for (var i = 0, il = browserPrefixes.length; i < il; i++ ) {
			SESfullScreenApi.prefix = browserPrefixes[i];
			if (typeof document[SESfullScreenApi.prefix + 'CancelFullScreen' ] != 'undefined' ) {
				SESfullScreenApi.supportsFullScreen = true;
				break;
			}
		}
	}
	// update methods to do something useful
	if (SESfullScreenApi.supportsFullScreen) {
		SESfullScreenApi.fullScreenEventName = SESfullScreenApi.prefix + 'fullscreenchange';
		SESfullScreenApi.isFullScreen = function() {
			switch (this.prefix) {	
				case '':
					return document.fullScreen;
				case 'webkit':
					return document.webkitIsFullScreen;
				default:
					return document[this.prefix + 'FullScreen'];
			}
		}
		SESfullScreenApi.requestFullScreen = function(el) {
			return (this.prefix === '') ? el.requestFullScreen() : el[this.prefix + 'RequestFullScreen']();
		}
		SESfullScreenApi.cancelFullScreen = function(el) {
			return (this.prefix === '') ? document.cancelFullScreen() : document[this.prefix + 'CancelFullScreen']();
		}		
	}

	// jQuery plugin
	if (typeof jQuery != 'undefined') {
		jQuery.fn.requestFullScreen = function() {
			return this.each(function() {
				var el = jQuery(this);
				if (SESfullScreenApi.supportsFullScreen) {
					SESfullScreenApi.requestFullScreen(el);
				}
			});
		};
	}
	// export api
	window.SESfullScreenApi = SESfullScreenApi;	
})();
// do something interesting with fullscreen support
var fsButton = document.getElementById('fsbutton');
function toogle(){

if(is_fullscreen == 0 || slideshow)
	window.SESfullScreenApi.requestFullScreen(document.body);	
else
	window.SESfullScreenApi.cancelFullScreen(document.body);
}

if (window.SESfullScreenApi.supportsFullScreen) {
	document.addEventListener(SESfullScreenApi.fullScreenEventName, function() {
		if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
		if (SESfullScreenApi.isFullScreen()) {
			is_fullscreen = 1;
			sesJqueryObject('#ses_media_lightbox_container').addClass('fullscreen');
			sesJqueryObject('.sesalbum_toogle_screen').css('backgroundPosition','-44px 0');
			if(sesJqueryObject('#pswp__button--info-show-lightbox').hasClass('active')){
				sesJqueryObject("#pswp__button--info-show-lightbox").removeClass('active');
				sesJqueryObject("#pswp__scroll-wrap").removeClass('pswp_info_panel_open');
				sesJqueryObject("#pswp__scroll-wrap").addClass('pswp_info_panel_close');
				setTimeout(function(){ gallery.updateSize(true); }, 510);
			}
			sesJqueryObject('.pswp__button--close').hide();
		} else {
			sesJqueryObject('.sesalbum_toogle_screen').css('backgroundPosition','0 0');
			sesJqueryObject('.ses_media_lightbox_slideshow_options').hide();
			sesJqueryObject('.pswp__ui > .pswp__top-bar').show();
			sesJqueryObject('#nextprevbttn').show();
			slideshow = false;
			sesJqueryObject('#sesalbum_slideshow_playpause').find('i').removeClass('fa-pause');
			sesJqueryObject('#sesalbum_slideshow_playpause').find('i').addClass('fa-play');
			sesJqueryObject('#sesalbum_slideshow_playpause').find('span').html(en4.core.language.translate('Play'));
			is_fullscreen = 0;
			clearInterval(slideShowInterval);
			sesJqueryObject('.pswp__button--close').show();
			sesJqueryObject('#ses_media_lightbox_container').removeClass('fullscreen');
		}
		if(typeof slideshow != 'undefined' && slideshow){
				sesJqueryObject('.ses_media_lightbox_slideshow_options').show();
				sesJqueryObject('.pswp__ui > .pswp__top-bar').hide();
				sesJqueryObject('#nextprevbttn').hide();
		}
	}, true);
} else {
	sesJqueryObject('#fsbutton').hide();
}
//Key Events
sesJqueryObject(document).on('keyup', function (e) {
		if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA')
				return true;
		if(!sesJqueryObject('#ses_media_lightbox_container').length){
			return false;
		}
		e.preventDefault();
		//Next Img On Right Arrow Click
		if (e.keyCode === 39) { 
			NextImageViewerPhoto();return false; 
		}
		// like code
		if (e.keyCode === 76) {
			sesJqueryObject('#sesLightboxLikeUnlikeButton').trigger('click');
		}
		// favourite code
		if (e.keyCode === 70) {
			if(sesJqueryObject('#sesalbum_favourite').length > 0)
				sesJqueryObject('#sesalbum_favourite').trigger('click');
		}
		//Prev Img on Left Arrow Click
		if (e.keyCode === 37) { 
			PrevImageViewerPhoto(); return false;
		}
});
function NextImageViewerPhoto(){
	if(sesJqueryObject('.pswp').attr('aria-hidden') == 'true'){
			return false;;	
	}
	if(sesJqueryObject('#nav-btn-next').length){
			document.getElementById('nav-btn-next').click();
	}else if(sesJqueryObject('#last-element-btn').length){
			sesJqueryObject('#last-element-btn').click();
	}
	return false;
}
function PrevImageViewerPhoto(){
	if(sesJqueryObject('.pswp').attr('aria-hidden') == 'true'){
			return false;
	}
	if(sesJqueryObject('#nav-btn-prev').length){
		document.getElementById('nav-btn-prev').click();
	}else if(sesJqueryObject('#first-element-btn').length){
			document.getElementById('show-all-photo-container').click();
	}
	return false;
}
function ajax_download(url) {
    var $iframe,
        iframe_doc,
        iframe_html;
    if (($iframe = sesJqueryObject('#download_iframe')).length === 0) {
        $iframe = sesJqueryObject("<iframe id='download_iframe'" +
                    " style='display: none' src='about:blank'></iframe>"
                   ).appendTo("body");
    }
    iframe_doc = $iframe[0].contentWindow || $iframe[0].contentDocument;
    if (iframe_doc.document) {
        iframe_doc = iframe_doc.document;
    }
    iframe_html = "<html><head></head><body><form method='POST' action='" +
                  url +"'>"
        iframe_html +="</form></body></html>";
    iframe_doc.open();
    iframe_doc.write(iframe_html);
    sesJqueryObject(iframe_doc).find('form').submit();
}
sesJqueryObject(document).on("click", ".ses-album-photo-download", function (e) {
	e.preventDefault();
  ajax_download(sesJqueryObject(this).prop('href'));
});
sesJqueryObject(document).on('click','.ses-image-viewer',function(e){
		e.preventDefault();
		return false;
});
sesJqueryObject(document).on('click','#show-all-photo-container',function(){
	if(sesJqueryObject(this).hasClass('active')){
		sesJqueryObject(this).removeClass('active');
		sesJqueryObject('#all_photo_container').css('display','none');
	}else{
		sesJqueryObject(this).addClass('active');
		sesJqueryObject('#all_photo_container').css('display','block');
	}
});
sesJqueryObject(document).on('click','#ses_media_lightbox_all_photo_id > a',function(){
		sesJqueryObject('#all_photo_container').css('display','none');
		sesJqueryObject('#show-all-photo-container').removeClass('active');
		if(sesJqueryObject('#close-all-photos').length>0)
			sesJqueryObject('#close-all-photos').removeClass('active');
});
sesJqueryObject(document).on('click','.ses_ml_more_popup_a_list > a , .ses_ml_more_popup_bc > a',function(){
		sesJqueryObject('#last-element-content').removeClass('active');
		sesJqueryObject('#last-element-content').css('display','none');
		sesJqueryObject('#ses_ml_photos_panel_wrapper').html('');
});
sesJqueryObject(document).on('click','#morepopup_bkbtn_btn',function(){
	sesJqueryObject('.ses_ml_photos_panel_content').find('div').find('a').eq(0).click();
});
sesJqueryObject(document).click(function(event){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	if((event.target.id != 'close-all-photos' && event.target.id != 'a_btn_btn') && event.target.id != 'last-element-btn' && (event.target.id != 'morepopup_closebtn' && event.target.id != 'morepopup_closebtn_btn')){
		sesJqueryObject('#all_photo_container').css('display','none');
		sesJqueryObject('#show-all-photo-container').removeClass('active');
		sesJqueryObject('#last-element-content').css('display','none');
		sesJqueryObject('#last-element-content').removeClass('active');
		if(sesJqueryObject('#close-all-photos').length>0)
			sesJqueryObject('#close-all-photos').removeClass('active');
	}
	if(event.target.id == 'a_btn_btn' || event.target.id == 'show-all-photo-container' || event.target.id == 'close-all-photos' || event.target.id == 'first-element-btn'){
			if(sesJqueryObject('#close-all-photos').hasClass('active')){
				sesJqueryObject('#close-all-photos').removeClass('active');
				sesJqueryObject('#all_photo_container').css('display','none');
				sesJqueryObject('#show-all-photo-container').removeClass('active');
			}else{
				sesJqueryObject('#close-all-photos').addClass('active');
				sesJqueryObject('#show-all-photo-container').addClass('active');
				sesJqueryObject('#all_photo_container').css('display','block');
			}	
	}else	if(event.target.id == 'morepopup_closebtn' || event.target.id == 'morepopup_closebtn_btn' || event.target.id == 'last-element-btn'){
		if(sesJqueryObject('#last-element-content').hasClass('active')){
			sesJqueryObject('#last-element-content').removeClass('active');
			sesJqueryObject('#last-element-content').css('display','none');
		}else{
			sesJqueryObject('#last-element-content').addClass('active');
			sesJqueryObject('#last-element-content').css('display','block');
		}
	}
	if((event.target.id != 'overlay-model-class' && event.target.id != 'overlay-model-class-down') && sesJqueryObject('#overlay-model-class').hasClass('active')){
		if(sesJqueryObject('#overlay-model-class').hasClass('active')){
			sesJqueryObject('#overlay-model-class').removeClass('active');
			sesJqueryObject('.pswp__top-bar-more-tooltip').css('display','none');
			sesJqueryObject(".overlay-model-class").css('display','none');
		}else{
			sesJqueryObject('#overlay-model-class').addClass('active');
			sesJqueryObject('.pswp__top-bar-more-tooltip').css('display','block');
			sesJqueryObject(".overlay-model-class").css('display','block');
		}	
	}
});
var changeDotCounter;
sesJqueryObject(document).on('click','#last-element-btn',function(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	sesJqueryObject('#last-element-content').css('display','block');
	sesJqueryObject('#last-element-content').addClass('active');
	if(!sesJqueryObject('#content_last_element_lightbox').hasClass('active')){
			sesJqueryObject('#content_last_element_lightbox').html('<div class="ses_ml_more_popup_loading_txt">'+en4.core.language.translate("Wait,there's more")+'<span id="1-dot" style="display:none">.</span><span id="2-dot" style="display:none">.</span><span id="3-dot" style="display:none">.</span></div>');
	var changeDotCounter = setInterval(makeDotMove, 500);
			getlastElementData();
	}
	return false;
});
function getlastElementData(){
	if(document.URL.search('chanel_id') == -1)
		var URL = en4.core.baseUrl+'albums/photo/last-element-data/';
	else
		var URL = en4.core.baseUrl+'sesvideo/chanel/last-element-data/';
	imageViewerGetLastElem = new Request.HTML({
      url :URL,
      data : {
        format : 'html',
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {
				sesJqueryObject('#content_last_element_lightbox').html(responseHTML);
				sesJqueryObject('#content_last_element_lightbox').addClass('active');
				clearTimeout(changeDotCounter);
				return true;
      }
		}); 
		en4.core.request.send(imageViewerGetLastElem, {
			'force': true
		});	
}
function makeDotMove(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	if(sesJqueryObject('#1-dot').css('display') == 'none')
		sesJqueryObject('#1-dot').show();
	else if(sesJqueryObject('#2-dot').css('display') == 'none')
		sesJqueryObject('#2-dot').show();
	else if(sesJqueryObject('#3-dot').css('display') == 'none')
		sesJqueryObject('#3-dot').show();
	else{
		sesJqueryObject('#1-dot').hide();
		sesJqueryObject('#2-dot').hide();
		sesJqueryObject('#3-dot').hide();	
	}
}
function getAllPhoto(requestURL,sesModuleData){
 if(typeof sesModuleData == 'undefined'){
  requestPhotoSesalbumURL = requestURL.replace('image-viewer-detail','all-photos');
	url = '';
 }else{
	url = requestURL;
 	requestPhotoSesalbumURL = en4.core.baseUrl+'sesbasic/index/allphoto-ses-compatibility-code/';
 }
	imageViewerGetRequest = new Request.HTML({
      url :requestPhotoSesalbumURL,
      data : {
        format : 'html',
				url:url,
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {
					sesJqueryObject('#all_photo_container').html(responseHTML);
				if( !sesJqueryObject.trim( sesJqueryObject('#ses_media_lightbox_all_photo_id').html() ).length ) 
					sesJqueryObject('#show-all-photo-container').hide();
				else
					sesJqueryObject('#show-all-photo-container').show();
					var photo_id = sesJqueryObject('#sesalbum_photo_id_data_src').attr('data-src');
					sesJqueryObject('#all-photo-container').slimscroll({
					 height: 'auto',
					 alwaysVisible :true,
					 color :'#ffffff',
					 railOpacity :'0.5',
					 disableFadeOut :true,					 
					});
					 sesJqueryObject('#all-photo-container').slimScroll().bind('slimscroll', function(event, pos){
						if(canPaginateAllPhoto == '1' && pos == 'bottom') {
							 sesphotolightbox_123();
						}
        });
				if(photo_id){
					sesJqueryObject(document).removeClass('currentthumb');
					sesJqueryObject('#photo-lightbox-id-'+photo_id).addClass('currentthumb');
		 		}
					return true;
      }
			}); 
			en4.core.request.send(imageViewerGetRequest, {
				'force': true
			});	
}
sesJqueryObject(document).on('click','#first-element-btn',function(){
	if(!sesJqueryObject('#ses_media_lightbox_container').length)
			return;
	document.getElementById('show-all-photo-container').click();
});
index = 0;
sesaIndex = 0;
function getImageViewerObjectData(imageURL,requestedURL,forceAllPhoto,firstPointObject,moduleData,sesModuleData){
		if(((index == 0 || typeof forceAllPhoto != 'undefined')) && sesaIndex == 0)
				getAllPhoto(requestedURL,sesModuleData);
		if(typeof sesModuleData != 'undefined'){
				var url = requestedURL;
				requestedURL = en4.core.baseUrl+'sesbasic/index/ses-compatibility-code/';
		}else
			var url = '';
		
		 imageViewerGetRequest = new Request.HTML({
      url :requestedURL,
      data : {
        format : 'html',
				url : url,
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {
					sesJqueryObject('#nextprevbttn').html(responseHTML);
					if(sesJqueryObject('#last-element-content').text() == ''  && moduleData != 'yes'){
						var setHtml = sesJqueryObject('#last-element-content').html(sesJqueryObject('#content-from-element').html());
					}
					if(sesJqueryObject('#media_photo_next_ses').find('#gallery-img').hasClass('ses-private-image')){
							sesJqueryObject('.pswp__top-bar-share').hide();
							sesJqueryObject('.pswp__top-bar-more').hide();
							sesJqueryObject('.pswp__top-bar-msg').hide();
					}else{
							sesJqueryObject('.pswp__top-bar-share').show();
							sesJqueryObject('.pswp__top-bar-more').show();
							sesJqueryObject('.pswp__top-bar-msg').show();
					}
					sesJqueryObject('#content-from-element').html('');
					sesJqueryObject('.pswp__top-bar').html(sesJqueryObject('#imageViewerId').html());
					sesJqueryObject('.pswp__preloader').removeClass('pswp__preloader--active');
					sesJqueryObject('.pswp__top-bar-action').css('display','block');
					if((feedPhoto && firstPointObject == 0) || sesJqueryObject('#media_photo_next_ses').find('#gallery-img').hasClass('ses-private-image')){
						var img = document.createElement('img');
							img.onload = function(){
								openPhotoSwipe(sesJqueryObject('#media_photo_next_ses').find('#gallery-img').attr('src'),this.width,this.height);
								sesJqueryObject('.image_show_pswp').attr('src',sesJqueryObject('#media_photo_next_ses').find('#gallery-img').attr('src'));
						}
						img.src = sesJqueryObject('#media_photo_next_ses').find('#gallery-img').attr('src');
						sesJqueryObject('#gallery-img').css('display','block');
						firstStartPoint = 1;
					}
					sesJqueryObject('#media_photo_next_ses').remove();
					sesJqueryObject('.image_show_pswp').wrap('<div id="media_photo_next_ses" >');
					var htmlInfo = sesJqueryObject('#ses_pswp_information').html();
					sesJqueryObject('#ses_pswp_information').html('');
					if(sesJqueryObject('.ses_pswp_information').length)
						sesJqueryObject('.ses_pswp_information').remove();
					sesJqueryObject( '<div class="ses_pswp_information">'+htmlInfo+'</div>' ).insertAfter( "#pswp__scroll-wrap" );
					var photo_id = sesJqueryObject('#sesalbum_photo_id_data_src').attr('data-src');
					if(moduleData != 'yes')
						sesJqueryObject('.currentthumb').removeClass('currentthumb');
					sesJqueryObject('#photo-lightbox-id-'+photo_id).addClass('currentthumb');
					/*if(sesJqueryObject('#map-canvas').length>0)
						initializeSesAlbumMap();*/
					sesJqueryObject('#heightOfImageViewerContent').css('height', sesJqueryObject('.ses_pswp_information').height()+'px');
					sesJqueryObject('#flexcroll').slimscroll({
					 height: 'auto',
					 start : sesJqueryObject('#ses_pswp_info'),
					});
					if( !sesJqueryObject.trim( sesJqueryObject('#ses_media_lightbox_all_photo_id').html() ).length ) 
						sesJqueryObject('#show-all-photo-container').hide();
					else
						sesJqueryObject('#show-all-photo-container').show();
					return true;
      }
			}); 
			en4.core.request.send(imageViewerGetRequest, {
				'force': true
			});
			index++;
			sesaIndex++;
			return ;
}
var slideShowInterval;
var speed = 6000;
var slideshow ;
function slideShow(){
		slideshow = true;
		toogle();
		sesJqueryObject('.ses_media_lightbox_slideshow_options').show();
		sesJqueryObject('.pswp__ui > .pswp__top-bar').hide();
		sesJqueryObject('#nextprevbttn').hide();
}
sesJqueryObject(document).on('click','#sesalbum_slideshow_playpause',function(){
		if(sesJqueryObject(this).find('i').hasClass('fa-play')){
			sesJqueryObject(this).find('i').addClass('fa-pause');
			sesJqueryObject(this).find('i').removeClass('fa-play');
			sesJqueryObject(this).find('span').html('Pause');
			slideShowInterval = setInterval(changePosition, speed);			
		}else{
			clearInterval(slideShowInterval);
			sesJqueryObject(this).find('i').removeClass('fa-pause');
			sesJqueryObject(this).find('i').addClass('fa-play');
			sesJqueryObject(this).find('span').html('Play');
		}
});
sesJqueryObject(document).on('click','#sesalbum_slideshow_stop',function(){
		slideshow = false;
		clearInterval(slideShowInterval);
		toogle();
});
function changePosition(){
 if(!sesJqueryObject('#last-element-btn').length){
		if(sesJqueryObject('#nav-btn-next').length){
			document.getElementById('nav-btn-next').click();
		}else if(sesLightbox){
			sesJqueryObject(this).find('i').removeClass('fa-pause');
			sesJqueryObject(this).find('i').addClass('fa-play');
			sesJqueryObject(this).find('span').html('Play');
			clearInterval(slideShowInterval);
			changeSlideshowOptions();
		}else{
			changePosition();
		}
 }else{
	 	sesJqueryObject('#last-element-btn').click();
		sesJqueryObject(this).find('i').removeClass('fa-pause');
		sesJqueryObject(this).find('i').addClass('fa-play');
		sesJqueryObject(this).find('span').html('Play');
		clearInterval(slideShowInterval);
		changeSlideshowOptions();
 }
}
function changeSlideshowOptions(){
	sesJqueryObject('.ses_media_lightbox_slideshow_options').hide();
	sesJqueryObject('.pswp__ui > .pswp__top-bar').show();
	sesJqueryObject('#nextprevbttn').show();
	slideshow = false;
	sesJqueryObject('#sesalbum_slideshow_playpause').find('i').removeClass('fa-pause');
	sesJqueryObject('#sesalbum_slideshow_playpause').find('i').addClass('fa-play');
	sesJqueryObject('#sesalbum_slideshow_playpause').find('span').html(en4.core.language.translate('Play'));
}
sesJqueryObject(document).on('click','#editBDetailsLink',function(e){
		e.preventDefault();
		sesJqueryObject('#titleSes').val(trim(sesJqueryObject('#ses_title_get').html(),' '));
		sesJqueryObject('#descriptionSes').val(trim(sesJqueryObject('#ses_title_description').html(),' '));
	if(sesJqueryObject('#locationSes').length >0){
		sesJqueryObject('#locationSes').val(trim(sesJqueryObject('#ses_location_data').html()));
		mapLoad = false;
		if(sesJqueryObject('#map-canvas').length)
			sesJqueryObject('#map-canvas').remove();
		initializeSesAlbumMapList();
		//if(sesJqueryObject('#ses_location_data').html())
				//editSetMarkerOnMap();
	 //google.maps.event.trigger(map, 'resize');
	}
		sesJqueryObject('#editDetailsForm').css('display','block');
		sesJqueryObject('#ses_pswp_info').css('display','none');
});
sesJqueryObject(document).on('click','#cancelBDetailsSes',function(e){
		e.preventDefault();
		sesJqueryObject('#editDetailsForm').css('display','none');
		sesJqueryObject('#ses_pswp_info').css('display','block');
});
sesJqueryObject(document).on('click','#saveBDetailsChanelSes',function(e){
	e.preventDefault();
	var thisObject = this;
	sesJqueryObject(thisObject).prop("disabled",true);
	var photo_id = sesJqueryObject('#photo_id_ses').val();
	var formData =  sesJqueryObject("#changeBPhotoDetails").serializeArray();
	sesJqueryObject.ajax({  
    type: "POST",  
    url: en4.core.baseUrl+'sesbasic/index/edit-detail/photo_id/'+photo_id,  
    data: formData,  
    success: function(response) {
      var data = JSON.parse(response);
			if(data.status && !data.error){
				sesJqueryObject(thisObject).prop("disabled",false);
				sesJqueryObject('#ses_title_get').html(sesJqueryObject('#titleSes').val());
				sesJqueryObject('#ses_title_description').html(sesJqueryObject('#descriptionSes').val());
				sesJqueryObject('#editDetailsForm').css('display','none')
				sesJqueryObject('#ses_pswp_info').css('display','block');
				return false;
			}else{
				alert(en4.core.language.translate('Something went wrong,try again later.'));	
				return false;
			}
    }
});
	return false;
});
sesLightbox = false;
feedPhoto = false;
function openLightBoxForSesPlugins(href,imageURL){
	var manageData = '';
	if(typeof imageURL == 'undefined'){
		feedPhoto = true;
		firstPointObject = 0;
		sesLightbox = true;
		var imageURL = en4.core.baseUrl+'application/modules/Sesbasic/externals/images/loading.gif';
		manageData = 'yes';
	}
	getRequestedAlbumPhotoForImageViewer(imageURL,href,'',manageData,'','yes');
	return false;
}
