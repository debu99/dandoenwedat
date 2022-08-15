function showTooltipSesbasicMembership(x, y, contents, className) {
	if(sesJqueryObject('.sesbasic_notification').length > 0)
		sesJqueryObject('.sesbasic_notification').hide();
	sesJqueryObject('<div class="sesbasic_notification '+className+'">' + contents + '</div>').css( {
		display: 'block',
	}).appendTo("body").fadeOut(5000,'',function(){
		sesJqueryObject(this).remove();	
	});
}
var sesaddFriendRequest,sescancelFriendRequest, sesremoveFriend, sesacceptFriend;
sesJqueryObject(document).on('click', '.sesbasic_member_addfriend_request', function() {
    var sesthis = this;
		var data = {
      'user_id' : sesJqueryObject(this).attr('data-src'),
      'format' : 'html',
			'parambutton': sesJqueryObject(this).attr('data-rel'),
    };
  if(typeof sesaddFriendRequest != 'undefined')
    sesaddFriendRequest.cancel();
	 data[sesJqueryObject(this).attr('data-tokenname')] = sesJqueryObject(this).attr('data-tokenvalue');
   sesaddFriendRequest =  (new Request.HTML({
    url: en4.core.baseUrl + 'sesbasic/membership/add-friend',
    'data': data,
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavascript) {
			var result = sesJqueryObject.parseJSON(responseHTML);
			if(result.status == 1){
     		sesJqueryObject(sesthis).parent().html(result.message);
				showTooltip('10','10','<i class="fa fa-check-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>','sesbasic_friend_request_notification');
			}
			else
				 en4.core.showError(en4.core.language.translate(result.message));
    }
  })).send();
  
});

sesJqueryObject(document).on('click', '.sesbasic_member_cancelfriend_request', function() {
  
    var sesthis = this;
		var data = {
      'user_id' : sesJqueryObject(this).attr('data-src'),
      'format' : 'html',
			'parambutton': sesJqueryObject(this).attr('data-rel'),
    };
    if(typeof sescancelFriendRequest != 'undefined')
      sescancelFriendRequest.cancel();
		data[sesJqueryObject(this).attr('data-tokenname')] = sesJqueryObject(this).attr('data-tokenvalue');
    sescancelFriendRequest = (new Request.HTML({
    url: en4.core.baseUrl + 'sesbasic/membership/cancel-friend',
    'data': data,
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavascript) {
     var result = sesJqueryObject.parseJSON(responseHTML);
			if(result.status == 1){
     		sesJqueryObject(sesthis).parent().html(result.message);
				showTooltip('10','10','<i class="fa fa-times-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>','sesbasic_friend_remove_notification');
			}
			else
				 en4.core.showError(en4.core.language.translate(result.message));
    }
  })).send();
});

sesJqueryObject(document).on('click', '.sesbasic_member_removefriend_request', function() {
    var sesthis = this;
		var data = {
      'user_id' : sesJqueryObject(this).attr('data-src'),
      'format' : 'html',
			'parambutton': sesJqueryObject(this).attr('data-rel'),
    };
    if(typeof sesremoveFriend != 'undefined')
      sesremoveFriend.cancel();
		data[sesJqueryObject(this).attr('data-tokenname')] = sesJqueryObject(this).attr('data-tokenvalue');
    sesremoveFriend = (new Request.HTML({
    url: en4.core.baseUrl + 'sesbasic/membership/remove-friend',
    'data':data,
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavascript) {
    var result = sesJqueryObject.parseJSON(responseHTML);
			if(result.status == 1){
     		sesJqueryObject(sesthis).parent().html(result.message);
				showTooltip('10','10','<i class="fa fa-times-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>','sesbasic_friend_remove_notification');
			}
			else
				 en4.core.showError(en4.core.language.translate(result.message));
    }
  })).send();
});

sesJqueryObject(document).on('click', '.sesbasic_member_acceptfriend_request', function() {
    var sesthis = this;
		var data = {
      'user_id' : sesJqueryObject(this).attr('data-src'),
      'format' : 'html',
			'parambutton': sesJqueryObject(this).attr('data-rel'),
    };
    if(typeof sesacceptFriend != 'undefined')
      sesacceptFriend.cancel();
		data[sesJqueryObject(this).attr('data-tokenname')] = sesJqueryObject(this).attr('data-tokenvalue');
    sesacceptFriend = (new Request.HTML({
    url: en4.core.baseUrl + 'sesbasic/membership/accept-friend',
    'data': data,
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavascript) {
    var result = sesJqueryObject.parseJSON(responseHTML);
			if(result.status == 1){
     		sesJqueryObject(sesthis).parent().html(result.message);
				showTooltip('10','10','<i class="fa fa-check-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>','sesbasic_friend_request_notification');
			}
			else
				 en4.core.showError(en4.core.language.translate(result.message));
    }
  })).send();
});
