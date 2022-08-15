//Tooltip code for verification icon
sesJqueryObject(document).on('mouseover mouseout', '.sesbasic_verify_tip', function(event) {
	sesJqueryObject(this).tooltipster({
        interactive: true,
        content: '',
        contentCloning: false,
        contentAsHTML: true,
        animation: 'fade',
        updateAnimation:false,
        functionBefore: function(origin, continueTooltip) {
            //get attr
            if(typeof sesJqueryObject(origin).attr('data-rel') == 'undefined')
                var guid = sesJqueryObject(origin).attr('data-src');
            else
                var guid = sesJqueryObject(origin).attr('data-rel');

            // we'll make this function asynchronous and allow the tooltip to go ahead and show the loading notification while fetching our data.
            continueTooltip();

            if(typeof sesJqueryObject(this).parent().find('.sesbasic_member_verification_tip').html() == 'undefined') {
              var data = "<div class='sesbasic_member_verification_tip'>"+sesJqueryObject(this).parent().parent().find('.sesbasic_member_verification_tip').html()+"<div>";
            } else {
              var data = "<div class='sesbasic_member_verification_tip'>"+sesJqueryObject(this).parent().find('.sesbasic_member_verification_tip').html()+"<div>";
            }

            origin.tooltipster('content', data);
        },
	});
	sesJqueryObject(this).tooltipster('show');
});

//option show hide code
sesJqueryObject(document).mouseup(function (e)
{
  var container = sesJqueryObject(".sesact_pulldown_wrapper");
  if (!container.is(e.target) // if the target of the click isn't the container...
      && container.has(e.target).length === 0) // ... nor a descendant of the container
  {
    container.removeClass('sesact_pulldown_active');
    //container.hide();
  }else if(sesJqueryObject(e.target).hasClass('sesact_pulldown_wrapper') || sesJqueryObject(e.target).closest('.sesact_pulldown_wrapper').length){
      if(sesJqueryObject(e.target).hasClass('sesact_pulldown_wrapper')){
        if( sesJqueryObject(e.target).hasClass('sesact_pulldown_active'))
          sesJqueryObject(e.target).removeClass('sesact_pulldown_active');
        else{
          container.removeClass('sesact_pulldown_active');
          sesJqueryObject(e.target).addClass('sesact_pulldown_active');
        }
      }else{
        if( sesJqueryObject(e.target).closest('.sesact_pulldown_wrapper').hasClass('sesact_pulldown_active'))
          sesJqueryObject(e.target).closest('.sesact_pulldown_wrapper').removeClass('sesact_pulldown_active');
        else{
          container.removeClass('sesact_pulldown_active');
          sesJqueryObject(e.target).closest('.sesact_pulldown_wrapper').addClass('sesact_pulldown_active');
        }
      }
  }
});
//tooltip code
var sestooltipOrigin;
sesJqueryObject(document).on('mouseover mouseout', '.ses_tooltip', function(event) {
	if(sesbasicdisabletooltip)
		return false;

	sesJqueryObject(this).tooltipster({
					interactive: true,
					content: '<div class="sesbasic_tooltip_loading">Loading...</div>',
					contentCloning: false,
					contentAsHTML: true,
					animation: 'fade',
					updateAnimation:false,
					functionBefore: function(origin, continueTooltip) {
						//get attr
						if(typeof sesJqueryObject(origin).attr('data-rel') == 'undefined')
							var guid = sesJqueryObject(origin).attr('data-src');
						else
							var guid = sesJqueryObject(origin).attr('data-rel');
							// we'll make this function asynchronous and allow the tooltip to go ahead and show the loading notification while fetching our data.
							continueTooltip();
						       sestooltipOrigin = sesJqueryObject(this);
							if (origin.data('ajax') !== 'cached') {
								sesJqueryObject.ajax({
											type: 'POST',
											url: en4.core.baseUrl+'sesbasic/tooltip/index/guid/'+guid,
											success: function(data) {
												// update our tooltip content with our returned data and cache it
												origin.tooltipster('content', data).data('ajax', 'cached');
											}
								});
							}
					}
	});
	sesJqueryObject(this).tooltipster('show');
});
sesJqueryObject(document).on('change','#myonoffswitch',function(){
	ses_view_adultContent();
})
//adult content switch
var isActiveRequest;
function ses_view_adultContent(){
	var	url = en4.core.baseUrl+'sesbasic/index/adult/';
	var isActiveRequest =	(new Request.HTML({
      method: 'post',
      'url': url,
      'data': {
        format: 'html',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        //keep Silence
				location.reload();
      }
    }));
		isActiveRequest.send();
}

function socialSharingPopUp(url,title, saveURL, type, showCount){

  //if(1) {
    var	urlsave = en4.core.baseUrl+'sessocialshare/index/savesocialsharecount/';
    var socialShareCountSave =	(new Request.HTML({
        method: 'post',
        'url': urlsave,
        'data': {
          title: title,
          pageurl: saveURL,
          type: type,
          format: 'html',
        },
        onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
          //keep Silence
          //location.reload();
          if(showCount == 1) {
            var countType = sesJqueryObject('.sessocialshare_count_'+type).html();
            sesJqueryObject('.sessocialshare_count_'+type).html(++countType);
          }
        }
    }));
    socialShareCountSave.send();

  //}

  if(type) {
    if(title == 'Facebook')
      url = url; //+encodeURI('%26fbrefresh%3Drefresh', '_blank');
    if(title == 'Google')
      window.open(url, '_blank');
    else
      window.open(url, '_blank');
  } else {
    if(title == 'Facebook')
      url = url; //+encodeURI('%26fbrefresh%3Drefresh');
    if(title == 'Google')
      window.open(url, title ,'height=500,width=850');
    else
      window.open(url, title ,'height=500,width=800');
  }
	return false;
}
function openSmoothBoxInUrl(url){
	Smoothbox.open(url);
	parent.Smoothbox.close;
	return false;
}
//send quick share link
function sesAjaxQuickShare(url){
	if(!url)
		return;
	sesJqueryObject('.sesbasic_popup_slide_close').trigger('click');
	(new Request.HTML({
      method: 'post',
      'url': url,
      'data': {
        format: 'html',
				is_ajax : 1
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        //keep Silence
				showTooltip('10','10','<i class="fa fa-envelope"></i><span>'+(en4.core.language.translate("Quick share successfully "))+'</span>','sesbasic_message_notification');
      }
    })).send();
}
//make href in tab container
function tabContainerHrefSesbasic(tabId){
	if(sesJqueryObject('#main_tabs').length){
		var tab = sesJqueryObject('#main_tabs').find('.tab_'+tabId);
		if(tab.length){
			var hrefTab = window.location.href;
			var queryString = '';
			if(hrefTab.indexOf('?') > 0){
				var splitStringQuery = hrefTab.split('?');
				hrefTab = splitStringQuery[0];
				if(typeof splitStringQuery[1] != 'undefined'){
					queryString = '?'+splitStringQuery[1];
				}
			}
			if(hrefTab.indexOf('/tab/') > 0){
				hrefTab = hrefTab.split('/');
				hrefTab.pop();
				hrefTab.pop();
				hrefTab = hrefTab.join('/');
			}
			hrefTab = hrefTab+'/tab/'+tabId+queryString
			tab.find('a').attr('href',hrefTab);
			var clickElem = tab.find('a').attr('onclick')+';return false;';
			tab.find('a').attr('onclick',clickElem);
		}
	}
}
//content like, favourite, rated and follow auto tooltip from left bottom.
function showTooltipSesbasic(x, y, contents, className) {
	if(sesJqueryObject('.sesbasic_notification').length > 0)
		sesJqueryObject('.sesbasic_notification').hide();
		sesJqueryObject('<div class="sesbasic_notification '+className+'">' + contents + '</div>').css( {
		display: 'block',
	}).appendTo("body").fadeOut(5000,'',function(){
		sesJqueryObject(this).remove();
	});
}
//common function for like comment ajax
function like_favourite_data_photo(element,functionName,itemType,moduleName,likeNoti,unLikeNoti,className){

		if(!sesJqueryObject(element).attr('data-url'))
			return;
		if(sesJqueryObject(element).hasClass('button_active')){
				sesJqueryObject(element).removeClass('button_active');
		}else
				sesJqueryObject(element).addClass('button_active');

    var URL = en4.core.baseUrl + moduleName+'/index/'+functionName;
    if(itemType) {
      itemType = itemType;
    } else {
      itemType = sesJqueryObject(element).attr('data-type');
      if(itemType == 'sespage_photo') {
        moduleName = 'sespage';
        var URL = en4.core.baseUrl + moduleName+'/ajax/'+functionName;
      }
    }
		 (new Request.HTML({
      method: 'post',
      'url':  URL,
      'data': {
        format: 'html',
        id: sesJqueryObject(element).attr('data-url'),
				type:itemType,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        var response =jQuery.parseJSON( responseHTML );
				if(response.error)
					alert(en4.core.language.translate('Something went wrong,please try again later'));
				else{
					sesJqueryObject(element).find('span').html(response.count);
					if(response.condition == 'reduced'){
							sesJqueryObject(element).removeClass('button_active');
							showTooltip(10,10,unLikeNoti)
							return true;
					}else{
							sesJqueryObject(element).addClass('button_active');
							showTooltip(10,10,likeNoti,className)
							return false;
					}
				}
      }
    })).send();
}
sesJqueryObject(document).on('click','.sesbasic_favourite_sesbasic_video',function(){
	like_favourite_data_photo(this,'favourite',itemType,'<i class="fa fa-heart"></i><span>'+(en4.core.language.translate("Video added as Favourite successfully"))+'</span>','<i class="fa fa-heart"></i><span>'+(en4.core.language.translate("Video Unfavourited successfully"))+'</span>','sesbasic_favourites_notification');
});


sesJqueryObject(document).on('click','.sesbasic_favourite_sesbasic_photo',function(){
  like_favourite_data_photo(this,'favourite','','','<i class="fa fa-heart"></i><span>'+(en4.core.language.translate("Photo added as Favourite successfully"))+'</span>','<i class="fa fa-heart"></i><span>'+(en4.core.language.translate("Photo Unfavourited successfully"))+'</span>','sesbasic_favourites_notification');
});

sesJqueryObject(document).on('click','.openSmoothbox',function(e){
  var url = sesJqueryObject(this).attr('href');
  openSmoothBoxInUrl(url);
  return false;
});
sesJqueryObject(document).on('click','.opensmoothboxurl',function(e){
  var url = sesJqueryObject(this).attr('href');
  openSmoothBoxInUrl(url);
  return false;
});
//open url in smoothbox
function opensmoothboxurl(openURLsmoothbox){
  openSmoothBoxInUrl(openURLsmoothbox);
	return false;
}

sesJqueryObject(document).on('click','#sesbasic_btn_currency',function(){
	if(sesJqueryObject(this).hasClass('active')){
		sesJqueryObject(this).removeClass('active');
		sesJqueryObject('#sesbasic_currency_change').hide();
	}else{
		sesJqueryObject(this).addClass('active');
		sesJqueryObject('#sesbasic_currency_change').show();
	}
});
//currency change
sesJqueryObject(document).on('click','ul#sesbasic_currency_change_data li > a',function(){
	var currencyId = sesJqueryObject(this).attr('data-rel');
	setSesCookie('sesbasic_currencyId',currencyId,365);
	location.reload();
});
function setSesCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires+';path=/;';
}

// Option Pulldown
sesJqueryObject(document).on('click','.sesbasic_pulldown_toggle',function(){
	if(sesJqueryObject(this).hasClass('showpulldown')){
		sesJqueryObject(this).removeClass('showpulldown');
	}else{
		sesJqueryObject('.sesbasic_pulldown_toggle').removeClass('showpulldown');
		sesJqueryObject(this).addClass('showpulldown');
	}
		return false;
});
sesJqueryObject(document).click(function(){
	sesJqueryObject('.sesbasic_pulldown_toggle').removeClass('showpulldown');
});


// light box like work
sesJqueryObject(document).on('click','#sesbasicLightboxLikeUnlikeButton',function() {
  var dataid = sesJqueryObject(this).attr('data-id');
  if(!sesJqueryObject('#sesadvancedcomment_like_action_'+dataid).length){
    sesJqueryObject('#comments .comments_options').find("a:eq(1)").trigger('click');
  }else{
    var count = sesJqueryObject(this).find('#like_unlike_count').html();
    if(sesJqueryObject('#sesadvancedcomment_like_action_'+dataid).hasClass('sesadvancedcommentlike')){
      count = parseInt(count) + 1;
      sesJqueryObject(this).addClass(' button_active');
    }else{
      count = parseInt(count) - 1;
      sesJqueryObject(this).removeClass('button_active');
    }
          sesJqueryObject(this).find('#like_unlike_count').html(count);
          sesJqueryObject('#sesadvancedcomment_like_action_'+dataid).trigger('click');
  }
      return false;
});
