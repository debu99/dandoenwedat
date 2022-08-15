/* $Id:core.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

var sesadvancedactivityfeedactive;
var authSesactmyWindow;
function getMentionDataActivity(url,sharingPostText,orginalSharingText,formObj,getMentionText){
  if(sesJqueryObject('textarea#activity_body').attr('data-mentions-input')){
    sesJqueryObject('textarea#activity_body').mentionsInput('val', function(data) {
       submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,data);
    });
  }else{
      submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,'');
  }
}
function submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,getMentionText){
  if(typeof getMentionText == 'undefined')
  {
    getMentionDataActivity(url,sharingPostText,orginalSharingText,formObj,getMentionText);
    return;
  }
  url  = url +'/userphotoalign/'+userphotoalign;
  sesJqueryObject('#file_multi').remove();
  var formData = new FormData(formObj);
	formData.append('is_ajax', 1);
  formData.append('subject',en4.core.subject.guid);
  formData.append('body',getMentionText);
  var hashtag = sesJqueryObject('#hashtagtextsesadv').val();

  //page feed work
  var elemParent = sesJqueryObject('#sesact_post_box_status').find('.sespage_switcher_cnt').find('.sespage_feed_change_option_a');
  if(elemParent.length){
    formData.append('postingType', elemParent.attr('data-rel'));
  }
    //store feed work
    var elemParent = sesJqueryObject('#sesact_post_box_status').find('.estore_switcher_cnt').find('.estore_feed_change_option_a');
    if(elemParent.length){
        formData.append('postingType', elemParent.attr('data-rel'));
    }
  //group feed work
  var elemParent = sesJqueryObject('#sesact_post_box_status').find('.sesgroup_switcher_cnt').find('.sesgroup_feed_change_option_a');
  if(elemParent.length){
    formData.append('postingType', elemParent.attr('data-rel'));
  }

  //var data = composeInstance.getForm().toQueryString();
  if(url.indexOf('&') <= 0)
    url = url+'?';
  url = url+'is_ajax=true';
  if(hashtag)
    url = url+"&hashtag="+hashtag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage;

  if(typeof sesItemSubjectGuid != "undefined")
    var itemSubject = sesItemSubjectGuid;
  else
    var itemSubject = "";

  url = url+'&subjectPage='+itemSubject;

  sesJqueryObject('#compose-submit').html(sharingPostText);
  sesadvancedactivityfeedactive = sesJqueryObject.ajax({
      type:'POST',
      url: url,
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(responseHTML){
        try{
          var parseJson = sesJqueryObject.parseJSON(responseHTML);
          if(parseJson.status){
            if(hashtag && !parseJson.existsHashTag){
               var html = "Your post has been added to your <a href='"+parseJson.userhref+"'>profile</a> but won't appear in this feed because it doesn't mention ‪#"+hashtag+".";
              sesJqueryObject("<div class='schedule_post_cnt sesadv_success_msg'><span>"+html+"</span></div>").insertBefore('.sesadv_noresult_tip');
              setTimeout(function() {sesJqueryObject('.schedule_post_cnt').remove();}, 5000);
            }else if(parseJson.approveFeed != ""){
                var html = parseJson.approveFeed;
              sesJqueryObject("<div class='schedule_post_cnt sesadv_success_msg'><span>"+html+"</span></div>").insertBefore('.sesadv_noresult_tip');
              setTimeout(function() {sesJqueryObject('.schedule_post_cnt').remove();}, 5000);
            }else if(parseJson.videoProcess == 1){
              var html = "Your video is currently being processed - you will be notified when it is ready to be viewed. <a href='/"+videosURLsesvideos+"/manage'>Click here</a> to view uploaded video";
              sesJqueryObject("<div class='schedule_post_cnt sesadv_success_msg'><span>"+html+"</span></div>").insertBefore('.sesadv_noresult_tip');
              setTimeout(function() {sesJqueryObject('.schedule_post_cnt').remove();}, 30000);

            }else if(parseJson.scheduled_post && parseJson.scheduled_post_time){
              var html = en4.core.language.translate("Your post successfully scheduled on ")+parseJson.scheduled_post_time;
              sesJqueryObject("<div class='schedule_post_cnt sesadv_success_msg'><span>"+html+"</span></div>").insertBefore('.sesadv_noresult_tip');
              setTimeout(function() {sesJqueryObject('.schedule_post_cnt').remove();}, 5000);
            }else{
              sesJqueryObject('#activity-feed').prepend(parseJson.feed);
              Smoothbox.bind($('activity-feed'));
            }
            sesJqueryObject('.composer_crosspost_toggle').removeClass('composer_crosspost_toggle_active');
            sesJqueryObject('.sesact_content_pulldown').hide();
            sesJqueryObject('.sesact_content_pulldown_wrapper').find('a').removeClass('sesact_post_media_options_active');
            sesJqueryObject('.sesact_content_pulldown_list').find('input[type=checkbox]').prop('checked',false);
            // dont set if on action view page.
            if(typeof SesadvancedactivityUpdateHandler.options != 'undefined')
            SesadvancedactivityUpdateHandler.options.last_id = parseJson.last_id;
          }else{
             en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
             sesJqueryObject('#compose-submit').html(orginalSharingText);
            // clearInterval(dotsAnimationWhenPostingInterval);
             return;
          }
        }catch(e){
           en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
           sesJqueryObject('#compose-submit').html(orginalSharingText);
          // clearInterval(dotsAnimationWhenPostingInterval);
             return;
        }
        initSesadvAnimation();
        sesadvtooltip();
        sesJqueryObject('.sesadv_noresult_tip').hide();
        resetComposerBoxStatus();
        hideStatusBoxSecond();
        sesJqueryObject('#compose-submit').html(orginalSharingText);
         en4.core.runonce.trigger();
       // clearInterval(dotsAnimationWhenPostingInterval);
        if(sesJqueryObject('#hashtagtextsesadv').val()) {
         // sesJqueryObject('#activity_body').val('#'+sesJqueryObject('#hashtagtextsesadv').val());
         // sesJqueryObject('#activity_body').trigger('keyup');
          composeInstance.setContent('#'+sesJqueryObject('#hashtagtextsesadv').val());
        }
        activateFunctionalityOnFirstLoad();
      },
     error: function(data){
        en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
        sesJqueryObject('#compose-submit').html(orginalSharingText);
       // clearInterval(dotsAnimationWhenPostingInterval);
      },
    });
}
sesJqueryObject(document).on('click','.sesadv_approve_btn',function(){
  var url = sesJqueryObject(this).closest('form').attr('action');
  var actionid = sesJqueryObject(this).attr('data-url');
  sesJqueryObject('#activity-item-'+actionid).fadeOut("slow", function(){
    sesJqueryObject('#activity-item-'+actionid).remove();
  });
  sessmoothboxclose();
  sesJqueryObject.post(url,{approve:"1"},function(){

  })

})
var dotsAnimationWhenPosting = 0,dotsAnimationWhenPostingInterval;
function dotsAnimationWhenPostingFn(sharingPostText)
{
    if(dotsAnimationWhenPosting < 3)
    {
        if(dotsAnimationWhenPosting == 0)
          sesJqueryObject('#compose-submit').text(sharingPostText+'.');
        else if(dotsAnimationWhenPosting == 1)
          sesJqueryObject('#compose-submit').text(sharingPostText+'..');
        else
          sesJqueryObject('#compose-submit').text(sharingPostText+'...');
        dotsAnimationWhenPosting++;
    }
    else
    {
        sesJqueryObject('#compose-submit').text(sharingPostText);
        dotsAnimationWhenPosting = 0;
    }
}
sesJqueryObject(document).on('click','.close_parent_notification_sesadv',function(e){
  sesJqueryObject(this).closest('.parent_notification_sesadv').remove();
})
sesJqueryObject(document).on('click','.sesadvactivity_popup_preview',function(e){
   e.preventDefault();
    en4.core.showError('<div class="sesact_img_preview_popup"><div class="sesact_img_preview_popup_img"><img src="'+sesJqueryObject(this).attr('href')+'"> </div><div class="sesact_img_preview_popup_btm"><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button></div></div>');
		sesJqueryObject ('.sesact_img_preview_popup').parent().parent().addClass('sesact_img_preview_popup_wrapper');
});
sesJqueryObject(document).on('click','.buysell_img_a',function(){
  var image = sesJqueryObject(this).find('img').attr('src');
  sesJqueryObject('.sesact_sellitem_popup_photos_strip').find('.selected').removeClass('selected');
  sesJqueryObject(this).find('img').addClass('selected');
  sesJqueryObject('.selected_image_buysell').attr('src',image);
});
sesJqueryObject(document).on('click','.mark_as_sold_buysell',function(){
  var sold = sesJqueryObject(this).attr('data-sold');
  var href = sesJqueryObject(this).attr('data-href');
  sesJqueryObject('.mark_as_sold_buysell_'+href).removeClass('mark_as_sold_buysell');
  sesJqueryObject('.mark_as_sold_buysell_'+href).html('<i class="fa fa-check"></i>' + sold);
   var sesadvancedactivitybuysellsold = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/buysellsold/action_id/'+href,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
        //silence
    },
   error: function(data){

    },
  });
  sesadvancedactivitybuysellsold.send();
});
sesJqueryObject(document).on('click','.save_feed_adv, .unsave_feed_adv',function(){
  var save = sesJqueryObject(this).attr('data-save');
  var unsave = sesJqueryObject(this).attr('data-unsave');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  if(!save || !unsave || !actionid)
    return false;
  if(sesJqueryObject(this).hasClass('save_feed_adv')){
    sesJqueryObject(this).find('span').html(unsave);
    sesJqueryObject(this).removeClass('save_feed_adv').addClass('unsave_feed_adv');
  }else{
    sesJqueryObject(this).find('span').html(save);
    sesJqueryObject(this).addClass('save_feed_adv').removeClass('unsave_feed_adv');
  }
  var that = this;
  var elem = sesJqueryObject('.sesadv_active_tabs');
  if(elem.length)
  {
     var data = elem.find('a').attr('data-src');
     if(data == 'saved_feeds')
      sesJqueryObject('#activity-item-'+actionid).fadeOut("slow", function(){
        sesJqueryObject('#activity-item-'+actionid).remove();
        if(sesJqueryObject('#activity-feed').children().length)
       sesJqueryObject('.sesadv_noresult_tip').hide();
      else
       sesJqueryObject('.sesadv_noresult_tip').show();
      });

  }
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/savefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     //silence

    },
   error: function(data){

    },
  });
});
sesJqueryObject(document).on('click','.sesadv_feed_link',function(e){
  e.preventDefault();
  en4.core.showError("<div class='sesact_feedlink_popup'><div class='sesact_feedlink_popup_head'>"+en4.core.language.translate('Permalink of this Post')+"</div><div class='sesact_feedlink_popup_cont'><p>"+en4.core.language.translate('Copy link of this feed:')+"</p><p>" + '<input type="text" value="'+this.href+'" id="sesadv_link_feed_sel"></p>' + '<p><button onclick="openHrefWindow(\''+this.href+'\');">'+en4.core.language.translate("Go to this feed")+' </button><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button></p></div></div>');
  sesJqueryObject('#sesadv_link_feed_sel').select();
	sesJqueryObject ('.sesact_feedlink_popup').parent().parent().addClass('sesact_feedlink_popup_wrapper');
});
function openHrefWindow(href){
  window.location.href = href;
}

sesJqueryObject(document).on('click','.sesadvcommentable',function(e){
  e.preventDefault();
  var url = sesJqueryObject(this).attr('data-href');
  var enable = sesJqueryObject(this).attr('data-save');
  var disable = sesJqueryObject(this).attr('data-unsave');
  var commentable = sesJqueryObject(this).attr('data-commentable');
  if(!enable || !disable)
    return false;
  if(commentable == 0){
    sesJqueryObject(this).find('span').html(disable);
    sesJqueryObject(this).attr('data-commentable',1);
  }else{
    sesJqueryObject(this).find('span').html(enable);
    sesJqueryObject(this).attr('data-commentable',0);
  }
  var that = this;
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: url,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     if(responseHTML){
       var jsonObj = sesJqueryObject.parseJSON(responseHTML);
       if(jsonObj.status){
         var action_id = jsonObj.action_id;
         var feed  = jsonObj.feed;
         sesJqueryObject('#activity-item-'+action_id).replaceWith(feed);
       }
     }

    },
   error: function(data){

    },
  });
});
sesJqueryObject(document).on('click','.sesadv_hide_feed',function(){
  var name = sesJqueryObject(this).attr('data-name');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  var subjectid = sesJqueryObject(this).attr('data-subjectid');
  if(!name || !actionid || !subjectid)
    return false;

  var parent = sesJqueryObject(this).closest('.sesact_feed_header').parent();
  parent.find('.sesact_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.sesact_comments').hide();
  parent.find('.sesadv_hide').remove();
  parent.append('<div class="sesadv_hide"><a href="javasctipt:;" class="fas fa-times sesadv_hide_close sesadv_hide_close_fn" title="Close"></a><p>'+en4.core.language.translate("You won\'t see this post in Feed.")+' <a href="javascript:;" data-name="'+name+'" class="sesadv_undo_hide_feed" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p><div><p><a href="javascript:;" class="sesadv_hide_feed_all_feed" data-name="'+name+'" data-actionid="'+actionid+'">'+en4.core.language.translate("Hide all from")+'  '+name+'</a></p></div></div>');

  var that = this;
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/hidefeed/action_id/'+actionid+'/subject_id/'+subjectid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });
});
sesJqueryObject(document).on('click','.sesadv_report_feed',function(){
  var name = sesJqueryObject(this).attr('data-name');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  var guid = sesJqueryObject(this).attr('data-guid');
  if(!name || !actionid || !guid)
    return false;
  var reportLink = "report/create/subject/"+guid;
  var parent = sesJqueryObject(this).closest('.sesact_feed_header').parent();
  parent.find('.sesact_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.sesact_comments').hide();
  parent.find('.sesadv_hide').remove();
  parent.append('<div class="sesadv_hide"><a href="javasctipt:;" class="fas fa-times sesadv_hide_close sesadv_hide_close_fn" title="Close"></a><p>'+en4.core.language.translate("You won\'t see this post in Feed.")+' <a href="javascript:;" data-name="'+name+'" class="sesadv_undo_hide_feed" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p><div><p>'+en4.core.language.translate("If you find it offensive, please")+' <a href="javascript:;" onclick="openSmoothBoxInUrl(&#39;'+reportLink+'&#39;)" class="sesadv_report_feed" >'+en4.core.language.translate("file a report.")+'</a></p></div></div>');

  var that = this;
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/hidefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });

});
sesJqueryObject(document).on('click','.sesadv_hide_close_fn',function(e){
	sesJqueryObject(this).closest('li').remove();
});
sesJqueryObject(document).on('click','.sesadv_hide_feed_all',function(e){
  var name = sesJqueryObject(this).attr('data-name');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  if(!name || !actionid)
    return false;

  var parent = sesJqueryObject(this).closest('.sesact_feed_header').parent();
   parent.find('.sesact_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.sesact_comments').hide();
  parent.find('.sesadv_hide').remove();
  parent.append('<div class="sesadv_hide"><a href="javasctipt:;" class="fas fa-times sesadv_hide_close sesadv_hide_close_fn" title="Close"></a><p>'+en4.core.language.translate("You won\'t see")+' '+name+en4.core.language.translate("post in Feed.")+'  <a href="javascript:;" data-name="'+name+'" class="sesadv_undo_hide_feed_all" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p></div>');


  var list = getAllElementsWithAttributeElem('data-activity-feed-item');
  var lists = (list.join(','));
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/hidefeed/action_id/'+actionid+'/type/user/lists/'+lists,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      if(responseHTML){
        var json = sesJqueryObject.parseJSON(responseHTML);
        if(json.list){
          var list = json.list;
          for(i=0;i<list.length;i++){
            if(!sesJqueryObject('#activity-item-'+list[i]).find('.sesadv_hide').length)  {
                sesJqueryObject('#activity-item-'+list[i]).hide();
            }
          }
        }
      }
    },
   error: function(data){

    },
  });
});
function getAllElementsWithAttributeElem(attribute) {
    var matchingElements = [];
    var values = [];
    var allElements = document.getElementsByTagName('*');
    for (var i = 0; i < allElements.length; i++) {
      if (allElements[i].getAttribute(attribute)) {
        // Element exists with attribute. Add to array.
        matchingElements.push(allElements[i]);
        values.push(allElements[i].getAttribute(attribute));
        }
      }
    return values;
  }
sesJqueryObject(document).on('click','.sesadv_undo_hide_feed_all',function(e){
  var name = sesJqueryObject(this).attr('data-name');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  if(!name || !actionid)
    return false;
  var parent = sesJqueryObject(this).closest('li');
  parent.find('.sesact_feed_header').show();
  parent.find('.feed_item_body').show();
	 parent.find('.sesact_comments').show();
  parent.find('.sesadv_hide').remove();
  var that = this;
  var list = getAllElementsWithAttributeElem('data-activity-feed-item');
  var lists = (list.join(','));
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/hidefeed/action_id/'+actionid+'/remove/true/type/user/lists/'+lists,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      if(responseHTML){
        var json = sesJqueryObject.parseJSON(responseHTML);
        if(json.list){
          var list = json.list;
          for(i=0;i<list.length;i++){
            if(!sesJqueryObject('#activity-item-'+list[i]).find('.sesadv_hide').length)  {
                sesJqueryObject('#activity-item-'+list[i]).show();
            }
          }
        }
      }
    },
   error: function(data){

    },
  });
});
sesJqueryObject(document).on('click','.sesadv_hide_feed_all_feed',function(){
  var actionid = sesJqueryObject(this).attr('data-actionid');
  sesJqueryObject('.sesadv_hide_feed_all_'+actionid).trigger('click');
});
sesJqueryObject(document).on('click','.sesadv_undo_hide_feed',function(e){
  var name = sesJqueryObject(this).attr('data-name');
  var actionid = sesJqueryObject(this).attr('data-actionid');
  if(!name || !actionid)
    return false;
  var parent = sesJqueryObject(this).closest('li');
	parent.find('.sesact_feed_header').show();
  parent.find('.feed_item_body').show();
	 parent.find('.sesact_comments').show();
  parent.find('.sesadv_hide').remove();
  var that = this;
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/hidefeed/action_id/'+actionid+'/remove/true',
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });
});
function resetComposerBoxStatus(){
  composeInstance.getTray().empty();
  composeInstance.plugins.each(function(plugin) {
    if(plugin.getName() != "facebook" && plugin.getName() != "twitter"){
      plugin.detach();
    }
    plugin.active = false;
    sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
  });

  sesJqueryObject('.resetaftersubmit').val('');
  sesJqueryObject('.highlighter').html('');
  sesJqueryObject('#activity_body').css('height','auto');
  sesJqueryObject('#toValues-element, #tag_friend_cnt, #locValues-element, #location_elem_act').html('');
  sesJqueryObject('#compose-tray').hide();
  if(sesJqueryObject('#sesadvancedactivity_tag').hasClass('active')){
   sesJqueryObject('#sesadvancedactivity_tag').removeClass('active');
   sesJqueryObject('.sesact_post_tag_cnt').hide();
  }
  if(sespageContentSelected){
    sesJqueryObject('#sespage-element').hide();
    sesJqueryObject('.sespage_post_tag_input').show();
    sespageContentSelected = "";
  }
  sesJqueryObject('.sesact_post_page_container').hide();
  if(sesJqueryObject('#sesadvancedactivity_location').hasClass('active')){
    sesJqueryObject('#sesadvancedactivity_location').removeClass('active');
    sesJqueryObject('.sesact_post_location_container').hide();
  }

  //Feeling Work
  if(sesJqueryObject('#sesadvancedactivity_feelings').hasClass('active')){
    sesJqueryObject('#sesadvancedactivity_feelings').removeClass('active');
    sesJqueryObject('.sesact_post_feeling_container').hide();
    sesJqueryObject('#feeling_elem_act').hide();
    sesJqueryObject('#feelingActType').html('');
    sesJqueryObject('#feelingActType').hide();
  }


  if(sesJqueryObject('#sesadvancedactivity_shedulepost').hasClass('active')){
    sesJqueryObject('#sesadvancedactivity_shedulepost').removeClass('active');
    sesJqueryObject('#scheduled_post').hide();
  }
  sesJqueryObject('.fileupload-cnt').html('');
  if(typeof removeTargetPostValues == 'function')
    removeTargetPostValues();
  sesJqueryObject('#tag_location').css('display','inline-block');
  sesJqueryObject('#dash_elem_act, #tag_friend_cnt, #location_elem_act, #sespage_elem_act').hide();

  if(sesJqueryObject('#hashtagtextsesadv').val()) {
    sesJqueryObject('#activity_body').val('#'+sesJqueryObject('#hashtagtextsesadv').val()).trigger('keyup');

    //composeInstance.setContent('#'+sesJqueryObject('#hashtagtextsesadv').val()).trigger('keyup');
  }
}
sesJqueryObject(document).on('submit','#sesadv_settings_form',function(e){
  e.preventDefault();
    var checkbox_value = "";
    sesJqueryObject(".sesadvcheckbox").each(function () {
        var ischecked = sesJqueryObject(this).is(":checked");
        if (ischecked) {
            checkbox_value += sesJqueryObject(this).val() + ",";
        }
    });
  if(!checkbox_value)
    return false;
  var that = this;
  sesJqueryObject(this).find('.sesbasic_loading_cont_overlay').show();
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/settingremove/user/'+checkbox_value,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      //sessmoothboxclose();
      if(responseHTML){
        sesJqueryObject(that).html('<p style="margin:10px;">Your changes saved successfully.</p>');
        location.reload();
      }
    },
   error: function(data){

    },
  });
});
 en4.core.runonce.add(function() {
  //tooltip
   sesadvtooltip();
});
//edit feed from delete
sesJqueryObject(document).on('click','.edit_feed_edit',function(e){
  e.preventDefault();
  var id = sesJqueryObject('#sesact_adv_delete').find('.hidden_actn').val();
  sessmoothboxclose();
  setTimeout(function() {sesJqueryObject('#sesact_edit_'+id).trigger('click');}, 600);
});
sesJqueryObject(document).on('submit','#sesact_adv_delete',function(e){
  e.preventDefault();
   var id = sesJqueryObject('#sesact_adv_delete').find('.hidden_actn').val();
   if(typeof sesItemSubjectGuid != "undefined")
    var itemSubject = sesItemSubjectGuid;
  else
    var itemSubject = "";

  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/index/delete/action_id/'+id+'/subjectPage/'+itemSubject,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      sessmoothboxclose();
      if(responseHTML){
        sessmoothboxclose();
        sesJqueryObject('#activity-item-'+id).fadeOut("slow", function(){
          sesJqueryObject('#activity-item-'+id).remove();
          if(!sesJqueryObject('#activity-feed >li').length)
            sesJqueryObject('.sesadv_noresult_tip').show();
        });
      }
    },
   error: function(data){

    },
  });
});
sesJqueryObject(document).on('submit','#sesact_adv_comment_delete',function(e){
  e.preventDefault();
  var id = sesJqueryObject('#sesact_adv_comment_delete').find('.hidden_cmnt').val();
  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: sesJqueryObject( this ).attr( 'action' ),
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      sesJqueryObject("#comment-"+id).remove();
      sessmoothboxclose();
    },
   error: function(data){

    },
  });
});
function isTouchDevice(){
    return true == ("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch);
}

function sesadvtooltip(){
  if(typeof displayCommunityadsCarousel == "function"){
    displayCommunityadsCarousel()
  }
  if(isTouchDevice()===false) {
      sesJqueryObjectTooltip('.sesadv_tooltip').powerTip({
          smartPlacement: true
      });
      sesJqueryObjectTooltip(".sesadv_tooltip").each(function () {
          var thisCircle = sesJqueryObjectTooltip(this);
          thisCircle.data("powertip", thisCircle.attr("title"));
      });
  }
feedUpdateFunction();
  //sescommunityads

}
//reschedule post
sesJqueryObject(document).on('click','.sesadv_reschedule_post',function(e){
  sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').remove();
  sesJqueryObject('.sesadvancedactivity_shedulepost_edit_select').remove();
  e.preventDefault();
  var action_id = sesJqueryObject(this).data('actionid');
  var value = sesJqueryObject(this).data('value');
  var html = '<div class="sesadvancedactivity_shedulepost_edit_overlay sesact_popup_overlay"></div><div class="sesadvancedactivity_shedulepost_edit_select sesbasic_bxs sesact_popup"><div class="sesact_popup_header">Schedule Post</div><div class="sesact_popup_cont"><b>Schedule Your Post</b><p>Select date and time on which you want to publish your post.</p><div class="sesact_time_input_wrapper"><div id="datetimepicker_edit" class="input-append date sesact_time_input"><input type="text" name="scheduled_post" id="scheduled_post_edit" value="'+value+'" /><span class="add-on" title="Select Time" ><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div><input type="hidden" id="schedule_post_reschedule_action_id" value="'+action_id+'"><div class="sesact_error sesadvancedactivity_shedulepost_edit_error"></div></div></div><div class="sesact_popup_btns sesadvancedactivity_shedulepost_edit_btns"><button type="submit" class="schedule_post_schedue_edit">Reshedule</button><button class="close schedule_post_close_edit">Cancel</button></div></div>';
  sesJqueryObject(html).appendTo('body');
  sesJqueryObject('#schedule_post_reschedule_action_id').val(action_id);
  makeDateTimePicker();
  //sesadvtooltip();
});
sesJqueryObject(document).on('click','.schedule_post_close_edit',function(e){
  e.preventDefault();
  sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').remove();
  sesJqueryObject('.sesadvancedactivity_shedulepost_edit_select').remove();
});
sesJqueryObject(document).on('click','.schedule_post_schedue_edit',function(e){
  var value = sesJqueryObject('#scheduled_post_edit').val();
  if(sesJqueryObject('.sesadvancedactivity_shedulepost_edit_error').css('display') == 'block' || !value){
    return;
   }
   e.preventDefault();
   var actionid = sesJqueryObject('#schedule_post_reschedule_action_id').val();
   value = value.replace(/\//g,'_');
   sesJqueryObject('.sesadvancedactivity_shedulepost_edit_btns > buttons').prop('disabled',true);
  sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/index/reschedule-post/action_id/'+actionid+'/value/'+value,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      responseHTML = sesJqueryObject.parseJSON(responseHTML);
      if(responseHTML.status){
        sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').remove();
        sesJqueryObject('.sesadvancedactivity_shedulepost_edit_select').remove();
        sesJqueryObject('#activity-item-'+actionid).fadeOut("slow", function(){
           sesJqueryObject('#activity-item-'+actionid).replaceWith(responseHTML.feed);
           sesJqueryObject('#activity-item-'+actionid).fadeIn("slow");
           initSesadvAnimation();
           sesadvtooltip();
           return;
        });
      }else{
        alert('Something went wrong, please try again later.');
        sesJqueryObject('.sesadvancedactivity_shedulepost_edit_btns > buttons').prop('disabled',false);
        return;
      }
    },
   error: function(data){
     alert('Something went wrong, please try again later.');
     sesJqueryObject('.sesadvancedactivity_shedulepost_edit_btns > buttons').prop('disabled',false);
     return;
    },
  });
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
        var url = 'sesadvancedactivity/index/get-likes';
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
(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
en4.activity = {

  load : function(next_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;
    if(typeof sesItemSubjectGuid != "undefined")
    var itemSubject = sesItemSubjectGuid;
  else
    var itemSubject = "";

    $('feed_viewmore').style.display = 'none';
    $('feed_loading').style.display = '';
    var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'sesadvancedactivity/widget/feed?hashtag='+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
      data : {
        //format : 'json',
        'maxid' : next_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : subject_guid,
        'filterFeed':sesJqueryObject('.sesadvancedactivity_filter_tabs .active > a').attr('data-src'),
      }
      /*
      onSuccess : function(){
        $('feed_viewmore').style.display = '';
        $('feed_loading').style.display = 'none';
      }*/
    }), {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'append'
    });
  },

  like : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sesadvancedactivity/index/like',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : $('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : $('comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'sesactivity'
    });
  },

  unlike : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sesadvancedactivity/index/unlike',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : $('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : $('comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'sesactivity'
    });
  },

  comment : function(action_id, body) {
    if( body.trim() == '' )
    {
      return;
    }

    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sesadvancedactivity/index/comment',
      data : {
        format : 'json',
        action_id : action_id,
        body : body,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : $('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : $('comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'sesactivity'
    });
  },

  attachComment : function(formElement){
    var bind = this;
    formElement.addEvent('submit', function(event){
      event.stop();
      bind.comment(formElement.action_id.value, formElement.body.value);
    });
  },

  viewComments : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sesadvancedactivity/index/viewComment',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
      //'element' : $('comment-likes-activity-item-'+action_id),
    });
  },

  viewLikes : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sesadvancedactivity/index/viewLike',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

//   hideNotifications : function(reset_text) {
//     en4.core.request.send(new Request.JSON({
//       'url' : en4.core.baseUrl + 'activity/notifications/hide'
//     }));
//     $('updates_toggle').set('html', reset_text).removeClass('new_updates');
//
//     /*
//     var notify_link = $('core_menu_mini_menu_updates_count').clone();
//     $('new_notification').destroy();
//     notify_link.setAttribute('id', 'core_menu_mini_menu_updates_count');
//     notify_link.innerHTML = "0 updates";
//     notify_link.inject($('core_menu_mini_menu_updates'));
//     $('core_menu_mini_menu_updates').setAttribute('id', '');
//     */
//     if($('notifications_main')){
//       var notification_children = $('notifications_main').getChildren('li');
//       notification_children.each(function(el){
//           el.setAttribute('class', '');
//       });
//     }
//
//     if($('notifications_menu')){
//       var notification_children = $('notifications_menu').getChildren('li');
//       notification_children.each(function(el){
//           el.setAttribute('class', '');
//       });
//     }
//     //$('core_menu_mini_menu_updates').setStyle('display', 'none');
//   },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      //$('updates_toggle').set('html', responseJSON.text).addClass('new_updates');
    }
  },

  markRead : function (action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/test',
      data : {
        format     : 'json',
        'actionid' : action_id
      }
    }));
  },

  cometNotify : function(responseObject){
    //for( var x in responseObject ) alert(responseObject[x]);
    //if( $type(responseObject.text) ){
      $('core_menu_mini_menu_updates').style.display = '';
      $('core_menu_mini_menu_updates_count').innerHTML = responseObject.text;
    //}
  }

};

NotificationUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : false,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      minDelay : 5000,
      maxDelay : 600000,
      delayFactor : 1.5,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      subject_guid : null
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,

  initialize : function(options) {
    this.setOptions(options);
    this.options.minDelay = this.options.delay;
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.state= true;
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });

    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      this.options.delay = this.options.minDelay;
      //$('updates_toggle').set('html', responseJSON.text).addClass('new_updates');
    } else {
      this.options.delay = Math.min(this.options.maxDelay, this.options.delayFactor * this.options.delay);
    }
  },

  loop : function() {
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.updateNotifications().addEvent('complete', function() {
        this.loop.delay(this.options.delay, this);
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },

  // Utility

  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
      if( typeof(console) && $type(console) ) {
        //console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  }
});

//(function(){

  en4.activity.compose = {

    composers : {},

    register : function(object){
      name = object.getName();
      this.composers[name] = object;
    },

    deactivate : function(){
      for( var x in this.composers ){
        this.composers[x].deactivate();
      }
      return this;
    }

  };


  en4.activity.compose.icompose = new Class({

    Implements: [Events, Options],

    name : false,

    element : false,

    options : {},

    initialize : function(element, options){
      this.element = $(element);
      this.setOptions(options);
    },

    getName : function(){
      return this.name;
    },

    activate : function(){
      en4.activity.compose.deactivate();
    },

    deactivate : function(){

    }
  });

//})();

SesadvancedactivityUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : true,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      next_id : null,
      subject_guid : null,
      showImmediately : false
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,

  //loopId : false,

  initialize : function(options) {
    this.setOptions(options);
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this._log('activity loop onStateActive');
        this.activestate = 1;
        this.state = true;
      }.bind(this),
      'onStateIdle' : function() {
        this._log('activity loop onStateIdle');
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
    //this.loopId = this.loop.periodical(this.options.delay, this);
  },

  stop : function() {
    this.state = false;
  },

  checkFeedUpdate : function(action_id, subject_guid){
    if( en4.core.request.isRequestActive() || !sesAdvancedActivityGetFeeds || sesAdvancedActivityGetAction_id) return;

    function getAllElementsWithAttribute(attribute) {
      var matchingElements = [];
      var values = [];
      var allElements = document.getElementsByTagName('*');
      for (var i = 0; i < allElements.length; i++) {
        if (allElements[i].getAttribute(attribute)) {
          // Element exists with attribute. Add to array.
          matchingElements.push(allElements[i]);
          values.push(allElements[i].getAttribute(attribute));
          }
        }
      return values;
    }
    var list = getAllElementsWithAttribute('data-activity-feed-item');
    this.options.last_id = Math.max.apply( Math, list );
    min_id = this.options.last_id + 1;
    var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
    var req = new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/name/sesadvancedactivity.feed?hashtag='+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage,
      data : {
        'format' : 'html',
        'getUpdates':1,
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true,
        'filterFeed':sesJqueryObject('.sesadvancedactivity_filter_tabs .active > a').attr('data-src'),
      }
    });
    en4.core.request.send(req, {
      'element' : $('feed-update'),
      }
    );



    req.addEvent('complete', function() {
      (function() {
        if( this.options.showImmediately && $('feed-update').getChildren().length > 0 ) {
          $('feed-update').setStyle('display', 'none');
          $('feed-update').empty();
          this.getFeedUpdate(this.options.next_id);
          }
        }).delay(50, this);
    }.bind(this));



   // Start LOCAL STORAGE STUFF
   if(localStorage) {
     var pageTitle = document.title;
     //@TODO Refill Locally Stored Sesadvancedactivity Feed

     // For each activity-item, get the item ID number Data attribute and add it to an array
     var feed  = document.getElementById('activity-feed');
     // For every <li> in Feed, get the Feed Item Attribute and add it to an array
     var items = feed.getElementsByTagName("li");
     var itemObject = { };
     // Loop through each item in array to get the InnerHTML of each Sesadvancedactivity Feed Item
     var c = 0;
     for (var i = 0; i < items.length; ++i) {
       if(items[i].getAttribute('data-activity-feed-item') != null){
         var itemId = items[i].getAttribute('data-activity-feed-item');
         itemObject[c] = {id: itemId, content : document.getElementById('activity-item-'+itemId).innerHTML };
         c++;
         }
       }
     // Serialize itemObject as JSON string
     var activityFeedJSON = JSON.stringify(itemObject);
     localStorage.setItem(pageTitle+'-activity-feed-widget', activityFeedJSON);
   }


   // Reconstruct JSON Object, Find Highest ID
   if(localStorage.getItem(pageTitle+'-activity-feed-widget')) {
     var storedFeedJSON = localStorage.getItem(pageTitle+'-activity-feed-widget');
     var storedObj = eval ("(" + storedFeedJSON + ")");

     //alert(storedObj[0].id); // Highest Feed ID
    // @TODO use this at min_id when fetching new Sesadvancedactivity Feed Items
   }
   // END LOCAL STORAGE STUFF


   return req;
  },

  getFeedUpdate : function(last_id){
    if( en4.core.request.isRequestActive() || !sesAdvancedActivityGetFeeds || sesAdvancedActivityGetAction_id) return;
    sesJqueryObject("#count_new_feed").html('');
    sesJqueryObject("#count_new_feed").hide();
    var min_id = this.options.last_id + 1;
    this.options.last_id = last_id;
    document.title = this.title;
    sesJqueryObject('.sesadv_noresult_tip').hide();
    var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
     if(typeof sesItemSubjectGuid != "undefined")
    var itemSubject = sesItemSubjectGuid;
  else
    var itemSubject = "";
    var req = (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/name/sesadvancedactivity.feed?hashtag="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
      'data': {
        'format' : 'html',
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid,
        'filterFeed':sesJqueryObject('.sesadvancedactivity_filter_tabs .active > a').attr('data-src'),
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('#activity-feed').prepend(responseHTML);
        initSesadvAnimation();
        sesadvtooltip();
      }
    }));
    req.send();
    return req;
  },

  loop : function() {
    this._log('activity update loop start');

    if( !this.state ) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate().addEvent('complete', function() {
        try {
          this._log('activity loop req complete');
          this.loop.delay(this.options.delay, this);
        } catch( e ) {
          this.loop.delay(this.options.delay, this);
          this._log(e);
        }
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }

    this._log('activity update loop stop');
  },

  // Utility
  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    try {
      if( 'console' in window && typeof(console) && 'log' in console ) {
        //console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  }
});
})(); // END NAMESPACE

sesJqueryObject(document).on('click','.sesadv_schedule_btn',function(e){
  sesJqueryObject(this).parent().hide();
})
//buy sell navigation
sesJqueryObject(document).keydown(function(e) {
  if(!sesJqueryObject('.sesact_sellitem_popup_header').length)
    return;
  var elem = sesJqueryObject('.sesact_sellitem_popup_photos_strip').find('div').find('a');
  var length = elem.length;
  if(length < 2)
    return;
  var selectedIndex = elem.find('img.selected').parent().index();
  if(e.keyCode == 37 || e.keyCode == 38) { // left
   if(length <= (selectedIndex-1))
    elem.eq(length-1).trigger('click');
   else
    elem.eq(selectedIndex-1).trigger('click');
  }else if(e.keyCode == 39 || e.keyCode == 40) { // right
    if(length <= (selectedIndex+1))
      elem.eq(0).trigger('click');
    else
      elem.eq(selectedIndex+1).trigger('click');
  }
});
sesJqueryObject(document).on('click','.allowed_hide_post_sesadv',function(e){
  var actionid = sesJqueryObject(this).attr('data-src');
  if(!actionid)
    return;
  sesJqueryObject(this).closest('li').remove();
  if(!sesJqueryObject('#activity-feed').find('li').length)
  sesJqueryObject('.sesadv_noresult_tip').show();

  var savefeed = sesJqueryObject.ajax({
    type:'POST',
    url: 'sesadvancedactivity/ajax/unhidefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     //silence

    },
   error: function(data){

    },
  });
});
function isUrl(s) {
       return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(s);
}
sesJqueryObject(document).on('click','.pintotopfeedsesadv',function(e){
  var url = sesJqueryObject(this).data('url');
  window.location.href = url;
})
