/* $Id:core.js 2017-01-19 00:00:00 SocialEngineSolutions $*/

sesJqueryObject(document).on('submit', '#sesadvancedcomment_contact_owner',function(e) {
  e.preventDefault();
  var formData = new FormData(this);
  var jqXHR=sesJqueryObject.ajax({
    url: en4.core.baseUrl +"sesadvancedcomment/index/contact",
    type: "POST",
    contentType:false,
    processData: false,
    data: formData,
    success: function(response){
      response = sesJqueryObject.parseJSON(response);
      if(response.status == 'true') {
			sesJqueryObject('#sessmoothbox_container').html("<div id='sesadvancedcomment_contact_message' class='sesadvancedcomment_contact_popup sesbasic_bxs'><div class='sesbasic_tip clearfix'><img src='application/modules/Sesadvancedcomment/externals/images/success.png' alt=''><span>"+en4.core.language.translate('Message sent successfully')+"</span></div></div>");
      	sesJqueryObject('#sesadvancedcomment_contact_message').fadeOut("slow", function(){setTimeout(function() {sessmoothboxclose();}, 3000);
      });
					
//        sesJqueryObject('#sespage_sent_meesage').show();
//        sesJqueryObject('#sespage_contact_popup').append('<div id="sespage_sent_meesage">Bharat Work</div>');
//        window.setTimeout(function() {sesJqueryObject('#sespage_sent_meesage').remove()}, 30000);
//				sessmoothboxclose();
      }
    }
  });
  return false;
});
sesJqueryObject(document).on('keypress','.body',function(event) {
  sesJqueryObject(this).closest('form').css('position','relative');
  if(sesJqueryObject(this).closest('form').hasClass('sesadv_form_submitting'))
    return false;
  if (event.keyCode == 13 && !event.shiftKey) {
     var body = sesJqueryObject(this).closest('form').find('.body').val();  
     var file_id = sesJqueryObject(this).closest('form').find('.file_id').val();
     var action_id = sesJqueryObject(this).closest('form').find('.file').val();;
     var emoji_id = sesJqueryObject(this).closest('form').find('.select_emoji_id').val();
     if(((!body && (file_id == 0)) && emoji_id == 0))
      return false;
    sesJqueryObject(this).closest('form').trigger('submit');
    sesJqueryObject(this).closest('form').addClass('submitting');
    sesJqueryObject(this).closest('form').append('<div class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
    return false;
   }
});
sesJqueryObject(document).on('click','.sescmt_media_more',function(){
  var elem = sesJqueryObject(this).parent().find('.sescmt_media_container');
  if(elem.hasClass('less')){
     elem.removeClass('less');
     elem.css('height','204px');
     sesJqueryObject(this).text('Show All');
  }else{
     elem.addClass('less');
     elem.css('height','auto'); 
     sesJqueryObject(this).text('Show Less');
  }
});
sesJqueryObject(document).on('click','.comment_btn_open',function(){
  var actionId = sesJqueryObject(this).attr('data-actionid');
  if(!actionId){
    actionId = sesJqueryObject(this).attr('data-subjectid');
    sesJqueryObject('#adv_comment_subject_btn_'+actionId).trigger('click'); 
  }else
    sesJqueryObject('#adv_comment_btn_'+actionId).trigger('click');  
  complitionRequestTrigger(); 
})
var isonCommentBox = isOnEditField = false;
var EditFieldValue = '';
function getDataMentionEditComment (that,data){
  if (sesJqueryObject(that).attr('data-mentions-input') === 'true') {  
       updateEditValComment(that, data);
  }
}
function updateEditValComment(that,data){
    EditFieldValue = data;
    sesJqueryObject(that).mentionsInput("update");  
}
var mentiondataarray = [];
sesJqueryObject(document).on('keyup','.body',function(){ 
    var data = sesJqueryObject(this).val();
     EditFieldValue = data;
});
sesJqueryObject(document).on('focus','.body',function(){ 
if(!sesJqueryObject(this).attr('id'))
  sesJqueryObject(this).attr('id',new Date().getTime());
  if(typeof sesadvancedactivitybigtext == 'undefined')
    sesadvancedactivitybigtext = false;
  isonCommentBox = true;
  var data = sesJqueryObject(this).val();
  
  if(!sesJqueryObject(this).val() || isOnEditField){ 
    if(!sesJqueryObject(this).val() )
      EditFieldValue = '';
    sesJqueryObject(this).mentionsInput({
        onDataRequest:function (mode, query, callback) {
         sesJqueryObject.getJSON('sesadvancedcomment/ajax/friends/query/'+query, function(responseData) {
          responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
          callback.call(this, responseData);
        });
      },
      //defaultValue: EditFieldValue,
      onCaret: true
    });
  }
  if(data){
     getDataMentionEditComment(this,data);
  }
  
  if(!sesJqueryObject(this).parent().hasClass('typehead')){
    sesJqueryObject(this).hashtags();
    sesJqueryObject(this).focus();
  }
  autosize(sesJqueryObject(this));
});
var CommentLikesTooltips;
en4.core.runonce.add(function() {
  // Add hover event to get likes
  $$('.comments_comment_likes').addEvent('mouseover', function(event) {
    var el = $(event.target);
    if( !el.retrieve('tip-loaded', false) ) {
      el.store('tip-loaded', true);
      el.store('tip:title', 'Loading...');
      el.store('tip:text', '');
      var id = el.get('id').match(/\d+/)[0];
      // Load the likes
      var url =  en4.core.baseUrl + 'sesadvancedcomment/index/get-likes';
      var req = new Request.JSON({
        url : url,
        data : {
          format : 'json',
          //type : 'core_comment',
          action_id : el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
          comment_id : id
        },
        onComplete : function(responseJSON) {
          el.store('tip:title', responseJSON.body);
          el.store('tip:text', '');
          CommentLikesTooltips.elementEnter(event, el); // Force it to update the text
        }
      });
      req.send();
    }
  });
  // Add tooltips
  CommentLikesTooltips = new Tips($$('.comments_comment_likes'), {
    fixed : true,
    className : 'comments_comment_likes_tips',
    offset : {
      'x' : 48,
      'y' : 16
    }
  });
  // Enable links in comments
  $$('.comments_body').enableLinks();
});
//reply comment
sesJqueryObject(document).on('click','.sesadvancedcommentreply',function(e){
  e.preventDefault();
  sesJqueryObject('.comment_reply_form').hide();
  sesJqueryObject(this).closest('.sesadvancedcomment_cnt_li').find('.comments_reply').find('.comment_reply_form').show();  
  sesJqueryObject(this).closest('.sesadvancedcomment_cnt_li').find('.comments_reply').find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply').show();
  
  
  sesJqueryObject(this).closest('.sesadvancedcomment_cnt_li').find('.comments_reply').find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply').find('.comment_form').find('.body').focus();
  complitionRequestTrigger();
})
function sesadvancedcommentlike(action_id, comment_id,obj,page_id,type,sbjecttype,subjectid,guid) {
  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/like',
    data : {
      format : 'json',
      action_id : action_id,
      page_id : page_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
      guid:guid,
      sbjecttype:sbjecttype,
      subjectid:subjectid,
      type:type
    },
    'onComplete' : function(responseHTML) {
      if( responseHTML ) {
        sesJqueryObject(obj).parent().parent().replaceWith(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });    
  ajax.send();
}
function sesadvancedcommentunlike(action_id, comment_id,obj,page_id,type,sbjecttype,subjectid,guid) {
  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/unlike',
    data : {
      format : 'json',
      page_id : page_id,
      action_id : action_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
      sbjecttype:sbjecttype,
      guid:guid,
      subjectid:subjectid,
      type:type
    },
    'onComplete' : function(responseHTML) {
      if(responseHTML){
        sesJqueryObject(obj).parent().parent().replaceWith(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
   ajax.send();
}
//like feed action content
sesJqueryObject(document).on('click','.sesadvancedcommentunlike',function(){
  var obj = sesJqueryObject(this);
  var action_id = sesJqueryObject(this).attr('data-actionid');
  var comment_id = sesJqueryObject(this).attr('data-commentid');
  var type = sesJqueryObject(this).attr('data-type');
   var datatext = sesJqueryObject(this).attr('data-text');
  var likeWorkText = sesJqueryObject(this).attr('data-like');
  var unlikeWordText = sesJqueryObject(this).attr('data-unlike');
  
  //check for unlike
  sesJqueryObject(this).find('i').removeAttr('style');
  sesJqueryObject(this).find('span').html(likeWorkText);
  sesJqueryObject(this).removeClass('sesadvancedcommentunlike').removeClass('_reaction').addClass('sesadvancedcommentlike');
  sesJqueryObject(this).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/unlike',
    data : {
      format : 'json',
      action_id : action_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
       sbjecttype:sesJqueryObject(this).attr('data-sbjecttype'),
      subjectid:sesJqueryObject(this).attr('data-subjectid'),
      type:type
    },
    'onComplete' : function(responseHTML) {
      if( responseHTML ) {
       var elemnt =  sesJqueryObject(obj).closest('.comment-feed').find('.sesadvcmt_comments').find('.comments_cnt_ul');
       if(elemnt.find('.sesadvcmt_comments_stats').length){
        elemnt = elemnt.find('.sesadvcmt_comments_stats');
        var getPreviousSearchComment = sesJqueryObject('.comment_stats_'+action_id).html();
        sesJqueryObject(elemnt).replaceWith(responseHTML.body);
        sesJqueryObject('.comment_stats_'+action_id).html(getPreviousSearchComment);
       }else
        sesJqueryObject(elemnt).prepend(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
   ajax.send();
});
var previousSesadvcommLikeObj;
//unlike feed action content
sesJqueryObject(document).on('click','.sesadvancedcommentlike',function(){
  var obj = sesJqueryObject(this);
	previousSesadvcommLikeObj = obj.closest('.sesadvcmt_hoverbox_wrapper');
  var action_id = sesJqueryObject(this).attr('data-actionid');
  var guid = "";
   var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sespage_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesgroup_switcher_cnt').find('a').first();
  if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesbusiness_switcher_cnt').find('a').first();
    if(!guidItem.length)
        var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
   if(guidItem)
    guid = guidItem.data('rel');
  var comment_id = sesJqueryObject(this).attr('data-commentid');
  var type = sesJqueryObject(this).attr('data-type');
  var datatext = sesJqueryObject(this).attr('data-text');
  var subject_id = sesJqueryObject(this).attr('data-subjectid');
  //check for like
  var isLikeElem = false;
  if(sesJqueryObject(this).hasClass('reaction_btn')){
    var image = sesJqueryObject(this).find('.reaction').find('i').css('background-image');
    image = image.replace('url(','').replace(')','').replace(/\"/gi, "");
    var elem = sesJqueryObject(this).parent().parent().parent().find('a');
    isLikeElem = true;
  }else{
    var image = sesJqueryObject(this).parent().find('.sesadvcmt_hoverbox').find('span').first().find('.reaction_btn').find('.reaction').find('i').css('background-image');
    image = image.replace('url(','').replace(')','').replace(/\"/gi, "");
    var elem = sesJqueryObject(this);
    isLikeElem = false
  }
  
  var likeWorkText = sesJqueryObject(elem).attr('data-like');
  var unlikeWordText = sesJqueryObject(elem).attr('data-unlike');
  
  //unlike
  if(sesJqueryObject(elem).hasClass('_reaction') && !isLikeElem){
    sesJqueryObject(elem).find('i').removeAttr('style');
    sesJqueryObject(elem).find('span').html(unlikeWordText);
    sesJqueryObject(elem).removeClass('sesadvancedcommentunlike').removeClass('_reaction').addClass('sesadvancedcommentlike');
    sesJqueryObject(elem).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  }else{
  //like  
    sesJqueryObject(elem).find('i').css('background-image', 'url(' + image + ')');
    sesJqueryObject(elem).find('span').html(datatext);
    sesJqueryObject(elem).removeClass('sesadvancedcommentlike').addClass('_reaction').addClass('sesadvancedcommentunlike');
    sesJqueryObject(elem).parent().addClass('feed_item_option_unlike').removeClass('feed_item_option_like');
  }

// 	var parentObject = previousSesadvcommLikeObj.parent().html();
// 	var parentElem = previousSesadvcommLikeObj.parent();
// 	previousSesadvcommLikeObj.parent().html('');
// 	parentElem.html(parentObject);
	
  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/like',
    data : {
      format : 'json',
      action_id : action_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
      guid : guid ,
       sbjecttype:sesJqueryObject(this).attr('data-sbjecttype'),
      subjectid:sesJqueryObject(this).attr('data-subjectid'),
      type:type
    },
    'onComplete' : function(responseHTML) {
      if( responseHTML ) {
       var elemnt =  sesJqueryObject(obj).closest('.comment-feed').find('.sesadvcmt_comments').find('.comments_cnt_ul');      
       
       if(elemnt.find('.sesadvcmt_comments_stats').length){
        elemnt = elemnt.find('.sesadvcmt_comments_stats');
        if(!action_id)
          action_id = subject_id;
        var getPreviousSearchComment = sesJqueryObject('.comment_stats_'+action_id).html();
        sesJqueryObject(elemnt).replaceWith(responseHTML.body);
        sesJqueryObject('.comment_stats_'+action_id).html(getPreviousSearchComment);
       }else
        sesJqueryObject(elemnt).prepend(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });    
  ajax.send();
});
//cancel comment edit
sesJqueryObject(document).on('click','.sesadvancedcomment_cancel',function(e){
  e.preventDefault();
  var parentElem = sesJqueryObject(this).closest('.sesadvancedcomment_cnt_li');
  parentElem.find('.comment_edit').remove();
   parentElem.find('.comments_info').show();
   var topParentElement = parentElem.closest('.comments');
  topParentElement = topParentElement.find('.sesadvancedactivity-comment-form').show();
  complitionRequestTrigger();
});
//cancel comment reply edit
sesJqueryObject(document).on('click','.sesadvancedcomment_cancel_reply',function(e){
  e.preventDefault();
  var parentElem = sesJqueryObject(this).closest('li');
   parentElem.find('.comments_reply_info').show();
   parentElem.find('.comment_edit').remove();
   complitionRequestTrigger();
});
//cancel file upload image
sesJqueryObject(document).on('click','.cancel_upload_file',function(e){
  e.preventDefault();
  var id = sesJqueryObject(this).attr('data-url');
  var value =  sesJqueryObject(this).parent().parent().parent().find('.comment_form').find('.file_id').val().replace(id+'_album_photo','');
  sesJqueryObject(this).parent().parent().parent().find('.comment_form').find('.file_id').val(value);
  value = sesJqueryObject(this).parent().parent().parent().find('.comment_form').find('.file_id').val().replace(id+'_video','');
  sesJqueryObject(this).parent().parent().parent().find('.comment_form').find('.file_id').val(value);
  sesJqueryObject(this).parent().hide().remove('');
  complitionRequestTrigger();
})
function getEditCommentMentionData(obj){ 
  sesJqueryObject(obj).find('.body').mentionsInput('val', function(data) {
     submiteditcomment(obj,data);
  });  
}
//edit comment
sesJqueryObject(document).on('submit','.sesadvancedactivity-comment-form-edit',function(e){
 e.preventDefault();
 getEditCommentMentionData(this);
});
function submiteditcomment(that,data){
  var body = data; 
 var file_id = sesJqueryObject(that).find('.file_id').val();
 if((!body && file_id == 0))
  return false;
  var formData = new FormData(that);
  formData.append('bodymention', body);
  submitCommentFormAjax = sesJqueryObject.ajax({
      type:'POST',
      url: en4.core.baseUrl+'sesadvancedcomment/index/edit-comment/',
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        sesJqueryObject(that).removeClass('submitting');
        sesJqueryObject(that).find('.sesbasic_loading_cont_overlay').remove();
        try{
          var dataJson = sesJqueryObject.parseJSON(data);
          if(dataJson.status == 1){
            var parentElem =  sesJqueryObject(that).parent().parent();
            parentElem.find('.comments_info').find('.comments_body').html(dataJson.content);
            parentElem.find('.comments_info').show();
            parentElem.find('.comment_edit').remove();
            parentElem.closest('.comments').find('.sesadvancedactivity-comment-form').show();
            en4.core.runonce.trigger();
            complitionRequestTrigger();
          //silence
          }else{
            alert('Something went wrong, please try again later');	
          }
          
        }catch(err){
          //silence
        }
      },
      error: function(data){
        //silence
      }
  });   
}
function commentreplyedit(that,data){
  var body = data;  
 var file_id = sesJqueryObject(that).find('.file_id').val();
 if((!body && file_id == 0))
  return false;
  var formData = new FormData(that);
  formData.append('bodymention', body);
  
  
  
  submitCommentFormAjax = sesJqueryObject.ajax({
      type:'POST',
      url: en4.core.baseUrl+'sesadvancedcomment/index/edit-reply/',
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        sesJqueryObject(that).removeClass('submitting');
        sesJqueryObject(that).find('.sesbasic_loading_cont_overlay').remove();
        try{
          var dataJson = sesJqueryObject.parseJSON(data);
          if(dataJson.status == 1){
            var parentElem =  sesJqueryObject(that).parent().parent();
            parentElem.find('.comments_reply_info').find('.comments_reply_body').html(dataJson.content);
            parentElem.find('.comments_reply_info').show();
            parentElem.find('.comment_edit').remove();
            en4.core.runonce.trigger();
            complitionRequestTrigger();
          //silence
          }else{
            alert('Something went wrong, please try again later');	
          }
        }catch(err){
          //silence
        }
      },
      error: function(data){
        //silence
      }
  });   
}
function getCommentReplyEditMentionData(obj){ 
  sesJqueryObject(obj).find('.body').mentionsInput('val', function(data) {
     commentreplyedit(obj,data);
  });  
}
//edit comment reply
sesJqueryObject(document).on('submit','.sesadvancedactivity-comment-form-edit-reply',function(e){
 e.preventDefault();
 getCommentReplyEditMentionData(this);
});
function commentReply(that,data){
  var body = data;  
 var file_id = sesJqueryObject(that).find('.comment_form').find('.file_id').val();
 var emoji_id = sesJqueryObject(that).find('.select_emoji_id').val();
 var gif_id = sesJqueryObject(that).find('.select_gif_id').val();
 if(((!body && (file_id == 0)) && emoji_id == 0 && gif_id == 0))
  return false
  if(!sesJqueryObject('.select_file').val()){
    sesJqueryObject('.select_file').remove();
    executed = true;
  }
  var formData = new FormData(that);
  if(executed == true)
    sesJqueryObject(that).find('.file_comment_select').parent().append('<input type="file" name="Filedata" class="select_file" multiple="" value="0" style="display:none;">');
  formData.append('bodymention', body);
  //page
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sespage_switcher_cnt').find('.sespage_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
  //group
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sesgroup_switcher_cnt').find('.sesgroup_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
  //business
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sesbusiness_switcher_cnt').find('.sesbusiness_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
    //store
    var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.estore_switcher_cnt').find('.estore_feed_change_option_a');
    if(elem.length){
        guid = elem.attr('data-subject');
        formData.append('guid', guid);
    }
  submitCommentFormAjax = sesJqueryObject.ajax({
      type:'POST',
      url: en4.core.baseUrl+'sesadvancedcomment/index/reply/',
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        sesJqueryObject(that).removeClass('submitting');
        sesJqueryObject(that).find('.sesbasic_loading_cont_overlay').remove();
        try{
          var dataJson = sesJqueryObject.parseJSON(data);
          if(dataJson.status == 1){
            //sesJqueryObject(dataJson.content).insertBefore(sesJqueryObject(that).closest('.comments_reply').find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply'));
						sesJqueryObject(that).parent().parent().find('.comments_reply_cnt').append(dataJson.content);
            sesJqueryObject(that).find('._form_container').find('.comment_form').find('.body').val('');
            sesJqueryObject(that).find('._form_container').find('.comment_form').find('.body').css('height','auto');
            sesJqueryObject(that).find('._form_container').find('.comment_form').find('.body').parent().parent().find('div').eq(0).html('');
           var fileElem = sesJqueryObject(that).find('._form_container').find('.comment_form').find('._sesadvcmt_post_icons').find('span');
           fileElem.find('.select_file').val('');
           fileElem.find('.file_id').val('');
           fileElem.find('.select_emoji_id').val('');
           fileElem.find('.select_gif_id').val('');
           sesJqueryObject(that).find('._form_container').find('.uploaded_file').html('');
            sesJqueryObject(that).find('._form_container').find('.uploaded_file').hide();
            en4.core.runonce.trigger();
            complitionRequestTrigger();
          //silence
          }else{
            alert('Something went wrong, please try again later');	
          }
        }catch(err){
          //silence
        }
      },
      error: function(data){
        //silence
      }
  });   
}
//create reply comment
sesJqueryObject(document).on('submit','.sesadvancedactivity-comment-form-reply',function(e){
 e.preventDefault();
 getCommentMentionData(this);
});
function getCommentMentionData(obj){ 
  sesJqueryObject(obj).find('.body').mentionsInput('val', function(data) {
     commentReply(obj,data);
  });  
}
//comment edit form
sesJqueryObject(document).on('click','.sesadvancedcomment_edit',function(e){
  e.preventDefault();  
  var parentElem = sesJqueryObject(this).closest('.sesadvancedcomment_cnt_li');
  var topParentElement = parentElem.closest('.comments');
  topParentElement = topParentElement.find('.sesadvancedactivity-comment-form').hide();
  parentElem.find('.comments_info').hide();
  var textBody = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').html();
  if(textBody != ""){
    textBody = textBody.split('<br>').join('');
  }
  //Feeling work
  EditFieldValue = textBody;
  
  
  isOnEditField = true;
  var datamention = parentElem.find('.comments_info').find('.comments_body').find('#data-mention').html();
  if(datamention){
    mentionsCollectionValEdit = JSON.parse(datamention);
  }
  var module = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('rel');
  module = '<input type="hidden" name="modulecomment" value="'+module+'"><input type="hidden"  class="select_emoji_id" name="emoji_id" value="0">';
  var subject = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('data-subject');
  var subjectid = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('data-subjectid');
  var subjectInputs = '';
  if(subject){
    subjectInputs = '<input type="hidden" name="resource_type" value="'+subject+'"><input type="hidden" name="resource_id" value="'+subjectid+'">';  
  }
  var fileid,filesrc,image = '';
  var display = 'none';
  var comment_id = parentElem.attr('id').replace('comment-','');
  fileid = 0;
  files = '';
  filesLength = parentElem.find('.comments_info').find('.comments_body').find('.comment_image');
  if(filesLength.length){
    for(var i =0; i<filesLength.length;i++){
      if(fileid == 0)
        fileid = '';
     if(sesJqueryObject(filesLength[i]).attr('data-type') == 'album_photo'){
      fileid = fileid+sesJqueryObject(filesLength[i]).attr('data-fileid')+'_album_photo,';
      var videoBtn = '';
     }else{
      fileid = fileid+sesJqueryObject(filesLength[i]).attr('data-fileid')+'_video,';
      var videoBtn = '<a href="javascript:;" class="sescmt_play_btn fa fa-play"></a>';
     }
     filesrc = sesJqueryObject(filesLength[i]).find('img').attr('src');
     image = '<img src="'+filesrc+'"><a href="javascript:;" data-url="'+sesJqueryObject(filesLength[i]).attr('data-fileid')+'" class="cancel_upload_file fas fa-times" title="Cancel"></a>'+videoBtn;
     display = 'block';
     files = '<div class="uploaded_file" style="display:block;">'+image+'</div>'+files;
    }
  }
  videoLink = '';
  if(videoModuleEnable == 1 ){
     videoLink = '<span><a href="javascript:;" class="video_comment_select"></a></span>';
  }
  imageLink = '';
  if(AlbumModuleEnable == 1 ){
     imageLink = '<a href="javascript:;" class="file_comment_select"></a>';
  }
  sesfeelingEmojis = '';
  if(typeof sesemojiEnable != "undefined" && sesemojiEnable == 1) {
    sesfeelingEmojis = '<span class="sesact_post_tool_i tool_i_feelings"><a href="javascript:;" class="feeling_emoji_comment_select"></a></span>';
  }
  var d = new Date();
  var time = d.getTime();
  var html = '<div class="comment_edit _form_container sesbasic_clearfix"><form class="sesadvancedactivity-comment-form-edit" method="post"><div class="comment_form sesbasic_clearfix"><textarea class="body" name="body" id="'+time+'" cols="45" rows="1" placeholder="'+en4.core.language.translate("Write a comment...")+'">'+textBody+'</textarea><div class="_sesadvcmt_post_icons sesbasic_clearfix"><span>'+imageLink+'<input type="file" name="Filedata" class="select_file" multiple style="display:none;">'+module+subjectInputs+'<input type="hidden" name="file_id" class="file_id" value="'+fileid+'"><input type="hidden" class="file" name="comment_id" value="'+comment_id+'"></span>'+videoLink+'<span><a href="javascript:;" class="emoji_comment_select"></a></span>'+sesfeelingEmojis+'</div></div><div class="uploaded_file"  style="display:none;"></div><div class="upload_file_cnt">'+files+'</div><div class="sesadvcmt_btns" style="margin-top:0px;"><a href="javascript:;" class="sesadvancedcomment_cancel">cancel</a></div></form></div>';
  
  sesJqueryObject(html).insertBefore(parentElem.find('.comments_info'));
  parentElem.parent().find('.comment_edit').find('form').find('.comment_form').find('.body').trigger('focus');
  complitionRequestTrigger();
  sesJqueryObject('#'+time).val(textBody);
  sesJqueryObject('#'+time).trigger("focus");
});
//comment reply edit form
sesJqueryObject(document).on('click','.sesadvancedcomment_reply_edit',function(e){
  e.preventDefault();   
  var parent = sesJqueryObject(this).closest('.comments_reply_cnt');
  parent.find('.comment_edit').remove();
  parent.find('.comments_reply_info').show();
  var parentElem = sesJqueryObject(this).closest('.comments_reply_info');
   parentElem.find('.comments_reply').find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply').hide();
  parentElem.hide();
  var textBody = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').html();
  if(textBody != ""){
    textBody = textBody.split('<br>').join('');
  }
  //Feeling work
  EditFieldValue = textBody;
  
  
  isOnEditField = true;
  var datamention = parentElem.find('.comments_reply_body').find('#data-mention').html();
  if(datamention){
    mentionsCollectionValEdit = JSON.parse(datamention);
  }
  var module = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('rel');
  module = '<input type="hidden" name="modulecomment" value="'+module+'"><input type="hidden" name="emoji_id" class="select_emoji_id" value="0">';
  var subject = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('data-subject');
  var subjectid = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('data-subjectid');
  var subjectInputs = '';
  if(subject){
    subjectInputs = '<input type="hidden" name="resource_type" value="'+subject+'"><input type="hidden" name="resource_id" value="'+subjectid+'">';  
  }
  var fileid,filesrc,image = '';
  var display = 'none';
  var comment_id = parentElem.closest('li').attr('id').replace('comment-','');
  fileid = 0;
  files = '';
  filesLength = parentElem.find('.comments_reply_body').find('.comment_reply_image');
  if(filesLength.length){
    for(var i =0; i<filesLength.length;i++){
      if(fileid == 0)
        fileid = '';
     if(sesJqueryObject(filesLength[i]).attr('data-type') == 'album_photo'){
      fileid = fileid+sesJqueryObject(filesLength[i]).attr('data-fileid')+'_album_photo,';
      var videoBtn = '';
     }else{
      fileid = fileid+sesJqueryObject(filesLength[i]).attr('data-fileid')+'_video,';
      var videoBtn = '<a href="javascript:;" class="play_upload_file fa fa-play"></a>';
     }
     filesrc = sesJqueryObject(filesLength[i]).find('img').attr('src');
     image = '<img src="'+filesrc+'"><a href="javascript:;" data-url="'+sesJqueryObject(filesLength[i]).attr('data-fileid')+'" class="cancel_upload_file fas fa-times" title="Cancel"></a>'+videoBtn;
     display = 'block';
     files = '<div class="uploaded_file" style="display:block;">'+image+'</div>'+files;
    }
  }
  videoLink = '';
  if(videoModuleEnable == 1 ){
     videoLink = '<span><a href="javascript:;" class="video_comment_select"></a></span>';
  }
  imageLink = '';
  if(AlbumModuleEnable == 1 ){
     imageLink = '<a href="javascript:;" class="file_comment_select"></a>';
  }
 
  //Feeling Work
  sesfeelingEmojis = '';
  if(typeof sesemojiEnable != "undefined" && sesemojiEnable == 1) {
    sesfeelingEmojis = '<span class="sesact_post_tool_i tool_i_feelings"><a href="javascript:;" class="feeling_emoji_comment_select"></a></span>';
  }
  
  var d = new Date();
  var time = d.getTime();
  var html = '<div class="comment_edit _form_container sesbasic_clearfix"><form class="sesadvancedactivity-comment-form-edit-reply" method="post"><div class="comment_form sesbasic_clearfix"><textarea class="body" id="'+time+'" name="body" cols="45" rows="1" placeholder="Write a reply...">'+textBody+'</textarea><div class="_sesadvcmt_post_icons sesbasic_clearfix"><span>'+imageLink+'<input type="file" name="Filedata" class="select_file" multiple style="display:none;">'+module+subjectInputs+'<input type="hidden" name="file_id" class="file_id" value="'+fileid+'"><input type="hidden" class="file" name="comment_id" value="'+comment_id+'"></span>'+videoLink+'<span><a href="javascript:;" class="emoji_comment_select"></a></span>'+sesfeelingEmojis+'</div></div><div class="uploaded_file" style="display:none;"></div><div class="upload_file_cnt">'+files+'</div><div class="sesadvcmt_btns" style="margin-top:0px;"><a href="javascript:;" class="sesadvancedcomment_cancel_reply">cancel</a></div></form></div>';  
  sesJqueryObject(html).insertBefore(parentElem);
  //var textArea = parentElem.parent().find('.comment_edit').find('form').find('.comment_form').find('.body').focus();
  //autosize(textArea);
  complitionRequestTrigger();
  sesJqueryObject('#'+time).val(textBody);
  sesJqueryObject('#'+time).trigger("focus");
});
//video in comment
var clickVideoAddBtn;
sesJqueryObject(document).on('click','.video_comment_select',function(e){
   clickVideoAddBtn = this;
   if(youtubePlaylistEnable == 1){
    var text = 'Paste a Youtube or Vimeo link here';  
   }else
    var text = 'Paste Vimeo link here';
   en4.core.showError('<div class="sescmt_add_video_popup"><div class="sescmt_add_video_popup_header">Add Video</div><div class="sescmt_add_video_popup_cont"><p><input type="text" value="" placeholder="'+text+'" id="sesadvvideo_txt"><img src="application/modules/Core/externals/images/loading.gif" style="display:none;" id="sesadvvideo_img"></p></div><div class="sescmt_add_video_popup_btm"><button type="button" id="sesadvbtnsubmit">Add</button><button onclick="Smoothbox.close()">Close</button></div></div>');
	 sesJqueryObject ('.sescmt_add_video_popup').parent().parent().addClass('sescmt_add_video_popup_wrapper sesbasic_bxs');
   sesJqueryObject('#sesadvvideo_txt').focus();
});
sesJqueryObject(document).on('click','#sesadvbtnsubmit',function(e){
  var value = sesJqueryObject('#sesadvvideo_txt').val();
  if(!value){
    sesJqueryObject('#sesadvvideo_txt').css('border','1px solid red');
    return false;
  }else{
    sesJqueryObject('#sesadvvideo_txt').css('border','');  
  }
  if(youtubePlaylistEnable == 1 && validYoutube(value))
    type = 1;  
  else if(validVimeo(value))
    type = 2;
  else{
    sesJqueryObject('#sesadvvideo_txt').css('border','1px solid red');
    return false;
  }
  
  sesJqueryObject('#sesadvbtnsubmit').prop('disabled',true);
  sesJqueryObject('#sesadvvideo_img').show();
  
   var ajax = new Request.JSON({
    url : en4.core.baseUrl + videoModuleName+'/index/compose-upload/format/json/c_type/wall',
    data : {
      format : 'json',
      uri:value,
      type:type
    },
    'onComplete' : function(responseHTML) {
      if(typeof responseHTML.status != 'undefined' && responseHTML.status){
         var videoid = responseHTML.video_id;
         var src = responseHTML.src;
         var form = sesJqueryObject(clickVideoAddBtn).closest('form');
         if(!form.find('.upload_file_cnt').length){
            var container = sesJqueryObject('<div class="upload_file_cnt"></div>').insertAfter(sesJqueryObject(form).find('.uploaded_file')); 
          }else
            var container = form.find('.upload_file_cnt');
          var uploadFile = sesJqueryObject('<div class="uploaded_file"></div>')
          var uploadImageLoader = sesJqueryObject('<img src="application/modules/Core/externals/images/loading.gif" class="_loading" />').appendTo(uploadFile);
          sesJqueryObject(uploadFile).appendTo(container);
          if(sesJqueryObject(form).find('.file_id').val() == 0)
            uploadFileId = '';
          else
            uploadFileId = sesJqueryObject(form).find('.file_id').val();
          sesJqueryObject(form).find('.file_id').val(uploadFileId+videoid+'_video'+',');
          sesJqueryObject(uploadFile).html('<img src="'+src+'"><a href="javascript:;" data-url="'+videoid+'" class="cancel_upload_file fas fa-times" title="Cancel"></a><a href="javascript:;" class="sescmt_play_btn fa fa-play"></a>');
          complitionRequestTrigger();
          Smoothbox.close();
      }else{
         sesJqueryObject('#sesadvvideo_txt').css('border','1px solid red');
      }
      sesJqueryObject('#sesadvbtnsubmit').prop('disabled',false);
      sesJqueryObject('#sesadvvideo_img').hide();
    }
  });    
  ajax.send();
  
});
function validYoutube(myurl){
  var matches = myurl.match(/watch\?v=([a-zA-Z0-9\-_]+)/);
  if (matches || myurl.indexOf('youtu.be') > -1)
     return true;
  else
    return false;
}
function validVimeo(myurl){
  //var myurl = "https://vimeo.com/23374724";
  if (myurl.indexOf('https://vimeo.com') >= 0 ) { 
     return true;
  } else { 
      return false;
  };  
}
//click on reply reply
sesJqueryObject(document).on('click','.sesadvancedcommentreplyreply',function(e){
  e.preventDefault();
  sesJqueryObject('.comment_reply_form').hide();
  var parent = sesJqueryObject(this).closest('.comments_reply');
  parent.find('.comment_reply_form').show();
  parent.find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply').show();
  parent.find('.comment_reply_form').find('.sesadvancedactivity-comment-form-reply').find('.comment_form').find('.body').focus();
  complitionRequestTrigger();
})
//view more comment
function sesadvancedcommentactivitycomment(action_id,page,obj,subjecttype){
  var type = sesJqueryObject(obj).closest('.comments_cnt_ul').find('.sesadvcmt_comments_stats');
  if(type.length){
    type = type.find('.sesadvcmt_pulldown_wrapper').find('.sesadvcmt_pulldown').find('.sesadvcmt_pulldown_cont').find('.search_adv_comment').find('li > a.active').data('type');
  }else
    type = '';
  if(typeof subjecttype != 'undefined')
    var url = en4.core.baseUrl + 'sesadvancedcomment/comment/list';
  else
    var url = en4.core.baseUrl + 'sesadvancedcomment/index/viewcomment';
  new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'page' : page,
        'action_id':action_id,
        'id':action_id,
        'type':subjecttype,
        'searchtype':type,
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          try{
            var dataJson = sesJqueryObject.parseJSON(responseHTML);
            dataJson = dataJson.body;
          }catch(err){
             var dataJson = responseHTML;
          }
          var onbView = sesJqueryObject(obj).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.comment_view_more');
          sesJqueryObject(obj).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').append(dataJson);
          onbView.remove();
          en4.core.runonce.trigger();
          complitionRequestTrigger();
        }
      }
    }).send();
  
}
//view more comment
function sesadvancedcommentactivitycommentreply(action_id,comment_id,page,obj,module,type){
  if(typeof type == 'undefined')
    var url = en4.core.baseUrl + 'sesadvancedcomment/index/viewcommentreply';
  else
    var url = en4.core.baseUrl + 'sesadvancedcomment/index/viewcommentreplysubject';
  new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'page' : page,
        'comment_id':comment_id,
        'action_id':action_id,
        'moduleN':module,
        'type':type,
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          var dataJson = sesJqueryObject.parseJSON(responseHTML);
          var onbView = sesJqueryObject(obj).closest('.comment_reply_view_more');
          onbView.parent().prepend(dataJson.body);
          onbView.remove();
          en4.core.runonce.trigger();
          complitionRequestTrigger();
        }
      }
    }).send();
  
}

//open url in smoothbox
sesJqueryObject(document).on('click','.sescommentsmoothbox',function(e){
  e.preventDefault();
  var url = sesJqueryObject(this).attr('href');
  sessmoothboxopen(this);
	parent.Smoothbox.close;
	return false;
})
//comment button click
sesJqueryObject(document).on('click','.sesadvanced_comment_btn',function(e){
  var commentCnt = sesJqueryObject(this).closest('.comment-feed').find('.comments');
  if(sesJqueryObject(this).hasClass('active')){
   // sesJqueryObject(this).removeClass('active');
   // commentCnt.hide();
   // return;
  }  
  sesJqueryObject(this).addClass('active');
  commentCnt.show();
  commentCnt.find('.advcomment_form').show();
  commentCnt.find('.advcomment_form').find('.comment_form').find('.body').focus();
  complitionRequestTrigger();
  return;
});

function getMentionData(obj){ 
  sesJqueryObject(obj).find('.body').mentionsInput('val', function(data) {
     submitCommentForm(obj,data);
  });  
}
function submitCommentForm(that,data){
  var body = data;  
 var file_id = sesJqueryObject(that).find('.file_id').val();
 var action_id = sesJqueryObject(that).find('.file').val();;
 var emoji_id = sesJqueryObject(that).find('.select_emoji_id').val();
 var gif_id = sesJqueryObject(that).find('.select_gif_id').val();
 if(((!body && (file_id == 0)) && emoji_id == 0 && gif_id == 0))
  return false;
  var guid = "";
  var executed = false;
  if(!sesJqueryObject('.select_file').val()){
    sesJqueryObject('.select_file').remove();
    executed = true;
  }
  
   var formData = new FormData(that);
   if(executed == true)
    sesJqueryObject(that).find('.file_comment_select').parent().append('<input type="file" name="Filedata" class="select_file" multiple="" value="0" style="display:none;">');
  //page
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sespage_switcher_cnt').find('.sespage_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
  //group
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sesgroup_switcher_cnt').find('.sesgroup_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);    
  }
  //business
  var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.sesbusiness_switcher_cnt').find('.sesbusiness_feed_change_option_a');
  if(elem.length){
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
    //store
    var elem = sesJqueryObject(that).closest('.comment-feed').find('.feed_item_date ul').find('.estore_switcher_cnt').find('.estore_feed_change_option_a');
    if(elem.length){
        guid = elem.attr('data-subject');
        formData.append('guid', guid);
    }
  formData.append('bodymention', body);
  submitCommentFormAjax = sesJqueryObject.ajax({
      type:'POST',
      url: en4.core.baseUrl+'sesadvancedcomment/index/comment/',
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        sesJqueryObject(that).removeClass('submitting');
        sesJqueryObject(that).find('.sesbasic_loading_cont_overlay').remove();
        try{
          var dataJson = sesJqueryObject.parseJSON(data);
          if(dataJson.status == 1){
            var elemS = sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul');
            var getPreviousSearchComment = sesJqueryObject('.comment_stats_'+action_id).html();
            if(elemS.find('.sesadvcmt_comments_stats').length){
              sesJqueryObject(dataJson.content).insertAfter(sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.sesadvcmt_comments_stats'));
              sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.sesadvcmt_comments_stats').replaceWith(dataJson.commentStats);
              var commentCount = sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.sesadvcmt_comments_stats').find('a.comment_btn_open').html();
            }else{
              sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').prepend(dataJson.content);
              sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').prepend(dataJson.commentStats);
               var commentCount = sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.sesadvcmt_comments_stats').find('a.comment_btn_open').html();
            }
            sesJqueryObject('.comment_stats_'+action_id).html(getPreviousSearchComment).find('a.comment_btn_open').html(commentCount);
            sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.comment_form').find('.body').val('');
						sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.comment_form').find('.body').css('height','auto');
           var fileElem =  sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.comment_form').find('._sesadvcmt_post_icons').find('span');
           fileElem.find('.select_file').val('');
           fileElem.find('.select_emoji_id').val('');
           fileElem.find('.select_gif_id').val('');
           fileElem.find('.file_id').val('0');
           sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.comment_form').find('.body').parent().parent().find('div').eq(0).html('');
           sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.uploaded_file').html('');
            sesJqueryObject(that).closest('.comment-feed').find('.comments').find('.sesadvancedactivity-comment-form').find('._form_container').find('.upload_file_cnt').remove();
            en4.core.runonce.trigger();
            complitionRequestTrigger();
          //silence
          }else{
            alert('Something went wrong, please try again later');	
          }
        }catch(err){
          //silence
        }
      },
      error: function(data){
        //silence
      }
  });   
}
sesJqueryObject(document).on('submit','.sesadvancedactivity-comment-form',function(e){
 e.preventDefault();
 getMentionData(this);
});
//upload image in comment
sesJqueryObject(document).on('click','.file_comment_select',function(e){
   sesJqueryObject(this).parent().find('.select_file').trigger('click');
});
//input file change value
sesJqueryObject(document).on('change','.select_file',function(e){
  var files = this.files;
   for (var i = 0; i < files.length; i++) 
   {
			var url = files[i].name;
    	var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
			if((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF')){
				uploadImageOnServer(this,files[i]);
			}
   }
    sesJqueryObject(this).val('');
});

function uploadImageOnServer(that,file){
  var form = sesJqueryObject(that).closest('form');
  if(!form.find('.upload_file_cnt').length){
    var container = sesJqueryObject('<div class="upload_file_cnt"></div>').insertAfter(sesJqueryObject(form).find('.uploaded_file')); 
  }else
    var container = form.find('.upload_file_cnt');
  var uploadFile = sesJqueryObject('<div class="uploaded_file"></div>')
  var uploadImageLoader = sesJqueryObject('<img src="application/modules/Core/externals/images/loading.gif" class="_loading" />').appendTo(uploadFile);
  sesJqueryObject(uploadFile).appendTo(container);
  complitionRequestTrigger();
  var formData = new FormData(sesJqueryObject(that).closest('form').get(0));
  formData.append('Filedata', file);
  submitCommentFormAjax = sesJqueryObject.ajax({
      type:'POST',
      url: en4.core.baseUrl+'sesadvancedcomment/index/upload-file/',
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
        var dataJson = data;
        try{
          var dataJson = sesJqueryObject.parseJSON(data);
          if(dataJson.status == 1){
            if(sesJqueryObject(form).find('.file_id').val() == 0)
              uploadFileId = '';
            else
              uploadFileId = sesJqueryObject(form).find('.file_id').val();
            sesJqueryObject(form).find('.file_id').val(uploadFileId+dataJson.photo_id+'_album_photo'+',');
            sesJqueryObject(uploadFile).html('<img src="'+dataJson.src+'"><a href="javascript:;" data-url="'+dataJson.photo_id+'" class="cancel_upload_file fas fa-times" title="Cancel"></a>');
            complitionRequestTrigger();
              //silence
          }else{
            //sesJqueryObject(form).find('.file_id').val('');
            //sesJqueryObject(form).find('.uploaded_file').hide();
            sesJqueryObject(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');	
          }
        }catch(err){
          sesJqueryObject(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');	
          //silence
        }
      },
      error: function(data){
        sesJqueryObject(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');	
        //silence
      }
  }); 
  
}
//emoji select in comment
sesJqueryObject(document).click(function(e) {
  if(sesJqueryObject(e.target).hasClass('gif_comment_select') ||sesJqueryObject(e.target).hasClass('emoji_comment_select') || sesJqueryObject(e.target).hasClass('feeling_emoji_comment_select')  || sesJqueryObject(e.target).attr('id') == 'sesadvancedactivityemoji-edit-a' || sesJqueryObject(e.target).attr('id') == "emotions_target" || sesJqueryObject(e.target).attr('id') == "sesadvancedactivity_feeling_emojis" || sesJqueryObject(e.target).attr('id') == 'sesadvancedactivity_feeling_emojisa')
    return;
  var container = sesJqueryObject('.ses_emoji_container');
  if ((!container.is(e.target) && container.has(e.target).length === 0)) {
     sesJqueryObject('.emoji_comment_select').removeClass('active');
     sesJqueryObject('.ses_emoji_container').hide();
  }
 
  //Feeling Plugin: Emojis Work
  var container = sesJqueryObject('.ses_feeling_emoji_container');
  if ((!container.is(e.target) && container.has(e.target).length === 0)) {
    sesJqueryObject('.feeling_emoji_comment_select').removeClass('active');
    sesJqueryObject('.ses_feeling_emoji_container').hide();
  }
  //Feeling Plugin: Emojis Work
  
});

var requestEmojiA;
sesJqueryObject(document).on('click','#sesadvancedactivityemoji-statusbox',function(){
    var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
    topPositionOfParentDiv = topPositionOfParentDiv;
    var leftSub = 264;
    var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
    leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
    sesJqueryObject(this).parent().find('.ses_emoji_container').css('right',0);
    sesJqueryObject(this).parent().find('.ses_emoji_container').show();

    if(sesJqueryObject(this).hasClass('active')){
      sesJqueryObject(this).removeClass('active');
      sesJqueryObject('#sesadvancedactivityemoji_statusbox').hide();
      return false;
     }
      sesJqueryObject(this).addClass('active');
      sesJqueryObject('#sesadvancedactivityemoji_statusbox').show();
      if(sesJqueryObject(this).hasClass('complete'))
        return false;
       if(typeof requestEmojiA != 'undefined')
        requestEmojiA.cancel();
       var that = this;
       var url = en4.core.baseUrl + 'sesadvancedactivity/ajax/emoji/';
       requestEmojiA = new Request.HTML({
        url : url,
        data : {
          format : 'html',
        },
        evalScripts : true,
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
          sesJqueryObject('#sesadvancedactivityemoji_statusbox').find('.ses_emoji_container_inner').find('.ses_emoji_holder').html(responseHTML);
          sesJqueryObject(that).addClass('complete');
          sesJqueryObject('#sesadvancedactivityemoji_statusbox').show();
         jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
            theme:"minimal-dark"
         });
        }
      });
     requestEmojiA.send();
});

sesJqueryObject(document).on('click','a.emoji_comment_select',function(){
  sesJqueryObject("#emoji_close").hide();
  sesJqueryObject("#sticker_close").show();
  clickEmojiContentContainer = this;
  sesJqueryObject('.emoji_content').removeClass('from_bottom');
  var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
	topPositionOfParentDiv = topPositionOfParentDiv;
  if(sesJqueryObject(this).hasClass('sesadv_outer_emoji')){
    var leftSub = 265;  
  }else if(sesJqueryObject(this).hasClass('activity_emoji_content_a') && typeof sesadvancedactivityDesign != 'undefined' && sesadvancedactivityDesign == 2){
    var leftSub = 55;  
  }else
    var leftSub = 264;
	var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
	leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
  if(sesJqueryObject('#ses_media_lightbox_container').length || sesJqueryObject('#ses_media_lightbox_container_video').length)
    topPositionOfParentDiv = topPositionOfParentDiv + offsetY;
	sesJqueryObject('._emoji_content').css('top',topPositionOfParentDiv+'px');
	sesJqueryObject('._emoji_content').css('left',leftPositionOfParentDiv).css('z-index',100);
  sesJqueryObject('._emoji_content').show();
  var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
  var availableSpace = sesJqueryObject(document).height() - eTop;
  if(availableSpace < 400 && !sesJqueryObject('#ses_media_lightbox_container').length){
      sesJqueryObject('.emoji_content').addClass('from_bottom');
  }
  if(sesJqueryObject(this).hasClass('active')){
    sesJqueryObject(this).removeClass('active');
    sesJqueryObject('.emoji_content').hide();
    complitionRequestTrigger();
    return;
   }
    sesJqueryObject(this).addClass('active');
    sesJqueryObject('.emoji_content').show();
    sesJqueryObject("#sesadvancedactivityemoji_statusbox").hide();
    complitionRequestTrigger();

    if(!sesJqueryObject('.ses_emoji_holder').find('.empty_cnt').length)
      return;
     var that = this;
     var url = en4.core.baseUrl+'sesadvancedcomment/index/emoji/',
     requestComentEmoji = new Request.HTML({
      url : url,
      data : {
        format : 'html',
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('.emoji_content').find('.ses_emoji_container_inner').find('.ses_emoji_holder').html(responseHTML);
        sesJqueryObject(that).addClass('complete');
        sesJqueryObject('._emoji_content').show();
        complitionRequestTrigger();
				jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
					theme:"minimal-dark"
				});
      }
    });
   requestComentEmoji.send();
});
sesJqueryObject(document).on('click','.select_comment_emoji_adv > img',function(e){
  var code = sesJqueryObject(this).parent().parent().attr('rel');
  var form = sesJqueryObject(this).closest('form');
  if(!sesJqueryObject(form).find('.comment_form').length){
    var html = form.find('.body').html();
    form.find('.body').val(html+' '+code);
  }else{
    var html = form.find('.comment_form').find('.body').val();
    form.find('.comment_form').find('.body').val(html+' '+code);
  }
  var aEmoji = sesJqueryObject(this).closest('.emoji_content').first().parent().find('a.emoji_comment_select').trigger('click');
  complitionRequestTrigger();
});

//GIF Work
sesJqueryObject(document).on('click','a.gif_comment_select',function() {
  sesJqueryObject("#sticker_close").hide();
  sesJqueryObject("#emoji_close").show();
  clickGifContentContainer = this;
  sesJqueryObject('.gif_content').removeClass('from_bottom');
  var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;
  if(sesJqueryObject(this).hasClass('activity_gif_content_a') && typeof sesadvancedactivityDesign != 'undefined' && sesadvancedactivityDesign == 2){
    var leftSub = 55;  
  }else
    var leftSub = 264;
  
    var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
    leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
    if(sesJqueryObject('#ses_media_lightbox_container').length || sesJqueryObject('#ses_media_lightbox_container_video').length)
      topPositionOfParentDiv = topPositionOfParentDiv + offsetY;
    sesJqueryObject('._gif_content').css('top',topPositionOfParentDiv+'px');
    sesJqueryObject('._gif_content').css('left',leftPositionOfParentDiv).css('z-index',100);
    sesJqueryObject('._gif_content').show();
    var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
    var availableSpace = sesJqueryObject(document).height() - eTop;
    if(availableSpace < 400 && !sesJqueryObject('#ses_media_lightbox_container').length){
      sesJqueryObject('.gif_content').addClass('from_bottom');
    }

    if(sesJqueryObject(this).hasClass('active')){
      sesJqueryObject(this).removeClass('active');
      sesJqueryObject('.gif_content').hide();
      complitionRequestTrigger();
      return;
    }
    
    sesJqueryObject(this).addClass('active');
    sesJqueryObject('.gif_content').show();
    complitionRequestTrigger();

    if(!sesJqueryObject('.ses_gif_holder').find('.empty_cnt').length)
      return;

    var that = this;
    var url = en4.core.baseUrl+'sesfeedgif/index/gif/',
    requestComentGif = new Request.HTML({
      url : url,
      data : {
        format : 'html',
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('.gif_content').find('.ses_gif_container_inner').find('.ses_gif_holder').html(responseHTML);
        sesJqueryObject(that).addClass('complete');
        sesJqueryObject('._gif_content').show();
        complitionRequestTrigger();
        jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
          theme:"minimal-dark"
        });
      }
    });
    requestComentGif.send();
});

var clickGifContentContainer;
function activityGifFeedAttachment(that){
  var code = sesJqueryObject(that).parent().parent().attr('rel');
  var image = sesJqueryObject(that).attr('src');
  composeInstance.plugins.each(function(plugin) {
    plugin.deactivate();
    sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
  });
  sesJqueryObject('#fancyalbumuploadfileids').val('');
  sesJqueryObject('.fileupload-cnt').html('');
  composeInstance.getTray().empty();
  sesJqueryObject('#compose-tray').show();
  sesJqueryObject('#compose-tray').html('<div class="sesact_composer_gif"><img src="'+image+'"><a class="remove_gif_image_feed notclose fas fa-times" href="javascript:;"></a></div>');
  sesJqueryObject('#image_id').val(code);
  sesJqueryObject('.gif_content').hide();  
  sesJqueryObject('.gif_comment_select').removeClass('active');
  
  //Feed Background Image Work
  if($('feedbgid') && sesJqueryObject('#image_id').val()) {
    $('hideshowfeedbgcont').style.display = 'none';
    sesJqueryObject('#feedbgid_isphoto').val(0);
    sesJqueryObject('.sesact_post_box').css('background-image', 'none');
    sesJqueryObject('#activity-form').removeClass('feed_background_image');
    sesJqueryObject('#feedbg_content').css('display','none');
  }
}
sesJqueryObject(document).on('click','._sesadvgif_gif > img',function(e){
  if(sesJqueryObject(clickGifContentContainer).hasClass('activity_gif_content_a')){
    activityGifFeedAttachment(this);  
  }else
    commentGifContainerSelect(this);
  sesJqueryObject('.exit_gif_btn').trigger('click');
});

function commentGifContainerSelect(that){
  var code = sesJqueryObject(that).parent().parent().attr('rel');
  var elem = sesJqueryObject(clickGifContentContainer).parent();
  var elemInput = elem.parent().find('span').eq(0).find('.select_gif_id').val(code);
  elem.closest('form').trigger('submit');
}

/*ACTIVITY FEED*/
sesJqueryObject(document).on('click','.remove_gif_image_feed',function(){
  composeInstance.getTray().empty();
  sesJqueryObject('#image_id').val('');
  sesJqueryObject('#compose-tray').hide();
  
  //Feed Background Image Work
  if($('feedbgid') && sesJqueryObject('#image_id').val() == '') {
    var feedbgid = sesJqueryObject('#feedbgid').val();
    $('hideshowfeedbgcont').style.display = 'block';
    sesJqueryObject('#feedbg_content').css('display','block');
    var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
    sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
    sesJqueryObject('#feedbgid_isphoto').val(1);
    sesJqueryObject('#feedbg_main_continer').css('display','block');
    if(feedbgid) {
      sesJqueryObject('#activity-form').addClass('feed_background_image');
    }
  }
});
var gifsearchAdvReq;

var canPaginatePageNumber = 1;
sesJqueryObject(document).on('keyup change','.search_sesgif',function(){
  var value = sesJqueryObject(this).val();
  if(!value){
    sesJqueryObject('.main_search_category_srn').show();
    sesJqueryObject('.main_search_cnt_srn').hide();
    return;
  }
  sesJqueryObject('.main_search_category_srn').hide();
  sesJqueryObject('.main_search_cnt_srn').show();
  if(typeof gifsearchAdvReq != 'undefined') {
    gifsearchAdvReq.cancel();
    isGifRequestSend = false;
  }
  document.getElementById('main_search_cnt_srn').innerHTML = '<div class="sesgifsearch sesbasic_loading_container" style="height:100%;"></div>';
  canPaginatePageNumber = 1;
  searchGifContent();
});

var isGifRequestSend = false;
function searchGifContent(valuepaginate) {
  
  var value = '';
  var search_sesgif = sesJqueryObject('.search_sesgif').val();
  
  if(isGifRequestSend == true)
    return;
  
  if(typeof valuepaginate != 'undefined') {
    value = 1;
    document.getElementById('main_search_cnt_srn').innerHTML = document.getElementById('main_search_cnt_srn').innerHTML + '<div class="sesgifsearchpaginate sesbasic_loading_container" style="height:100%;"></div>';
  }
  
  isGifRequestSend = true;
  gifsearchAdvReq = (new Request.HTML({
    method: 'post',
    'url': en4.core.baseUrl + "sesfeedgif/index/search-gif/",
    'data': {
      format: 'html',
        text: search_sesgif,
        page: canPaginatePageNumber,
        is_ajax: 1,
        searchvalue: value,
    },
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
      
      sesJqueryObject('.sesgifsearch').remove();
      sesJqueryObject('.sesgifsearchpaginate').remove();
      
      if(sesJqueryObject('.sesfeedgif_search_results').length == 0)
      sesJqueryObject('#main_search_cnt_srn').append(responseHTML);
      else 
        sesJqueryObject('.sesfeedgif_search_results').append(responseHTML);
      
      //document.getElementById('main_search_cnt_srn').innerHTML = document.getElementById('main_search_cnt_srn').innerHTML + responseHTML;
      
      
      //sesJqueryObject('.main_search_cnt_srn').html(responseHTML);
      
//       jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
//         theme:"minimal-dark"
//       });
      
      sesJqueryObject('.main_search_cnt_srn').slimscroll({
        height: 'auto',
        alwaysVisible :true,
        color :'#000',
        railOpacity :'0.5',
        disableFadeOut :true,
      });
            
      sesJqueryObject('.main_search_cnt_srn').slimscroll().bind('slimscroll', function(event, pos) {
        if(canPaginateExistingPhotos == '1' && pos == 'bottom' && sesJqueryObject('.sesgifsearchpaginate').length == 0) {
          sesJqueryObject('.sesbasic_loading_container').css('position','absolute').css('width','100%').css('bottom','5px');
          searchGifContent(1);
        }
      });
      isGifRequestSend = false;
    }
  })).send();
}
//GIF Work End


//Feeling Plugin: Emojis Work
sesJqueryObject(document).on('click','.feeling_emoji_comment_select',function(){

  clickFeelingEmojiContentContainer = this;

  sesJqueryObject('.feeling_emoji_content').removeClass('from_bottom');
  
  var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;

  if(sesJqueryObject(this).hasClass('feeling_activity_emoji_content_a') && typeof sesadvancedactivityDesign != 'undefined' && sesadvancedactivityDesign == 2) {
    var leftSub = 55;  
  } else
    var leftSub = 264;
    
  var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
  leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
  
  if(sesJqueryObject('#ses_media_lightbox_container').length || sesJqueryObject('#ses_media_lightbox_container_video').length)
    topPositionOfParentDiv = topPositionOfParentDiv + offsetY;

  sesJqueryObject('._feeling_emoji_content').css('top',topPositionOfParentDiv+'px');
  sesJqueryObject('._feeling_emoji_content').css('left',leftPositionOfParentDiv).css('z-index',100);
  sesJqueryObject('._feeling_emoji_content').show();
  var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
  var availableSpace = sesJqueryObject(document).height() - eTop;
  
  if(availableSpace < 400 && !sesJqueryObject('#ses_media_lightbox_container').length){
      sesJqueryObject('.feeling_emoji_content').addClass('from_bottom');
  }
  
  if(sesJqueryObject(this).hasClass('active')) {
    sesJqueryObject(this).removeClass('active');
    sesJqueryObject('.feeling_emoji_content').hide();
    complitionRequestTrigger();
    return false;
  }
  sesJqueryObject(this).addClass('active');
  sesJqueryObject('.feeling_emoji_content').show();

  complitionRequestTrigger();
  
  if(!sesJqueryObject('.ses_feeling_emoji_holder').find('.empty_cnt').length)
    return;
  
//   if(sesJqueryObject(this).hasClass('complete'))
//     return false;
//   
//   if(typeof feeling_requestEmoji != 'undefined')
//     feeling_requestEmoji.cancel();
  
  var that = this;
  var url = en4.core.baseUrl+'sesemoji/index/feelingemojicomment/',
  feeling_requestEmoji = new Request.HTML({
    url : url,
    data : {
      format : 'html',
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
      sesJqueryObject('.ses_feeling_emoji_holder').html(responseHTML);
      sesJqueryObject(that).addClass('complete');
      sesJqueryObject('.feeling_emoji_content').show();
      complitionRequestTrigger();
      jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
        theme:"minimal-dark"
      });
    }
  });
  feeling_requestEmoji.send();
});
//Feeling Plugin: Emojis Work


//like member
sesJqueryObject(document).on('click','ul.like_main_cnt_reaction li > a',function(){
    var relAttr = sesJqueryObject(this).attr('data-rel');
    var typeData = sesJqueryObject(this).attr('data-type');
    sesJqueryObject('.like_main_cnt_reaction > li').removeClass('active');
    sesJqueryObject(this).parent().addClass('active');
    sesJqueryObject('.sesact_mlist_popup_cont > .container_like_contnent_main').hide();
    var elem = sesJqueryObject('#container_like_contnent_'+relAttr);
    elem.show();
    if(typeData == 'comment')
      var typeData = 'sesadvancedcomment';
    else
      var typeData = 'sesadvancedactivity';
    if(elem.find('ul').find('.nocontent').length){
      var url = en4.core.baseUrl+typeData+'/ajax/likes/';
      complitionRequestTrigger();
     var requestComentEmojiContent = new Request.HTML({
      url : url,
      data : {
        format : 'html',
        id: elem.find('ul').find('.nocontent').attr('data-id'),
        resource_type: elem.find('ul').find('.nocontent').attr('data-resourcetype'),
        typeSelected: elem.find('ul').find('.nocontent').attr('data-typeselected'),
        item_id : elem.find('ul').find('.nocontent').attr('data-itemid'),
        page: 1,   
        type:relAttr, 
        is_ajax_content : 1,
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject(elem.find('ul')).html(responseHTML);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    });
    requestComentEmojiContent.send();
        
    }
    
});
function complitionRequestTrigger(){
	if(typeof feedUpdateFunction == "function")
	 feedUpdateFunction();
  sesJqueryObject(window).trigger('resize');
  //page
  var elem = sesJqueryObject('.sespage_feed_change_option_a');
  for(i=0;i<elem.length;i++){
    var imageItem = sesJqueryObject(elem[i]).attr('data-src');
    sesJqueryObject(elem[i]).closest('.comment-feed').find('.comment_usr_img').find('img').attr('src',imageItem);  
  }
  //group
  var elem = sesJqueryObject('.sesgroup_feed_change_option_a');
  for(i=0;i<elem.length;i++){
    var imageItem = sesJqueryObject(elem[i]).attr('data-src');
    sesJqueryObject(elem[i]).closest('.comment-feed').find('.comment_usr_img').find('img').attr('src',imageItem);  
  }
  //business
  var elem = sesJqueryObject('.sesbusiness_feed_change_option_a');
  for(i=0;i<elem.length;i++){
    var imageItem = sesJqueryObject(elem[i]).attr('data-src');
    sesJqueryObject(elem[i]).closest('.comment-feed').find('.comment_usr_img').find('img').attr('src',imageItem);  
  }
    //store
    var elem = sesJqueryObject('.estore_feed_change_option_a');
    for(i=0;i<elem.length;i++){
        var imageItem = sesJqueryObject(elem[i]).attr('data-src');
        sesJqueryObject(elem[i]).closest('.comment-feed').find('.comment_usr_img').find('img').attr('src',imageItem);
    }

};
/*Emotion Sticker*/
sesJqueryObject(document).on('click','.sesadv_emotion_btn_clk',function(e){
  var index = sesJqueryObject(this).parent().index();
  //For enable search work
  if(enablesearch == 0) {
    index = index -1;
  }
  var emojiCnt = sesJqueryObject('.ses_emoji_holder');
  emojiCnt.find('.emoji_content').hide();
  emojiCnt.find('.emoji_content').eq(index).show();
  var isComplete = sesJqueryObject(this).hasClass('complete')
  if(isComplete)
  return;
  var id = sesJqueryObject(this).attr('data-galleryid');
  var that = this;
  var emoji = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedcomment/ajax/emoji-content/gallery_id/'+id,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      sesJqueryObject(that).addClass('complete');
      emojiCnt.find('.emoji_content').eq(index).html(responseHTML);
      jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
          theme:"minimal-dark"
      });
    },
   error: function(data){
     //silence
    },
  });
});
var clickEmojiContentContainer;
function activityFeedAttachment(that){
  var code = sesJqueryObject(that).parent().parent().attr('rel');
  var image = sesJqueryObject(that).attr('src');
  composeInstance.plugins.each(function(plugin) {
    plugin.deactivate();
    sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
  });
  sesJqueryObject('#fancyalbumuploadfileids').val('');
  sesJqueryObject('.fileupload-cnt').html('');
  composeInstance.getTray().empty();
  sesJqueryObject('#compose-tray').show();
  sesJqueryObject('#compose-tray').html('<div class="sesact_composer_sticker"><img src="'+image+'"><a class="remove_reaction_image_feed notclose fas fa-times" href="javascript:;"></a></div>');
  sesJqueryObject('#reaction_id').val(code);
  sesJqueryObject('.emoji_content').hide();  
  sesJqueryObject('.emoji_comment_select').removeClass('active');
  
  //Feed Background Image Work
  if($('feedbgid') && sesJqueryObject('#reaction_id').val()) {
    
    //sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'block');
    $('hideshowfeedbgcont').style.display = 'none';
    sesJqueryObject('#feedbgid_isphoto').val(0);
    //sesJqueryObject('#feedbgid').val(0);
    sesJqueryObject('.sesact_post_box').css('background-image', 'none');
    sesJqueryObject('#activity-form').removeClass('feed_background_image');
    sesJqueryObject('#feedbg_content').css('display','none');
  }
  
  
}
sesJqueryObject(document).on('click','._simemoji_reaction > img',function(e){
  if(sesJqueryObject(clickEmojiContentContainer).hasClass('activity_emoji_content_a')){
    activityFeedAttachment(this);  
  }else
    commentContainerSelect(this);
  sesJqueryObject('.exit_emoji_btn').trigger('click');
});
function commentContainerSelect(that){
  var code = sesJqueryObject(that).parent().parent().attr('rel');
  var elem = sesJqueryObject(clickEmojiContentContainer).parent();
  var elemInput = elem.parent().find('span').eq(0).find('.select_emoji_id') .val(code);
  elem.closest('form').trigger('submit');  
}
/*ACTIVITY FEED*/
sesJqueryObject(document).on('click','.remove_reaction_image_feed',function(){
  composeInstance.getTray().empty();
  sesJqueryObject('#reaction_id').val('');
  sesJqueryObject('#compose-tray').hide();
  
  //Feed Background Image Work
  if($('feedbgid') && sesJqueryObject('#reaction_id').val() == '') {
    var feedbgid = sesJqueryObject('#feedbgid').val();
    $('hideshowfeedbgcont').style.display = 'block';
    sesJqueryObject('#feedbg_content').css('display','block');
    var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
    sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
    sesJqueryObject('#feedbgid_isphoto').val(1);
    sesJqueryObject('#feedbg_main_continer').css('display','block');
    if(feedbgid) {
      sesJqueryObject('#activity-form').addClass('feed_background_image');
    }
  }
});
var reactionsearchAdvReq;
sesJqueryObject(document).on('keyup change','.search_reaction_adv',function(){
   var value = sesJqueryObject(this).val();
   if(!value){
      sesJqueryObject('.main_search_category_srn').show();
      sesJqueryObject('.main_search_cnt_srn').hide();
      return;  
   }
    sesJqueryObject('.main_search_category_srn').hide();
    sesJqueryObject('.main_search_cnt_srn').show();
    if(typeof reactionsearchAdvReq != 'undefined')
      reactionsearchAdvReq.cancel();
     reactionsearchAdvReq = (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "sesadvancedcomment/ajax/search-reaction/",
      'data': {
        format: 'html',
        text: value,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('.main_search_cnt_srn').html(responseHTML);
        jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
          theme:"minimal-dark"
        });
      }
    })).send();
});
sesJqueryObject(document).on('click','.sesadv_reaction_cat',function(){
  var title = sesJqueryObject(this).data('title');
  sesJqueryObject('.search_reaction_adv').val(title);
  sesJqueryObject('.main_search_cnt_srn').html('')
  sesJqueryObject('.search_reaction_adv').trigger('change');
});
sesJqueryObject(document).on('click','.sesadv_reaction_remove_emoji, .sesadv_reaction_add_emoji',function(e){
  var add = sesJqueryObject(this).data('add');
  var remove = sesJqueryObject(this).data('remove');
  var gallery = sesJqueryObject(this).data('gallery');
  var title = sesJqueryObject(this).data('title');
  var src = sesJqueryObject(this).data('src');
  var index = sesJqueryObject(this).closest('._emoji_cnt').index() + 2;
  sesJqueryObject(this).prop("disabled", true);
  if(sesJqueryObject(this).hasClass('sesadv_reaction_remove_emoji')){
    var action = 'remove';
    sesJqueryObject('.sesadv_reaction_remove_emoji_'+gallery).html(add);
    sesJqueryObject('.sesadv_reaction_remove_emoji_'+gallery).removeClass('sesadv_reaction_remove_emoji').removeClass('sesadv_reaction_remove_emoji+'+gallery).addClass('sesadv_reaction_add_emoji').addClass('sesadv_reaction_add_emoji_'+gallery);
  }else{
    var action = 'add';
    sesJqueryObject('.sesadv_reaction_add_emoji_'+gallery).html(remove);
    sesJqueryObject('.sesadv_reaction_add_emoji_'+gallery).addClass('sesadv_reaction_remove_emoji').addClass('sesadv_reaction_remove_emoji_'+gallery).removeClass('sesadv_reaction_add_emoji').removeClass('sesadv_reaction_add_emoji_'+gallery);  
  }
  var that = this;
  reactionsearchAdvReq = (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "sesadvancedcomment/ajax/action-reaction/",
      'data': {
        format: 'html',
        gallery_id : gallery,
        actionD: action,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
          sesJqueryObject(that).prop("disabled", false);
         if(action == 'add'){
          var content = '<a data-galleryid="'+gallery+'" class="_headbtn sesadv_tooltip sesadv_emotion_btn_clk" title="'+title+'"><img src="'+src+'" alt="'+title+'"></a>';
          jqueryObjectOfSes(".ses_emoji_tabs").data('owlCarousel').addItem(content);
          sesJqueryObject(".ses_emoji_holder").append("<div style='display:none;position:relative;height:100%;' class='emoji_content'><div class='sesbasic_loading_container _emoji_cnt' style='height:100%;'></div></div>");
          sesadvtooltip();
          jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
            theme:"minimal-dark"
          });
        }else{
           jqueryObjectOfSes(".ses_emoji_tabs").data('owlCarousel').removeItem(index);
           jqueryObjectOfSes(".ses_emoji_holder > .emoji_content").eq(index).remove();
        }
      }
    })).send();
});
sesJqueryObject(document).on('click','.sesact_reaction_preview_btn',function(){
  var gallery = sesJqueryObject(this).data('gallery');
  sesJqueryObject('#sesact_reaction_gallery_cnt').hide();
  sesJqueryObject('.sesact_reaction_gallery_preview_cnt').show();
  if(sesJqueryObject('#sesact_reaction_preview_cnt_'+gallery).length){
     sesJqueryObject('#sesact_reaction_preview_cnt_'+gallery).show();
     return;
  }
  sesJqueryObject('.sesact_reaction_gallery_preview_cnt').append('<div class="sesbasic_loading_container _emoji_cnt sesact_reaction_gallery_preview_cnt_" id="sesact_reaction_preview_cnt_'+gallery+'" style="height:100%;"></div>');
  var reactionpreviewReq = (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "sesadvancedcomment/ajax/preview-reaction",
      'data': {
        format: 'html',
        gallery_id : gallery,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
         sesJqueryObject('#sesact_reaction_preview_cnt_'+gallery).html(responseHTML);
				 jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
          	theme:"minimal-dark"
   			 });
      }
    }));
    reactionpreviewReq.send();
});
sesJqueryObject(document).on('click','.sesact_back_store',function(){
  sesJqueryObject('#sesact_reaction_gallery_cnt').show();
  sesJqueryObject('.sesact_reaction_gallery_preview_cnt').hide();
  sesJqueryObject('.sesact_reaction_gallery_preview_cnt > .sesact_reaction_gallery_preview_cnt_').hide();
});
sesJqueryObject(document).on('click','.sesadvcnt_reset_emoji',function(){
  sesJqueryObject('.search_reaction_adv').val('').trigger('change');  
});

function carouselSesadvReaction(){
  jqueryObjectOfSes(".ses_emoji_tabs").owlCarousel({
        items : 6,
        itemsDesktop : [1199, 6],
        itemsDesktopSmall : [979, 6],
        itemsTablet : [768, 6],
        itemsMobile : [479, 6],
    navigation : true,
    pagination : false,
    loop: false,
    afterAction: function(){
      if ( this.itemsAmount > this.visibleItems.length ) {
        jqueryObjectOfSes('.owl-next').show();
        jqueryObjectOfSes('.owl-prev').show();
        jqueryObjectOfSes('.owl-next').show('');
        jqueryObjectOfSes('.owl-prev').show('');
        if ( this.currentItem == 0 ) {
          jqueryObjectOfSes('.owl-prev').hide();
        }
        if ( this.currentItem == this.maximumItem ) {
          jqueryObjectOfSes('.owl-next').hide('');
        }
      } else {
        jqueryObjectOfSes('.owl-next').hide();
        jqueryObjectOfSes('.owl-prev').hide();
      }
    },
  });  
}
/*FILTERING OPTIONS*/
sesJqueryObject(document).on('click','.search_adv_comment_a',function(e){
  if(sesJqueryObject(this).hasClass('active'))
    return;
  sesJqueryObject(this).closest('.search_adv_comment').find('li a').removeClass('active');
  sesJqueryObject(this).closest('.sesadvcmt_pulldown_wrapper').find('.search_advcomment_txt').find('span').text(sesJqueryObject(this).text());
  sesJqueryObject(this).addClass('active');
  var action_id =   sesJqueryObject(this).closest('.sesadvcmt_pulldown_wrapper').data('actionid');
  var ulObj = sesJqueryObject(this).closest('.comments_cnt_ul');
  var type = sesJqueryObject(this).data('type');
  if(ulObj.find('.sesadvcmt_comments_stats').length){
    ulObj.children().not(':first').remove();
    ulObj.append('<li style="position:relative" class="sesbasic_loading_container_li"><div class="sesbasic_loading_container" style="display:block;"></div></li>');
  }else{
    ulObj.html('<li style="position:relative"  class="sesbasic_loading_container_li"><div class="sesbasic_loading_container" style="display:block;"></div></li>');
  }
  sesadvancedcommentsearchaction(action_id,1,this,type,ulObj,sesJqueryObject(this).data('subjectype'));
});
//view more comment
function sesadvancedcommentsearchaction(action_id,page,obj,type,ulObj,subjectType){
  if(typeof subjectType != 'undefined')
    var url = en4.core.baseUrl + 'sesadvancedcomment/comment/list';
  else
    var url = en4.core.baseUrl + 'sesadvancedcomment/index/viewcomment';
  new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'page' : page,
        'action_id':action_id,
        'id':action_id,
        'type': subjectType,
        'searchtype':type,
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          try{
            var dataJson = sesJqueryObject.parseJSON(responseHTML);
            dataJson = dataJson.body;
          }catch(err){
             var dataJson = responseHTML;
          }
          ulObj.find('.sesbasic_loading_container_li').remove();
          ulObj.append(dataJson);
          en4.core.runonce.trigger();
          complitionRequestTrigger();
        }
      }
    }).send();
  
}

function removePreview(comment_id, type) {
  en4.core.request.send(new Request.HTML({
    method: 'post',
    'url': en4.core.baseUrl + 'sesadvancedcomment/index/removepreview',
    'data': {
      format: 'html',
      comment_id: comment_id,
      type: type,
      
    },
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
      if(document.getElementById('remove_previewli_'+ comment_id))
        document.getElementById('remove_previewli_'+ comment_id).destroy();
      if(document.getElementById('remove_preview_'+ comment_id))
        document.getElementById('remove_preview_'+ comment_id).destroy();
      if(document.getElementById('commentpreview_'+ comment_id))
        document.getElementById('commentpreview_'+ comment_id).destroy();
    }
  }));
  return false;
}

function showhidecommentsreply(comment_id, action_id) {
  if($('comments_reply_'+comment_id+'_'+action_id).style.display == 'block') {
    
    if($('comments_reply_'+comment_id+'_'+action_id))
      $('comments_reply_'+comment_id+'_'+action_id).style.display = 'none';
    
    if($('comments_reply_reply_'+comment_id+'_'+action_id))
      $('comments_reply_reply_'+comment_id+'_'+action_id).style.display = 'none';
    
    if($('comments_reply_body_'+comment_id))
      $('comments_reply_body_'+comment_id).style.display = 'none';
    
    if($('comments_body_'+comment_id))
      $('comments_body_'+comment_id).style.display = 'none';
    
    if(sesJqueryObject('#hideshow_'+comment_id+'_'+action_id))
      sesJqueryObject('#hideshow_'+comment_id+'_'+action_id).removeClass('fa fa-minus').addClass('far fa-plus-square');
  } else {
    
    if($('comments_reply_'+comment_id+'_'+action_id))
      $('comments_reply_'+comment_id+'_'+action_id).style.display = 'block';
    
    if($('comments_reply_reply_'+comment_id+'_'+action_id))
      $('comments_reply_reply_'+comment_id+'_'+action_id).style.display = 'block';
    
    if($('comments_reply_body_'+comment_id))
      $('comments_reply_body_'+comment_id).style.display = 'block';
    
    if($('comments_body_'+comment_id))
      $('comments_body_'+comment_id).style.display = 'block';
    
    if(sesJqueryObject('#hideshow_'+comment_id+'_'+action_id))
      sesJqueryObject('#hideshow_'+comment_id+'_'+action_id).removeClass('far fa-plus-square').addClass('fa fa-minus');
  }
}

sesJqueryObject(document).on('click','.sesadv_upvote_btn',function(){
  if(sesJqueryObject(this).hasClass('_disabled'))
    return;
  if(sesJqueryObject(this).closest('.advcomnt_feed_votebtn').hasClass('active'))
    return;
  sesJqueryObject(this).closest('.advcomnt_feed_votebtn').addClass('active');
  var itemguid  = sesJqueryObject(this).data('itemguid');
  var that = this;
  //var userguid  = sesJqueryObject(this).data('userguid');
  var guid = "";
   var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sespage_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesgroup_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesbusiness_switcher_cnt').find('a').first();
    if(!guidItem.length)
        var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
   if(guidItem)
    guid = guidItem.data('rel');
  var url  = en4.core.baseUrl + 'sesadvancedcomment/index/voteup';
  new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'itemguid' : itemguid,
        'userguid':guid,
        'type':'upvote',
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          sesJqueryObject(that).closest('.advcomnt_feed_votebtn').replaceWith(responseHTML);
        }
        sesJqueryObject(that).closest('.advcomnt_feed_votebtn').removeClass('active');
      }
    }).send();  
});
sesJqueryObject(document).on('click','.sesadv_downvote_btn',function(){
  if(sesJqueryObject(this).hasClass('_disabled'))
    return;
  if(sesJqueryObject(this).closest('.advcomnt_feed_votebtn').hasClass('active'))
    return;
  sesJqueryObject(this).closest('.advcomnt_feed_votebtn').addClass('active');
  var itemguid  = sesJqueryObject(this).data('itemguid');
  var that = this;
  //var userguid  = sesJqueryObject(this).data('userguid');
  var guid = "";
   var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sespage_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesgroup_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesbusiness_switcher_cnt').find('a').first();
    if(!guidItem.length)
        var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
   if(guidItem)
    guid = guidItem.data('rel');
  var url  = en4.core.baseUrl + 'sesadvancedcomment/index/voteup';
  new Request.HTML({
      'url' : url,
      'data' : {
        'format' : 'html',
        'itemguid' : itemguid,
        'userguid':guid,
        'type':'downvote',
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          sesJqueryObject(that).closest('.advcomnt_feed_votebtn').replaceWith(responseHTML);
        }
        sesJqueryObject(that).closest('.advcomnt_feed_votebtn').removeClass('active');
      }
    }).send();  
})
//like comment
sesJqueryObject(document).on('click','.sesadvancedcommentcommentlike',function(){
  var obj = sesJqueryObject(this);
	previousSesadvcommLikeObj = obj.closest('.sesadvcmt_hoverbox_wrapper');
  var action_id = sesJqueryObject(this).attr('data-actionid');
  //var guid = sesJqueryObject(this).attr('data-guid');
  var guid = "";
   var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sespage_switcher_cnt').find('a').first();
   if(!guidItem)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesgroup_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesbusiness_switcher_cnt').find('a').first();
    if(!guidItem.length)
        var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
   if(guidItem.length)
    guid = guidItem.data('rel');
  var comment_id = sesJqueryObject(this).attr('data-commentid');
  var type = sesJqueryObject(this).attr('data-type');
  var datatext = sesJqueryObject(this).attr('data-text');
  var subject_id = sesJqueryObject(this).attr('data-subjectid');
  //check for like
  var isLikeElem = false;
  if(sesJqueryObject(this).hasClass('reaction_btn')){
    var image = sesJqueryObject(this).find('.reaction').find('i').css('background-image');
    image = image.replace('url(','').replace(')','').replace(/\"/gi, "");
    var elem = sesJqueryObject(this).parent().parent().parent().find('a');
    isLikeElem = true;
  }else{
    var image = sesJqueryObject(this).parent().find('.sesadvcmt_hoverbox').find('span').first().find('.reaction_btn').find('.reaction').find('i').css('background-image');
    image = image.replace('url(','').replace(')','').replace(/\"/gi, "");
    var elem = sesJqueryObject(this);
    isLikeElem = false
  }
  
  var likeWorkText = sesJqueryObject(elem).attr('data-like');
  var unlikeWordText = sesJqueryObject(elem).attr('data-unlike');
  
  //unlike
  if(sesJqueryObject(elem).hasClass('_reaction') && !isLikeElem){
    sesJqueryObject(elem).find('i').removeAttr('style');
    sesJqueryObject(elem).find('span').html(unlikeWordText);
    sesJqueryObject(elem).removeClass('sesadvancedcommentcommentunlike').removeClass('_reaction').addClass('sesadvancedcommentcommentlike');
    sesJqueryObject(elem).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  }else{
  //like  
    sesJqueryObject(elem).find('i').css('background-image', 'url(' + image + ')');
    sesJqueryObject(elem).find('span').html(datatext);
    sesJqueryObject(elem).removeClass('sesadvancedcommentcommentlike').addClass('_reaction').addClass('sesadvancedcommentcommentunlike');
    sesJqueryObject(elem).parent().addClass('feed_item_option_unlike').removeClass('feed_item_option_like');
  }

// 	var parentObject = previousSesadvcommLikeObj.parent().html();
// 	var parentElem = previousSesadvcommLikeObj.parent();
// 	previousSesadvcommLikeObj.parent().html('');
// 	parentElem.html(parentObject);
	  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/like',
    data : {
      format : 'json',
      action_id : action_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
      guid : guid ,
       sbjecttype:sesJqueryObject(this).attr('data-sbjecttype'),
      subjectid:sesJqueryObject(this).attr('data-subjectid'),
      type:type
    },
    'onComplete' : function(responseHTML) {
      if( responseHTML ) {
        sesJqueryObject(obj).closest('.comments_date').replaceWith(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });    
  ajax.send();
});
//like feed action content
sesJqueryObject(document).on('click','.sesadvancedcommentcommentunlike',function(){
  var obj = sesJqueryObject(this);
  var action_id = sesJqueryObject(this).attr('data-actionid');
  var comment_id = sesJqueryObject(this).attr('data-commentid');
  var type = sesJqueryObject(this).attr('data-type');
   var datatext = sesJqueryObject(this).attr('data-text');
  var likeWorkText = sesJqueryObject(this).attr('data-like');
  var unlikeWordText = sesJqueryObject(this).attr('data-unlike');
  
  var guid = "";
   var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sespage_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesgroup_switcher_cnt').find('a').first();
   if(!guidItem.length)
    var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .sesbusiness_switcher_cnt').find('a').first();
    if(!guidItem.length)
        var guidItem = sesJqueryObject(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
   if(guidItem)
    guid = guidItem.data('rel');
  //check for unlike
  sesJqueryObject(this).find('i').removeAttr('style');
  sesJqueryObject(this).find('span').html(likeWorkText);
  sesJqueryObject(this).removeClass('sesadvancedcommentcommentunlike').removeClass('_reaction').addClass('sesadvancedcommentcommentlike');
  sesJqueryObject(this).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  var ajax = new Request.JSON({
    url : en4.core.baseUrl + 'sesadvancedcomment/index/unlike',
    data : {
      format : 'json',
      action_id : action_id,
      comment_id : comment_id,
      subject : en4.core.subject.guid,
      guid:guid,
       sbjecttype:sesJqueryObject(this).attr('data-sbjecttype'),
      subjectid:sesJqueryObject(this).attr('data-subjectid'),
      type:type
    },
    'onComplete' : function(responseHTML) {
      if( responseHTML ) {
        sesJqueryObject(obj).closest('.comments_date').replaceWith(responseHTML.body);
        en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
   ajax.send();
});
function setCommentFocus(comment_id)
{
  document.getElementById("comment"+comment_id).focus(); 
}