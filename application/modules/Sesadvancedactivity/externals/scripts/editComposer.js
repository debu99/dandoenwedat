/* $Id:editComposer.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

sesJqueryObject(document).on('click','#sesadvancedactivity_location_edit, .seloc_clk_edit',function(e){
  that = sesJqueryObject(this);
  if(sesJqueryObject(this).hasClass('.seloc_clk_edit'))
     that = sesJqueryObject('#sesadvancedactivity_location_edit');
   if(sesJqueryObject(this).hasClass('active')){
     sesJqueryObject(this).removeClass('active');
     sesJqueryObject('.sesact_post_location_container_edit').hide();
     return;
   }
   sesJqueryObject('.sesact_post_location_container_edit').show();
   sesJqueryObject(this).addClass('active');
});
sesJqueryObject(document).on('click','#sesadvancedactivity_tag_edit, .sestag_clk_edit',function(e){
  that = sesJqueryObject(this);
  if(sesJqueryObject(this).hasClass('.sestag_clk_edit'))
     that = sesJqueryObject('#sesadvancedactivity_tag_edit');
   if(sesJqueryObject(that).hasClass('active')){
     sesJqueryObject(that).removeClass('active');
     sesJqueryObject('.sesact_post_tag_cnt_edit').hide();
     return;
   }
   sesJqueryObject('.sesact_post_tag_cnt_edit').show();
   sesJqueryObject(that).addClass('active');
});


//Feelings Work
sesJqueryObject(document).on('click','#sesadvancedactivity_feelings_editspan',function(e){
  that = sesJqueryObject(this);
  if(sesJqueryObject(this).hasClass('.seloc_clk_edit'))
     that = sesJqueryObject('#sesadvancedactivity_feelings_editspan');
   if(sesJqueryObject(this).hasClass('active')){
     sesJqueryObject(this).removeClass('active');
     sesJqueryObject('.sesact_post_feelingcontent_containeredit').hide();
     sesJqueryObject('.sesact_post_feeling_container_edit').hide();
     return;
   }
  sesJqueryObject(this).addClass('active');
  sesJqueryObject('.sesact_post_feeling_container_edit').show();
  if(sesJqueryObject('#feelingactivityidedit').val() == '')
    sesJqueryObject('.sesact_post_feelingcontent_containeredit').show();
});

sesJqueryObject(document).on('click', '#feeling_activityedit', function(e){

  if(sesJqueryObject('#feelingactivityidedit').val() == '')
    sesJqueryObject('.sesact_post_feelingcontent_containeredit').show();
});

sesJqueryObject(document).on('keyup', '#feeling_activityedit', function(e){
  if (e.which == 8) {
    $('feelingactivityiconidedit').value = '';
    sesJqueryObject('#feeling_elem_actedit').html('');
    sesJqueryObject('#feeling_activityedit').attr("placeholder", "How are you feeling?");
  }
});

function showFeelingContanieredit() {

  if($('sesact_post_feeling_container_edit').style.display == '' || $('sesact_post_feeling_container_edit').style.display == 'table') {
    $('showFeelingContanieredit').removeClass('active');
    sesJqueryObject('#sesact_post_feeling_container_edit').hide();
  } else {
    $('showFeelingContanieredit').addClass('active');
    sesJqueryObject('#feeling_activity_remove_actedit').show();
    sesJqueryObject('#sesact_post_feeling_container_edit').show();
  }
}

function feelingactivityremoveactedit() {
  sesJqueryObject('#feeling_activity_remove_actedit').hide();
  sesJqueryObject('#feelingActTypeedit').html('');
  sesJqueryObject('#feelingActTypeedit').hide();
  sesJqueryObject('.sesfeelingactivity-ul').html('');
  if(sesJqueryObject('#feelingactivityidedit').val())
    $('feelingactivityidedit').value = '';
  sesJqueryObject('#feeling_activityedit').val('');
  $('feelingactivityiconidedit').value = '';
  sesJqueryObject('#feeling_elem_actedit').html('');
  //if(sesJqueryObject('#feelingactivityid').val() == '')
  //$('showFeelingContanier').addClass('active');
  // sesJqueryObject('.sesact_post_feelingcontent_container').show();
  //socialShareSearch();
}


//Autosuggest feeling work
sesJqueryObject(document).on('click', '.sesact_feelingactivitytypeliedit', function(e) {

  $('feelingactivityiconidedit').value = sesJqueryObject(this).attr('data-rel');
  $('feelingactivity_resource_typeedit').value = sesJqueryObject(this).attr('data-type');
  
  if(!sesJqueryObject(this).attr('data-rel')) {
    $('feelingactivity_customedit').value = 1;
    $('feelingactivity_customtextedit').value = sesJqueryObject('#feeling_activityedit').val();
  }
  
  if(sesJqueryObject(this).attr('data-icon')) {
    var finalFeeling = '-- ' + '<img class="sesfeeling_feeling_icon" title="'+sesJqueryObject(this).attr('data-title')+'" src="'+sesJqueryObject(this).attr('data-icon')+'"><span>' + ' ' +  sesJqueryObject('#feelingActTypeedit').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanieredit" class="" onclick="showFeelingContanieredit()">'+sesJqueryObject(this).attr('data-title')+'</a>';
  } else {
    var finalFeeling = '-- ' + '<img class="sesfeeling_feeling_icon" title="'+sesJqueryObject(this).attr('data-title')+'" src="'+sesJqueryObject(this).find('a').find('img').attr('src')+'"><span>' + ' ' +  sesJqueryObject('#feelingActTypeedit').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanieredit" class="" onclick="showFeelingContanieredit()">'+sesJqueryObject(this).attr('data-title')+'</a>';
  }
  
  sesJqueryObject('#feeling_activityedit').val(sesJqueryObject(this).attr('data-title'));
  sesJqueryObject('#feeling_elem_actedit').show();
  sesJqueryObject('#feeling_elem_actedit').html(finalFeeling);
  sesJqueryObject('#dash_elem_act_edit').hide();
  sesJqueryObject('#sesact_post_feeling_container_edit').hide();
});
//Autosuggest feeling work


  
sesJqueryObject(document).on('click', '.sesact_feelingactivitytypeedit', function(e) {
  
  var feelingsactivity = sesJqueryObject(this);
  var feelingIdEdit = sesJqueryObject(this).attr('data-rel');
  var feelingTypeEdit = sesJqueryObject(this).attr('data-type');
  var feelingTitleEdit = sesJqueryObject(this).attr('data-title');
  sesJqueryObject('#feelingActTypeedit').show();
  sesJqueryObject('#feelingActTypeedit').html(feelingTitleEdit);
  sesJqueryObject('#feeling_activityedit').attr("placeholder", "How are you feeling?");
  
  document.getElementById('feelingactivityidedit').value = feelingIdEdit;
  
  document.getElementById('feelingactivitytypeedit').value = feelingTypeEdit;
  
  sesJqueryObject('.sesact_post_feelingcontent_containeredit').hide();
  
  sesJqueryObject('#feeling_activityedit').trigger('change').trigger('keyup').trigger('keydown');
  
//   contentAutocompletefeelingedit.setOptions({
//     'postData': {
//       'feeling_id': document.getElementById('feelingactivityidedit').value,
//       'feeling_type': document.getElementById('feelingactivitytypeedit').value,
//     }
//   });
});

//Feeling Emojis Work

var feeling_requestEmojiA;
sesJqueryObject(document).on('click','#sesadvancedactivityfeeling_emoji-edit-a',function(){
  
  sesJqueryObject('.ses_emoji_container').removeClass('from_bottom');
  
  var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;
  var leftSub = 264;
  var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
  leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
  sesJqueryObject(this).parent().find('.ses_emoji_container').css('right',0);
  //sesJqueryObject(this).parent().find('.ses_emoji_container').css('top',topPositionOfParentDiv+'px');
  //sesJqueryObject(this).parent().find('.ses_emoji_container').css('left',leftPositionOfParentDiv).css('z-index',100);
  
  sesJqueryObject(this).parent().find('.ses_emoji_container').show();
  
  sesJqueryObject('#sesadvancedactivityfeeling_emoji_edit').show();

  if(sesJqueryObject(this).hasClass('active')) {
    sesJqueryObject(this).removeClass('active');
    sesJqueryObject('#sesadvancedactivityfeeling_emoji_edit').hide();
    return false;
  }
  
  sesJqueryObject(this).addClass('active');
  sesJqueryObject('#sesadvancedactivityfeeling_emoji_edit').show();
  
  if(sesJqueryObject(this).hasClass('complete'))
    return false;
  
  if(typeof feeling_requestEmojiA != 'undefined')
    feeling_requestEmojiA.cancel();

  var that = this;
  
  var url = en4.core.baseUrl + 'sesemoji/index/feelingemoji/edit/true';
  
  feeling_requestEmojiA = new Request.HTML({
    url : url,
    data : {
      format : 'html',
    },
    evalScripts : true,
    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
      
      sesJqueryObject('#sesadvancedactivityfeeling_emoji_edit').find('.ses_feeling_emoji_container_inner').find('.ses_feeling_emoji_holder').html(responseHTML);
      sesJqueryObject('#sesadvancedactivityfeeling_emoji_edit').show();
      sesJqueryObject(that).addClass('complete');
      initSesadvAnimation();
      sesadvtooltip();
      jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
          theme:"minimal-dark"
      });
    }
  });
  feeling_requestEmojiA.send();
});


sesJqueryObject(document).on('click','.select_feeling_emoji_advedit > img',function(e){
  
  var feeling_emoji_icon = sesJqueryObject(this).parent().parent().attr('data-icon');
  var html = sesJqueryObject('#edit_activity_body').val(); 
  if(html == '<br>')
    sesJqueryObject('#edit_activity_body').val('');
  sesJqueryObject('textarea#edit_activity_body').val(sesJqueryObject('textarea#edit_activity_body').val()+' '+feeling_emoji_icon);
  
  var data = sesJqueryObject('#edit_activity_body').val();
    EditFieldValue = data;

  sesJqueryObject('textarea#edit_activity_body').trigger('focus');
//  sesJqueryObject('#sesadvancedactivityfeeling_emoji-edit-a').trigger('click');
});

//Click on Emojis and scroll up and down contanier
sesJqueryObject(document).on('click','.emojis_clicka',function(e) {
  var emojiId = sesJqueryObject(this).attr('rel');
  jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar("scrollTo",jqueryObjectOfSes('.sesbasic_custom_scroll').find('.mCSB_container').find('#sesbasic_custom_scrollul').find('#main_emiji_'+emojiId));          
});


//Feeling Work End

var requestEmojiA;
sesJqueryObject(document).on('click','#sesadvancedactivityemoji-edit-a',function(){
  
    sesJqueryObject(this).parent().find('.ses_emoji_container').removeClass('from_bottom');
    
    var parentElem = sesJqueryObject('#sessmoothbox_container');
    var parentLeft = parentElem.css('left').replace('px','');
    var parentTop = parentElem.css('top').replace('px','');

    var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
    topPositionOfParentDiv = topPositionOfParentDiv;
    var leftSub = 264;
    var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
    leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
    sesJqueryObject(this).parent().find('.ses_emoji_container').css('right',0);
    //sesJqueryObject(this).parent().find('.ses_emoji_container').css('top',topPositionOfParentDiv+'px');
    //sesJqueryObject(this).parent().find('.ses_emoji_container').css('left',leftPositionOfParentDiv).css('z-index',100);
    sesJqueryObject(this).parent().find('.ses_emoji_container').show();

    if(sesJqueryObject(this).hasClass('active')){
      sesJqueryObject(this).removeClass('active');
      sesJqueryObject('#sesadvancedactivityemoji_edit').hide();
      return false;
     }
      sesJqueryObject(this).addClass('active');
      sesJqueryObject('#sesadvancedactivityemoji_edit').show();
      if(sesJqueryObject(this).hasClass('complete'))
        return false;
       if(typeof requestEmojiA != 'undefined')
        requestEmojiA.cancel();
       var that = this;
       var url = en4.core.baseUrl + 'sesadvancedactivity/ajax/emoji/edit/true';
       requestEmojiA = new Request.HTML({
        url : url,
        data : {
          format : 'html',
        },
        evalScripts : true,
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
          sesJqueryObject('#sesadvancedactivityemoji_edit').find('.ses_emoji_container_inner').find('.ses_emoji_holder').html(responseHTML);
          sesJqueryObject(that).addClass('complete');
          sesadvtooltip();
          initSesadvAnimation();
         jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
            theme:"minimal-dark"
         });
        }
      });
     requestEmojiA.send();
});

sesJqueryObject(document).on('click','.select_emoji_advedit > img',function(e){
  var code = sesJqueryObject(this).parent().parent().attr('rel');
  var html = sesJqueryObject('#edit_activity_body').val();
  if(html == '<br>')
    sesJqueryObject('#edit_activity_body').val('');
  sesJqueryObject('#edit_activity_body').val( sesJqueryObject('#edit_activity_body').val()+' '+code);
  var data = sesJqueryObject('#edit_activity_body').val();
  EditFieldValue = data;
  sesJqueryObject('#sesadvancedactivityemoji-edit-a').trigger('click');
});




sesJqueryObject(document).on('click','.adv_privacy_optn_edit li a',function(e){
  e.preventDefault();
  if(!sesJqueryObject(this).parent().hasClass('multiple')){
    sesJqueryObject('.adv_privacy_optn_edit > li').removeClass('active');
    var text = sesJqueryObject(this).text();
    sesJqueryObject('.sesact_privacy_btn_edit').attr('title',text);;
    sesJqueryObject(this).parent().addClass('active');
    sesJqueryObject('#adv_pri_option_edit').html(text);
    sesJqueryObject('#sesadv_privacy_icon').remove();
    sesJqueryObject('<i id="sesadv_privacy_icon" class="'+sesJqueryObject(this).find('i').attr('class')+'"></i>').insertBefore('#adv_pri_option_edit');
    
    if(sesJqueryObject(this).parent().hasClass('sesadv_network_edit'))
      sesJqueryObject('#privacy_edit').val(sesJqueryObject(this).parent().attr('data-src')+'_'+sesJqueryObject(this).parent().attr('data-rel'));
    else if(sesJqueryObject(this).parent().hasClass('sesadv_list_edit'))
      sesJqueryObject('#privacy_edit').val(sesJqueryObject(this).parent().attr('data-src')+'_'+sesJqueryObject(this).parent().attr('data-rel'));
   else
    sesJqueryObject('#privacy_edit').val(sesJqueryObject(this).parent().attr('data-src'));
  }
  sesJqueryObject('.sesact_privacy_btn_edit').parent().removeClass('sesact_pulldown_active');
});
sesJqueryObject(document).on('click','.mutiselectedit',function(e){
  if(sesJqueryObject(this).attr('data-rel') == 'network-multi')
    var elem = 'sesadv_network_edit';
  else
    var elem = 'sesadv_list_edit';
  var elemens = sesJqueryObject('.'+elem);
  var html = '';
  for(i=0;i<elemens.length;i++){
    html += '<li><input class="checkbox" type="checkbox" value="'+sesJqueryObject(elemens[i]).attr('data-rel')+'">'+sesJqueryObject(elemens[i]).text()+'</li>';
  }
  en4.core.showError('<form id="'+elem+'_select" class="_privacyselectpopup"><p>'+en.core.language.translate("Please select network to display post.")+'</p><ul class="sesbasic_clearfix">'+html+'</ul><div class="_privacyselectpopup_btns sesbasic_clearfix"><button type="submit">'+en.core.language.translate("Save")+'</button><button class="close" onclick="Smoothbox.close();return false;">'+en.core.language.translate("Close")+'</button></div></form>');  
  //pre populate
  var valueElem = sesJqueryObject('#privacy_edit').val();
  if(valueElem && valueElem.indexOf('network_list_') > -1 && elem == 'sesadv_network_edit'){
    var exploidV =  valueElem.split(',');
    for(i=0;i<exploidV.length;i++){
       var id = exploidV[i].replace('network_list_','');
       sesJqueryObject('.checkbox[value="'+id+'"]').prop('checked', true);
    }
   }else if(valueElem && valueElem.indexOf('member_list_') > -1 && elem == 'sesadv_list_edit'){
    var exploidV =  valueElem.split(',');
    for(i=0;i<exploidV.length;i++){
       var id = exploidV[i].replace('member_list_','');
       sesJqueryObject('.checkbox[value="'+id+'"]').prop('checked', true);
    }
   }
});
sesJqueryObject(document).on('submit','#sesadv_list_edit_select',function(e){
  e.preventDefault();
  var isChecked = false;
   var sesadv_list_select = sesJqueryObject('#sesadv_list_edit_select').find('[type="checkbox"]');
   var valueL = '';
   for(i=0;i<sesadv_list_select.length;i++){
    if(!isChecked)
      sesJqueryObject('.adv_privacy_optn_edit > li').removeClass('active');
    if(sesJqueryObject(sesadv_list_select[i]).is(':checked')){
      isChecked = true;
      var el = sesJqueryObject(sesadv_list_select[i]).val();
      sesJqueryObject('.lists[data-rel="'+el+'"]').addClass('active');
      valueL = valueL+'member_list_'+el+',';
    }
   }
   if(isChecked){
     sesJqueryObject('#privacy_edit').val(valueL);
     sesJqueryObject('#adv_pri_option_edit').html(en.core.language.translate('Multiple Lists'));
     sesJqueryObject('.sesact_privacy_btn_edit').attr('title',en.core.language.translate('Multiple Lists'));;
    sesJqueryObject(this).find('.close').trigger('click');
   }
   sesJqueryObject('#sesadv_privacy_icon_edit').removeAttr('class').addClass('sesact_list');
});
sesJqueryObject(document).on('submit','#sesadv_network_edit_select',function(e){
  e.preventDefault();
  var isChecked = false;
   var sesadv_network_select = sesJqueryObject('#sesadv_network_edit_select').find('[type="checkbox"]');
   var valueL = '';
   for(i=0;i<sesadv_network_select.length;i++){
    if(!isChecked)
      sesJqueryObject('.adv_privacy_optn_edit > li').removeClass('active');
    if(sesJqueryObject(sesadv_network_select[i]).is(':checked')){
      isChecked = true;
      var el = sesJqueryObject(sesadv_network_select[i]).val();
      sesJqueryObject('.network[data-rel="'+el+'"]').addClass('active');
      valueL = valueL+'network_list_'+el+',';
    }
   }
   if(isChecked){
     sesJqueryObject('#privacy_edit').val(valueL);
     sesJqueryObject('#adv_pri_option_edit').html(en.core.language.translate('Multiple Network'));
     sesJqueryObject('.sesact_privacy_btn_edit').attr('title',en.core.language.translate('Multiple Network'));;
    sesJqueryObject(this).find('.close').trigger('click');
   }
   sesJqueryObject('#sesadv_privacy_icon_edit').removeAttr('class').addClass('sesact_network');
});
 
function tagLocationWorkEdit(){
    if(!sesJqueryObject('#tag_location_edit').val())
      return;
     sesJqueryObject('#locValuesEdit-element').html('<span class="tag">'+sesJqueryObject('#tag_location_edit').val()+' <a href="javascript:void(0);" class="loc_remove_act_edit">x</a></span>');
      sesJqueryObject('#dash_elem_act_edit').show();
      sesJqueryObject('#location_elem_act_edit').show();
      sesJqueryObject('#location_elem_act_edit').html('at <a href="javascript:;" class="seloc_clk_edit">'+sesJqueryObject('#tag_location_edit').val()+'</a>');
      sesJqueryObject('#tag_location_edit').hide();  
  }
  
    
  sesJqueryObject(document).on('click','.loc_remove_act_edit',function(e){
    sesJqueryObject('#activitylngEdit').val('');
    sesJqueryObject('#activitylatEdit').val('');
    sesJqueryObject('#tag_location_edit').val('');
    sesJqueryObject('#locValuesEdit-element').html('');
    sesJqueryObject('#tag_location_edit').show();
    sesJqueryObject('#location_elem_act_edit').hide();
    if(!sesJqueryObject('#toValuesEdit-element').children().length)
       sesJqueryObject('#dash_elem_act_edit').hide();
  })    
// Populate data
  var maxRecipientsEdit = 50;
  
 function getMentionDataEdit(that,dataBody){
    var data = sesJqueryObject('#edit_activity_body').val();
    var data_status = sesJqueryObject(that).attr('data-status');

    if(sesJqueryObject('#buysell-title-edit').length) {
      if(!sesJqueryObject('#buysell-title-edit').val())
        return false;
      else if(!sesJqueryObject('#buysell-price-edit').val())
        return false;
    } 
    //Feeling Work
    else if(!data && data_status == 1 && !sesJqueryObject('#tag_location_edit').val() && !sesJqueryObject('#feeling_activityedit').val())
      return false;
    
    
    data = sesJqueryObject(that).get(0).toQueryString()+'&bodyText='+dataBody;
    var url  = en4.core.baseUrl + 'sesadvancedactivity/index/edit-feed-post/userphotoalign/'+userphotoalign;
    sesJqueryObject(that).find('#compose-submit').attr('disabled',true);
    if(url.indexOf('&') <= 0)
      url = url+'?';
    url = url+'is_ajax=true';
    var that = that;
    sesJqueryObject(that).find('#compose-submit').html(savingtextActivityPost);
    //sesJqueryObject('#dots-animation-posting').show();
    //dotsAnimationWhenPostingInterval = setInterval (function() { dotsAnimationWhenPostingFn(sharingPostText)}, 600);
    sesadvancedactivityfeedactive2  = new Request.HTML({
        url : url,
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript){
          try{
            var parseJson = sesJqueryObject.parseJSON(responseHTML);
            if(parseJson.status){
              sesJqueryObject('#activity-item-'+parseJson.last_id).replaceWith(parseJson.feed);
              
              sesJqueryObject('#activity-item-'+parseJson.last_id).fadeOut("slow", function(){
                 sesJqueryObject('#activity-item-'+parseJson.last_id).replaceWith(parseJson.feed);
                 sesJqueryObject('#activity-item-'+parseJson.last_id).fadeIn("slow");
                 sesadvtooltip();
                 initSesadvAnimation();
                 
              });
              
              sessmoothboxclose();           
            }else{
               en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
            }
          }catch(e){
            
          }
          sesJqueryObject(that).find('#compose-submit').html(savingtextActivityPostOriginal);
          sesJqueryObject(that).find('#compose-submit').removeAttr('disabled');
          
        },
        onError: function(){
          en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
        },
      });
    sesadvancedactivityfeedactive2.send(data);
  }
  //submit form
  sesJqueryObject(document).on('submit','.edit-activity-form',function(e){
    e.preventDefault(); 
    var that = this;
    sesJqueryObject('textarea#edit_activity_body').mentionsInput('val', function(data) {
       getMentionDataEdit(that,data);
    });
  });
  sesJqueryObject(document).on('click','.composer_targetpost_edit_toggle',function(e){
     openTargetPostPopupEdit(); 
  });
  sesJqueryObject(document).on('focus','#edit_activity_body',function(){ 
if(!sesJqueryObject(this).attr('id'))
  sesJqueryObject(this).attr('id',new Date().getTime());
  
  isonCommentBox = true;
  var data = sesJqueryObject(this).val();
  if(!sesJqueryObject(this).val() || isOnEditField){
    if(!sesJqueryObject(this).val() )
      EditFieldValue = '';
    sesJqueryObject(this).mentionsInput({
        onDataRequest:function (mode, query, callback) {
         sesJqueryObject.getJSON('sesadvancedactivity/ajax/friends/query/'+query, function(responseData) {
          responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
          callback.call(this, responseData);
        });
      },
      //defaultValue: EditFieldValue,
      onCaret: true
    });
  }
  
  if(data){
     getDataMentionEdit(this,data);
  }
  
  if(!sesJqueryObject(this).parent().hasClass('typehead')){
    sesJqueryObject(this).hashtags();
    sesJqueryObject(this).focus();
  }
  autosize(sesJqueryObject(this));
});
sesJqueryObject(document).on('keyup','#edit_activity_body',function(){ 
    var data = sesJqueryObject(this).val();
     EditFieldValue = data;
});