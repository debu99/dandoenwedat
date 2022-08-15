/* $Id: sesalbumimageviewerbasic.js  2015-6-16 00:00:000 SocialEngineSolutions $ */
var dataCommentSes = '';
	// store the default browser URL for change state after closing image viewer
	var defaultHashURL = '';
	defaultHashURL = document.URL;
	var firstStartPoint = 0;
	firstStartPointModule = 0;
	var getTagData;
	var mediaTags ;
  
  var sesCustomPhotoURL = false;
  
var offsetY = window.pageYOffset;
function makeLayoutForImageViewer(){
	if(sesJqueryObject('#ses_media_lightbox_container').length)
		return;
	sesJqueryObject('<div id="ses_media_lightbox_container" class="ses_media_lightbox_container"><div class="ses_media_lightbox_overlay" id="crossSes"></div> <div class="ses_media_lightbox_content"> <div class="ses_media_lightbox_left"><div class="ses_media_lightbox_item_wrapper"><div class="ses_media_lightbox_item"><img id="gallery-img" src="" alt="" /></div></div> <div class="ses_media_lightbox_nav_btns"><a id="nav-btn-next" style="display:none" class="ses_media_lightbox_nav_btn_next" ></a><a id="nav-btn-prev" class="ses_media_lightbox_nav_btn_prev" style="display:none;" ></a></div> </div><div class="ses_media_lightbox_information"></div><a href="javascript:;" id="fsbutton"  class="cross ses_media_lightbox_close_btn"><i></i></a></div></div>').appendTo('body');	
}
function getRequestedAlbumPhotoForImageViewer(imageURL,requestedURL,manageData){
	if(openPhotoInLightBoxSesalbum == 0){
		window.location.href = requestedURL.replace('image-viewer-detail','view');
		return;
	}
	makeLayoutForImageViewer();
  if(firstStartPoint == 0){
    offsetY = window.pageYOffset;
    sesJqueryObject('html').css('position','fixed').css('width','100%').css('overflow','hidden');
    sesJqueryObject('html').css('top', -offsetY + 'px');
  }
	sesJqueryObject('#ses_media_lightbox_container').show();
	sesJqueryObject('body').css({ 'overflow': 'hidden' });
 //check function call from image viewer or direct
 if(!dataCommentSes){
		dataCommentSes = sesJqueryObject('.layout_core_comments').html();
		getTagData = sesJqueryObject('#media_photo_div').find('*[id^="tag_"]');
		sesJqueryObject('#media_photo_div').find('*[id^="tag_"]').remove();
		mediaTags =	sesJqueryObject('#media_tags').html();
		sesJqueryObject('#media_tags').html('');
	}
	sesJqueryObject('.layout_core_comments').html('');
	history.pushState(null, null, requestedURL.replace('image-viewer-detail','view'));
	
	var height = sesJqueryObject('.ses_media_lightbox_content').height();
	var width = sesJqueryObject('.ses_media_lightbox_left').width();
	sesJqueryObject('#media_photo_next_ses').css('height',height+'px');
	//sesJqueryObject('#mainImageContainer').css('height',height+'px');
	sesJqueryObject('#gallery-img').css('max-height',height+'px');
	sesJqueryObject('#gallery-img').css('max-width',width+'px');
	//sesJqueryObject('#heightOfImageViewerContent').css('height', sesJqueryObject('.ses_media_lightbox_content').height()+'px');
	sesJqueryObject('#gallery-img').attr('src',imageURL);
	sesJqueryObject('.ses_media_lightbox_information').html('');
	sesJqueryObject('.ses_media_lightbox_options').remove();
	sesJqueryObject('#nav-btn-prev').hide();
	sesJqueryObject('.ses_media_lightbox_nav_btn_next').css('display','none');
	getImageViewerObjectData(imageURL,requestedURL,manageData);	
}
if(typeof sesalbuminstall != 'undefined' && sesalbuminstall == 1) {
// other module open in popup viewer code
sesJqueryObject(document).on("click", ".thumbs_photo", function (e) {
	var requestedURL = sesJqueryObject(this).attr('href');
	if(typeof sesJqueryObject(this).attr('onclick') != 'undefined')
		return;
	// check for view module pages images
	if ((requestedURL.indexOf("event_id") === -1 && requestedURL.indexOf("group_id") === -1 ) || requestedURL.indexOf("photo_id") === -1 ){
			return true;
	}
	if(openPhotoInLightBoxSesalbum == 0 || (openGroupPhotoInLightBoxSesalbum == 0 && requestedURL.indexOf("group_id") > -1 ) || (openEventPhotoInLightBoxSesalbum == 0 && requestedURL.indexOf("event_id") > -1)){
		window.location.href = requestedURL;
		return;
	}
		e.preventDefault();
	
		if(requestedURL){
			sesJqueryObject('#ses_media_lightbox_container').show();
			sesJqueryObject('body').css({ 'overflow': 'hidden' });
			history.pushState(null, null, hashTagURL);
			openLightBoxForSesPlugins(requestedURL);
		}
});

//message photo popup
sesJqueryObject(document).on('click','.message_attachment_info',function(e){
		e.preventDefault();
		feedPhoto = true;
		var imageObject = sesJqueryObject(this).find('div').find('a');
		var getImageHref = imageObject.attr('href');
		if(getImageHref.search('album_id') == -1 || getImageHref.search('photo_id') == -1){
			window.location.href = getImageHref;
			return;
		}
		var imageSource = sesJqueryObject(this).parent().find('.message_attachment_photo').find('img').attr('src');
		if(!imageSource){
			window.location.href = getImageHref;
			return;
		}
		if(openPhotoInLightBoxSesalbum == 0 ){
			window.location.href = getImageHref;
			return;
		}
		getImageHref = getImageHref.replace('view','image-viewer-detail');
		getRequestedAlbumPhotoForImageViewer(imageSource,getImageHref);
});
// activity feed image popup
sesJqueryObject(document).on("click", '.feed_attachment_album_photo', function (e) {
	e.preventDefault();
  if(sesJqueryObject(this).find('div').hasClass('sesadvancedactivity_buysell'))
    return;
	var imageObject = sesJqueryObject(this).find('div').find('a');
  var getImageHref = imageObject.attr('href');
	var imageSource = imageObject.find('img').attr('src');
	getImageHref = getImageHref.replace('view','image-viewer-detail');
  if(openPhotoInLightBoxSesalbum == 0 || getImageHref.indexOf('photo_id') < 0){
		window.location.href = getImageHref;
	}
	if(typeof imageSource == 'undefined')
		imageSource = en4.core.baseUrl+'application/modules/Sesbasic/externals/images/loading.gif';
	getRequestedAlbumPhotoForImageViewer(imageSource,getImageHref);
});
}
//Close image viewer
sesJqueryObject(document).on('click','.ses_media_lightbox_overlay, #crossSes, .cross',function (e) {
	if(sesJqueryObject('#ses_media_lightbox_container').css('display') != 'none'){
		sesJqueryObject('body').css({ 'overflow': 'initial' });
    sesJqueryObject('html').css('position','auto').css('overflow','auto');
    sesJqueryObject(window).scrollTop(offsetY);
    if(sesJqueryObject('.emoji_content').css('display') == 'block')
      sesJqueryObject('.exit_emoji_btn').click();
		history.pushState(null, null, defaultHashURL);
		sesJqueryObject('.layout_core_comments').html(dataCommentSes);
		clearInterval(slideShowInterval);
		e.preventDefault();
		firstStartPoint = 0;
		sesLightbox = false;
		sesaIndex=0;
		dataCommentSes = '';
		firstStartPointModule = 0;
		sesJqueryObject('#media_photo_next').after(getTagData);
		sesJqueryObject('#media_tags').html(mediaTags);		
		mediaTags = '';
		getTagData = '';
    sesCustomPhotoURL = false;
	}
	sesJqueryObject('#ses_media_lightbox_container').remove();
	sesJqueryObject('#ses_media_lightbox_container_video').remove();
});
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
if(is_fullscreen == 0)
	window.SESfullScreenApi.requestFullScreen(document.body);	
else
	window.SESfullScreenApi.cancelFullScreen(document.body);
}
if (window.SESfullScreenApi.supportsFullScreen) {	
	document.addEventListener(SESfullScreenApi.fullScreenEventName, function() {
		if (SESfullScreenApi.isFullScreen()) {
			is_fullscreen = 1;
			sesJqueryObject('.sesalbum_toogle_screen').css('backgroundPosition','-44px 0');
			sesJqueryObject('#ses_media_lightbox_container').addClass('fullscreen');
			var height = sesJqueryObject('.ses_media_lightbox_content').height();
			var width = sesJqueryObject('.ses_media_lightbox_left').width();
			sesJqueryObject('#media_photo_next_ses').css('height',height+'px');
			sesJqueryObject('#gallery-img').css('max-height',height+'px');
			sesJqueryObject('#gallery-img').css('max-width',width+'px');
			sesJqueryObject('#heightOfImageViewerContent').css('height', sesJqueryObject('.ses_media_lightbox_content').height()+'px');
			sesJqueryObject('#flexcroll').slimscroll({
			 height: 'auto',
			 start : sesJqueryObject('#ses_media_lightbox_media_info'),
			});
			sesJqueryObject('#gallery-img').attr('src',sesJqueryObject('#image-src-sesalbum-lightbox-hidden').html());
			window.wheelzoom(document.querySelector('#gallery-img'));
		} else {
			is_fullscreen = 0;
			clearInterval(slideShowInterval);
			sesJqueryObject('.sesalbum_toogle_screen').css('backgroundPosition','0 0');
			sesJqueryObject('#ses_media_lightbox_container').removeClass('fullscreen');
			var height = sesJqueryObject('.ses_media_lightbox_content').height();
			var width = sesJqueryObject('.ses_media_lightbox_left').width();
			sesJqueryObject('#media_photo_next_ses').css('height',height+'px');
			sesJqueryObject('#gallery-img').css('max-height',height+'px');
			sesJqueryObject('#gallery-img').css('max-width',width+'px');
			sesJqueryObject('#heightOfImageViewerContent').css('height', sesJqueryObject('.ses_media_lightbox_content').height()+'px');
			sesJqueryObject('#flexcroll').slimscroll({
			 height: 'auto',
			 start : sesJqueryObject('#ses_media_lightbox_media_info'),
			});
			sesJqueryObject('#gallery-img').attr('src',sesJqueryObject('#image-src-sesalbum-lightbox-hidden').html());
			window.wheelzoom(document.querySelector('#gallery-img'));
		}
	}, true);
} else {
	sesJqueryObject('#fsbutton').hide();
}

//Key Events
sesJqueryObject(document).on('keyup', function (e) {
		
		if(!sesJqueryObject('#ses_media_lightbox_container').length){
			return false;
		}
		e.preventDefault();
		//Close popup on esc
		if (e.keyCode === 27) { document.getElementById('crossSes').click();return false; }
		//Next Img On Right Arrow Click
		if (e.keyCode === 39) { 
			if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA')
				return;
			NextImageViewerPhoto();return false; 
		}
		// like code
		if (e.keyCode === 76) {
			if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA')
				return;
			sesJqueryObject('#sesLightboxLikeUnlikeButton').trigger('click');
		}
		// favourite code
		if (e.keyCode === 70) {
			if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA')
				return;
			sesJqueryObject('#sesalbum_favourite').trigger('click');
		}
		//Prev Img on Left Arrow Click
		if (e.keyCode === 37) { 
			if(sesJqueryObject('#'+e.target.id).prop('tagName') == 'INPUT' || sesJqueryObject('#'+e.target.id).prop('tagName') == 'TEXTAREA')
				return;
			PrevImageViewerPhoto(); return false;
		}
});
function checkThirdPartyModules(imageURL){
	if(imageURL){
		if (imageURL.indexOf("event_id") === -1 && imageURL.indexOf("group_id") === -1){
			return true;
		}else{
			return false;
		}
	}
		return false;
}
function NextImageViewerPhoto(){
	if(sesJqueryObject('#ses_media_lightbox_container').css('display') == 'none'){
			return false;;	
	}
	if(sesJqueryObject('#nav-btn-next').length){
			document.getElementById('nav-btn-next').click();
	}
	return false;
}
function PrevImageViewerPhoto(){
	if(sesJqueryObject('#ses_media_lightbox_container').css('display') == 'none'){
			return false;;	
	}
	if(sesJqueryObject('#nav-btn-prev').length){
		document.getElementById('nav-btn-prev').click();
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
		sesJqueryObject('.layout_core_comments').html('');	
		e.preventDefault();
		return false;
});
function getImageViewerObjectData(imageURL,requestedURL,manageData){
		if(sesJqueryObject('.ses_media_lightbox_information').children().length == 0){
			sesJqueryObject('.ses_media_lightbox_information').hide();
		}
		if(typeof manageData != 'undefined'){
			var url = requestedURL;
			if(sesCustomPhotoURL) {
			requestedURL = en4.core.baseUrl+'sesbasic/lightbox/image-viewer-detail/';
			} else { 
			requestedURL = en4.core.baseUrl+'albums/photo/ses-compatibility-code/';
			}
		} else
				var url = '';
		imageViewerGetRequest = new Request.HTML({
		url :requestedURL,
		data : {
			format : 'html',
					url:url,
		},
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {	
					 let isValidHtml = true; 
					 try {
						  let response = JSON.parse(responseHTML);
						  if(response.status == false) isValidHtml = false;
					  }
					  catch(e) {
						if(responseHTML == null) isValidHtml = false;
					  }
					if(isValidHtml) {
						sesJqueryObject('.ses_media_lightbox_content').html('');
						sesJqueryObject('.ses_media_lightbox_content').html(responseHTML);
					}
					var height = sesJqueryObject('.ses_media_lightbox_content').height();
					var width = sesJqueryObject('.ses_media_lightbox_left').width();
					sesJqueryObject('#media_photo_next_ses').css('height',height+'px');
					//sesJqueryObject('#mainImageContainer').css('height',height+'px');
					if(sesJqueryObject('#media_photo_next_ses').find('#gallery-img').hasClass('ses-private-image')){
							sesJqueryObject('.ses_media_lightbox_options_btns').hide();
							sesJqueryObject('.ses_media_lightbox_tag_btn').hide();
							sesJqueryObject('.ses_media_lightbox_share_btn').hide();
							sesJqueryObject('.ses_media_lightbox_more_btn').hide();
					}else	if(sesJqueryObject('#media_photo_next_ses').find('#gallery-img').hasClass('ses-blocked-album')){
							sesJqueryObject('.ses_media_lightbox_information').hide();
							sespromptPasswordCheck();
					}else{
							sesJqueryObject('.ses_media_lightbox_options_btns').show();
							sesJqueryObject('.ses_media_lightbox_tag_btn').show();
							sesJqueryObject('.ses_media_lightbox_share_btn').show();
							sesJqueryObject('.ses_media_lightbox_more_btn').show();
					}
					sesJqueryObject('#gallery-img').css('max-height',height+'px');
					sesJqueryObject('#gallery-img').css('max-width',width+'px');
					sesJqueryObject('#heightOfImageViewerContent').css('height', sesJqueryObject('.ses_media_lightbox_content').height()+'px');
					sesJqueryObject('#flexcroll').slimscroll({
					 height: 'auto',
					 start : sesJqueryObject('#ses_media_lightbox_media_info'),
					});
					window.wheelzoom(document.querySelector('#gallery-img'));
					// initImage();
					/*if(sesJqueryObject('#map-canvas').length>0 && !map)
						initializeSesAlbumMap();*/
					return true;
      }
			}); 
			en4.core.request.send(imageViewerGetRequest, {
				'force': true
			});
}
var slideShowInterval;
var speed = 6000;
function slideShow(){
	if(!sesJqueryObject('#ses_media_lightbox_container').hasClass('fullscreen'))
		toogle();
		slideShowInterval = setInterval(function(){changePositionPhoto();}, speed);
}
function changePositionPhoto() {
	if(sesJqueryObject('#nav-btn-next').length){
		document.getElementById('nav-btn-next').click();
	}else{
		toogle();
		clearInterval(slideShowInterval);
	}
}

sesJqueryObject(document).on('click','#editDetailsLink',function(e){
		e.preventDefault();
		sesJqueryObject('#titleSes').val(sesJqueryObject('#ses_title_get').html());
		sesJqueryObject('#descriptionSes').val(sesJqueryObject('#ses_title_description').html());
		sesJqueryObject('#editDetailsForm').css('display','block');
		sesJqueryObject('#ses_media_lightbox_media_info').css('display','none');
});


sesJqueryObject(document).on('click','#cancelDetailsSes',function(e){
  e.preventDefault();
  sesJqueryObject('#editDetailsForm').css('display','none');
  sesJqueryObject('#ses_media_lightbox_media_info').css('display','block');
});

sesJqueryObject(document).on('click','#saveDetailsSes',function(e){
	e.preventDefault();
	var thisObject = this;
	sesJqueryObject(thisObject).prop("disabled",true);
  var photo_type_ses = sesJqueryObject('#photo_type_ses').val();
	var photo_id = sesJqueryObject('#photo_id_ses').val();
	var album_id = sesJqueryObject('#album_id_ses').val();
	var formData =  sesJqueryObject("#changePhotoDetails").serializeArray();
  if(sesCustomPhotoURL) {
    var URL = en4.core.baseUrl+'sesbasic/lightbox/edit-detail/album_id/'+album_id+'/item_id/'+photo_id+'/item_type/'+photo_type_ses;
  } else {
    var URL = en4.core.baseUrl+'albums/photo/edit-detail/album_id/'+album_id+'/photo_id/'+photo_id;
  }
  
	sesJqueryObject.ajax({  
    type: "POST",
    url: URL,
    data: formData,  
    success: function(response) {  
      var data = JSON.parse(response);
			if(data.status && !data.error){
				sesJqueryObject(thisObject).prop("disabled",false);
				sesJqueryObject('#ses_title_get').html(sesJqueryObject('#titleSes').val());
				sesJqueryObject('#ses_title_description').html(sesJqueryObject('#descriptionSes').val());
				sesJqueryObject('#ses_location_data').html(sesJqueryObject('#locationSes').val());
				sesJqueryObject('#editDetailsForm').css('display','none')
				sesJqueryObject('#ses_media_lightbox_media_info').css('display','block');
			if(sesJqueryObject('#locationSes').val() != '')
				sesJqueryObject('#seslocationIn').html('In');
			else
				sesJqueryObject('#seslocationIn').html('');
				return false;
			}else{
				alert(en4.core.language.translate('Something went wrong,try again later.'));	
				return false;
			}
    }
});
	return false;
});

// sesJqueryObject(document).on('click','#changeSesPhotoDetails',function(e){
// 	e.preventDefault();
// 	var thisObject = this;
// 	sesJqueryObject(thisObject).prop("disabled",true);
// 	var formData =  sesJqueryObject("#changePhotoDetails").serializeArray();
// 	sesJqueryObject.ajax({  
//     type: "POST",  
//     url: en4.core.baseUrl+'sesalbum/photo/change-sesdetail/',  
//     data: formData,  
//     success: function(response) {  
//       var data = JSON.parse(response);
// 			if(data.status && !data.error){
// 				sesJqueryObject(thisObject).prop("disabled",false);
// 				sesJqueryObject('#ses_title_get').html(sesJqueryObject('#titleSes').val());
// 				sesJqueryObject('#ses_title_description').html(sesJqueryObject('#descriptionSes').val());
// 				sesJqueryObject('#editDetailsForm').css('display','none')
// 				sesJqueryObject('#ses_media_lightbox_media_info').css('display','block');
// 				return false;
// 			}else{
// 				alert(en4.core.language.translate('Something went wrong,try again later.'));	
// 				return false;
// 			}
//     }
// });
// 	return false;
// });

sesJqueryObject(document).on('click','#comments .comments_options a',function(event) {
	var thisObject = this;
	var htmlOnclick = sesJqueryObject(this).attr('onclick');
	if(typeof htmlOnclick != 'undefined' && htmlOnclick.search('comments') != -1 && sesJqueryObject('.sesalbum_othermodule_like_button').length){
			if(sesJqueryObject('.sesalbum_othermodule_like_button').hasClass('button_active')){
				sesJqueryObject('.sesalbum_othermodule_like_button').removeClass('button_active');
				showTooltip(10,10,'<i class="fa fa-thumbs-up"></i><span>'+en4.core.language.translate("Photo removed from like successfully")+'</span>');
				return;
			}else{
				sesJqueryObject('.sesalbum_othermodule_like_button').addClass('button_active');
				showTooltip(10,10,'<i class="fa fa-thumbs-up"></i><span>'+en4.core.language.translate("Photo like successfully")+'</span>', 'sesbasic_liked_notification');
				return ;
			}	
	}
		var currentURL = window.location.href;
	if(currentURL.search('video_id') != -1)
			var itemType = 'Chanel';
	else if(currentURL.search('chanel_id') != -1)
			var itemType = 'Chanel Photo';
	else if(htmlOnclick.search('album') == -1)
		return true;
	
	if(htmlOnclick.search('comments') != -1){
		// unlike code
		if(currentURL.search('album_id') != -1)
			var itemType = 'Photo';
		else if(currentURL.search('video_id') != -1)
			var itemType = 'Chanel';
		else if(currentURL.search('chanel_id') != -1)
			var itemType = 'Chanel Photo';
		else
			var itemType = 'Album';
		if(htmlOnclick.search('unlike') != -1){
		 if(sesJqueryObject('#ses_media_lightbox_container').css('display') == 'block'){
		 	sesJqueryObject('#sesLightboxLikeUnlikeButton').removeClass('button_active');
			sesJqueryObject('#sesLightboxLikeUnlikeButton').find('#like_unlike_count').html(parseInt(sesJqueryObject('#sesLightboxLikeUnlikeButton').find('#like_unlike_count').html())-1);
		 }else{
		 	sesJqueryObject('#sesLikeUnlikeButton').removeClass('button_active');
		 }
		 showTooltip(10,10,'<i class="fa fa-thumbs-up"></i><span>'+en4.core.language.translate(itemType+" removed from like successfully")+'</span>');
		}else{
			if(sesJqueryObject('#ses_media_lightbox_container').css('display') == 'block'){
		 		sesJqueryObject('#sesLightboxLikeUnlikeButton').addClass('button_active');
				sesJqueryObject('#sesLightboxLikeUnlikeButton').find('#like_unlike_count').html(parseInt(sesJqueryObject('#sesLightboxLikeUnlikeButton').find('#like_unlike_count').html())+1);
			}else{
				sesJqueryObject('#sesLikeUnlikeButton').addClass('button_active');
			}
			showTooltip(10,10,'<i class="fa fa-thumbs-up"></i><span>'+en4.core.language.translate(itemType+" Like Successfully")+'</span>', 'sesbasic_liked_notification');
		}
	}
});

//compatability code for SES
sesJqueryObject(document).ready(function(){
  if(typeof sesshowShowInfomation != "undefined"){
    //override lightbox function
    sesJqueryObject('<script type="application/javascript">function openSeaocoreLightBox(href){if(href.search("/albums/photo/view/album_id/") != -1) return;openLightBoxForSesPlugins(href);return false;}</script>').appendTo("body");	
  }
});

sesLightbox = false;
function openLightBoxForSesPlugins(href,imageURL){
	var manageData  = 'yes';
	if(typeof imageURL == 'undefined'){
		var imageURL = en4.core.baseUrl+'application/modules/Sesbasic/externals/images/loading.gif';
	}
  sesCustomPhotoURL = true;
	getRequestedAlbumPhotoForImageViewer(imageURL,href,manageData);
}