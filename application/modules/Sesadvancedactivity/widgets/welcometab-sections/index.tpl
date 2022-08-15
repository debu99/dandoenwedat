<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if($this->displaysections == 4): ?>
  <div class="sesact_welcome_heading sesbd">
    <?php $sitetitle = str_replace(array("[site_title]", "[user_name]"),array($this->sitetitle, ucwords($this->viewer()->getTitle())),$this->tabsettings);
    echo $this->translate($sitetitle); ?>
  </div>
<?php endif; ?>
<?php 
$photocanphotoshow = 'false';
if($this->canphotoshow == 1) {
  $photocanphotoshow = 'true';
} elseif($this->canphotoshow == 2 && empty($this->viewer->photo_id)) {
  $photocanphotoshow = 'true';
}
?>
<?php if($this->profilephotoupload && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum') && $this->displaysections == 1 && $photocanphotoshow == 'true'): ?>
	<div class="sesact_welcome_section sesact_welcome_section_photo">
  	<div class="sesact_welcome_section_head">
    	<i class="fa fa-image"></i>
      <span><?php echo $this->translate("Profile Photo Upload"); ?></span>
    </div>
    <div class="sesact_welcome_section_cont">
      <?php 
      if(isset($this->canEdit)){
      // First, include the Webcam.js JavaScript Library 
        $base_url = $this->layout()->staticBaseUrl;
        $this->headScript()->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/webcam.js'); 
        }
      ?>
      <div class="sesact_welcome_photo_update sesbasic_clearfix">
        <input type="file" name="sesalbum_profile_upload_direct" onchange="readImageUrlAdd(this)" id="sesalbum_profile_upload_direct" style="display:none;"  />
        <div id='profile_photo' class="sesact_welcome_photo_left sesbasic_bxs">
          <div class="sesact_welcome_photo_loader" id="sesalbum-profile-upload-loading" style="display:none;"></div>
        <?php if($this->widgetPlaced == 'home'){ ?>
          <a href="<?php echo Engine_Api::_()->user()->getViewer()->getHref(); ?>">
          <?php echo $this->itemPhoto($this->viewer()); ?>
        <?php }else{ ?>
          <?php echo $this->itemPhoto($this->subject());
            }
          ?>
          <?php if($this->widgetPlaced == 'home'){ ?>
            </a>
          <?php } ?>
        <?php if(isset($this->canEdit)){ ?>
          <!--<div class="sesalbum_album_coverphoto_op" id="sesalbum_profile_change">
              <a href="javascript:;" id="profile_change_btn"><i class="fa fa-camera" id="profile_change_btn_i"></i><span id="change_profile_txt"><?php echo $this->translate("Update Profile Picture"); ?></span></a>
              <div class="sesalbum_album_coverphoto_op_box sesalbum_option_box">
                <i class="sesalbum_album_coverphoto_op_box_arrow"></i>
                  <a id="uploadWebCamPhoto" href="javascript:;"><i class="fa fa-camera"></i><?php echo $this->translate("Take Photo"); ?></a>
                  <a id="uploadProfilePhoto" href="javascript:;"><i class="fa fa-plus"></i><?php echo $this->translate("Upload Photo"); ?></a>
                  <a id="fromExistingAlbum" href="javascript:;"><i class="fa fa-image"></i><?php echo $this->translate("Choose From Existing"); ?></a>
              </div>
            </div>-->
        <?php } ?>
        </div>
        <div class="sesact_welcome_photo_right">
        	<span>
            <a id="uploadProfilePhoto" href="javascript:;" class="sesbasic_link_btn"><i class="fa fa-plus"></i><?php echo $this->translate("Upload Photo"); ?></a>
          </span>
          <span class="sesact_welcome_photo_right_sep"><span class="sesbasic_text_light">OR</span></span>
          <span class="sesact_welcome_photo_right_link">
            <a id="uploadWebCamPhoto" href="javascript:;">
              <?php echo $this->translate("Take Photo"); ?>
            </a>
          </span>
          <span class="sesbasic_text_light"><?php echo $this->translate("With your webcam")?></span>
          
        </div>
      </div>
      <?php if(isset($this->canEdit)){ ?>
      <script type="application/javascript">
      sesJqueryObject('<div class="sesalbum_photo_update_popup sesbasic_bxs" id="sesalbum_popup_cam_upload" style="display:none"><div class="sesalbum_photo_update_popup_overlay"></div><div class="sesalbum_photo_update_popup_container sesalbum_photo_update_webcam_container"><div class="sesalbum_photo_update_popup_header"><?php echo $this->translate("Click to Take Photo") ?><a class="fas fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesalbum_photo_update_popup_webcam_options"><div id="sesalbum_camera" style="background-color:#ccc;"></div><div class="centerT sesalbum_photo_update_popup_btns">   <button onclick="take_snapshot()" style="margin-right:3px;" ><?php echo $this->translate("Take Photo") ?></button><button onclick="hideProfilePhotoUpload()" ><?php echo $this->translate("Cancel") ?></button></div></div></div></div><div class="sesalbum_photo_update_popup sesbasic_bxs" id="sesalbum_popup_existing_upload" style="display:none"><div class="sesalbum_photo_update_popup_overlay"></div><div class="sesalbum_photo_update_popup_container" id="sesalbum_popup_container_existing"><div class="sesalbum_photo_update_popup_header"><?php echo $this->translate("Select a photo") ?><a class="fas fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesalbum_photo_update_popup_content"><div id="sesalbum_album_existing_data"></div><div id="sesalbum_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="<?php echo $this->translate("Loading"); ?>" style="margin-top:10px;"  /></div></div></div></div>').appendTo('body');
      var canPaginatePageNumber = 1;
      function existingPhotosGet(){
        sesJqueryObject('#sesalbum_profile_existing_img').show();
        var URL = en4.core.staticBaseUrl+'albums/index/existing-photos/';
        (new Request.HTML({
            method: 'post',
            'url': URL ,
            'data': {
              format: 'html',
              page: canPaginatePageNumber,
              is_ajax: 1
            },
            onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
              document.getElementById('sesalbum_album_existing_data').innerHTML = document.getElementById('sesalbum_album_existing_data').innerHTML + responseHTML;
              sesJqueryObject('#sesalbum_album_existing_data').slimscroll({
                height: 'auto',
                alwaysVisible :true,
                color :'#000',
                railOpacity :'0.5',
                disableFadeOut :true,					 
                });
                sesJqueryObject('#sesalbum_album_existing_data').slimScroll().bind('slimscroll', function(event, pos){
                if(canPaginateExistingPhotos == '1' && pos == 'bottom' && sesJqueryObject('#sesalbum_profile_existing_img').css('display') != 'block'){
                    sesJqueryObject('#sesalbum_profile_existing_img').css('position','absolute').css('width','100%').css('bottom','5px');
                    existingPhotosGet();
                }
                });
                sesJqueryObject('#sesalbum_profile_existing_img').hide();
          }
          })).send();	
      }
      sesJqueryObject(document).on('click','a[id^="sesalbum_profile_upload_existing_photos_"]',function(event){
        event.preventDefault();
        var id = sesJqueryObject(this).attr('id').match(/\d+/)[0];
        if(!id)
          return;
        sesJqueryObject('#sesalbum-profile-upload-loading').show();
        hideProfilePhotoUpload();
        var URL = en4.core.staticBaseUrl+'albums/index/upload-existingphoto/';
        (new Request.HTML({
            method: 'post',
            'url': URL ,
            'data': {
              format: 'html',
              id: id,
              user_id:'<?php echo $this->user_id; ?>'
            },
            onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
              text = JSON.parse(responseHTML);
              if(text.status == 'true'){
                if(text.src != '')
                sesJqueryObject('#profile_photo').find('.thumb_profile').attr('src',text.src);
            }
            sesJqueryObject('#sesalbum-profile-upload-loading').hide();
            }
          })).send();	
      });
      sesJqueryObject(document).on('click','a[id^="sesalbum_existing_album_see_more_"]',function(event){
        event.preventDefault();
        var thatObject = this;
        sesJqueryObject(thatObject).parent().hide();
        var id = sesJqueryObject(this).attr('id').match(/\d+/)[0];
        var pageNum = parseInt(sesJqueryObject(this).attr('data-src'),10);
        sesJqueryObject('#sesalbum_existing_album_see_more_loading_'+id).show();
        if(pageNum == 0){
          sesJqueryObject('#sesalbum_existing_album_see_more_page_'+id).remove();
          return;
        }
        var URL = en4.core.staticBaseUrl+'albums/index/existing-albumphotos/';
        (new Request.HTML({
            method: 'post',
            'url': URL ,
            'data': {
              format: 'html',
              page: pageNum+1,
              id: id,
            },
            onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
              document.getElementById('sesalbum_photo_content_'+id).innerHTML = document.getElementById('sesalbum_photo_content_'+id).innerHTML + responseHTML;
              var dataSrc = sesJqueryObject('#sesalbum_existing_album_see_more_page_'+id).html();
              sesJqueryObject('#sesalbum_existing_album_see_more_'+id).attr('data-src',dataSrc);
              sesJqueryObject('#sesalbum_existing_album_see_more_page_'+id).remove();
              if(dataSrc == 0)
                sesJqueryObject('#sesalbum_existing_album_see_more_'+id).parent().remove();
              else
                sesJqueryObject(thatObject).parent().show();
              sesJqueryObject('#sesalbum_existing_album_see_more_loading_'+id).hide();
          }
          })).send();	
      });
      sesJqueryObject(document).on('click','#fromExistingAlbum',function(){
        sesJqueryObject('#sesalbum_popup_existing_upload').show();
        existingPhotosGet();
      });
      sesJqueryObject(document).on('click','#uploadProfilePhoto',function(){
          document.getElementById('sesalbum_profile_upload_direct').click();
      });
      function readImageUrlAdd(input){
        var url = input.files[0].name;
        var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
        if((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF')){
          var formData = new FormData();
          formData.append('webcam', input.files[0]);
          formData.append('user_id', '<?php echo $this->user_id; ?>');
          sesJqueryObject('#sesalbum-profile-upload-loading').show();
      sesJqueryObject.ajax({
          xhr:  function() {
          var xhrobj = sesJqueryObject.ajaxSettings.xhr();
          if (xhrobj.upload) {
              xhrobj.upload.addEventListener('progress', function(event) {
                  var percent = 0;
                  var position = event.loaded || event.position;
                  var total = event.total;
                  if (event.lengthComputable) {
                      percent = Math.ceil(position / total * 100);
                  }
                  //Set progress
              }, false);
          }
          return xhrobj;
          },
          url:  en4.core.staticBaseUrl+'albums/index/edit-profilephoto/',
          type: "POST",
          contentType:false,
          processData: false,
          cache: false,
          data: formData,
          success: function(response){
            text = JSON.parse(response);
            if(text.status == 'true'){
              if(text.src != '')
              sesJqueryObject('#profile_photo').find('img').attr('src',text.src);
            }
            sesJqueryObject('#sesalbum-profile-upload-loading').hide();
          }
          });
        }
      }
      sesJqueryObject(document).on('click','#uploadWebCamPhoto',function(){
        sesJqueryObject('#sesalbum_popup_cam_upload').show();
        <!-- Configure a few settings and attach camera -->
        Webcam.set({
          width: 320,
          height: 240,
          image_format:'jpeg',
          jpeg_quality: 90
        });
        Webcam.attach('#sesalbum_camera');
      });
      <!-- Code to handle taking the snapshot and displaying it locally -->
      function take_snapshot() {
        // take snapshot and get image data
        Webcam.snap(function(data_uri) {
          Webcam.reset();
          sesJqueryObject('#sesalbum_popup_cam_upload').hide();
          sesJqueryObject('#sesalbum-profile-upload-loading').show();
          // upload results
          
          Webcam.upload( data_uri, en4.core.staticBaseUrl+'albums/index/edit-profilephoto/user_id/<?php echo $this->user_id; ?>' , function(code, text) {
              text = JSON.parse(text);
              if(text.status == 'true'){
                if(text.src != '')
                sesJqueryObject('#profile_photo').find('img').attr('src',text.src);
              }
              sesJqueryObject('#sesalbum-profile-upload-loading').hide();
            } );
        });
      }
      function hideProfilePhotoUpload(){
        if(typeof Webcam != 'undefined')
        Webcam.reset();
        canPaginatePageNumber = 1;
        sesJqueryObject('#sesalbum_popup_cam_upload').hide();
        sesJqueryObject('#sesalbum_popup_existing_upload').hide();
        if(typeof Webcam != 'undefined'){
          sesJqueryObject('.slimScrollDiv').remove();
          sesJqueryObject('.sesalbum_photo_update_popup_content').html('<div id="sesalbum_album_existing_data"></div><div id="sesalbum_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="Loading" style="margin-top:10px;"  /></div>');
        }
      }
      
      </script>
      <?php } ?>
    </div>  
  </div>
<?php endif; ?>

<?php if($this->friendrequest && $this->displaysections == 2): ?>
	<div class="sesact_welcome_section sesact_welcome_section_reuqest">
  	<div class="sesact_welcome_section_head">
    	<i class="fa fa-user-plus"></i>
      <?php if( $this->friendRequests->getTotalItemCount() > 0 ): ?>
        <span class="sesact_welcome_user_list_more">
          <a href="<?php echo $this->url(array('action' => 'index'), 'recent_activity', true) ?>">
            <?php echo $this->translate("See All Friend Request") ?> &raquo;
          </a>
        </span>
      <?php endif;?>
  		<span><?php echo $this->translate("Friend Requests"); ?></span>
  	</div>
    <div class="sesact_welcome_section_cont">
  	<script type="text/javascript">
    var welcomewidget_request_send = function(action, user_id, notification_id, event, tokenName, tokenValue) {
    
      event.stopPropagation();
      var url;
      if( action == 'confirm' ) {
        url = '<?php echo $this->url(array('module' => 'sesbasic', 'controller' => 'friends', 'action' => 'confirm'), 'default', true) ?>';
      } else if( action == 'reject' ) {
        url = '<?php echo $this->url(array('module' => 'sesbasic', 'controller' => 'friends', 'action' => 'reject'), 'default', true) ?>';
      } else if( action == 'add' ) {
        url = '<?php echo $this->url(array('module' => 'sesbasic', 'controller' => 'friends', 'action' => 'add'), 'default', true) ?>';
      } else {
        return false;
      }
      
      var data = {
        'user_id' : user_id,
        'format' : 'json',
      };
      data[tokenName] = tokenValue;
      
      (new Request.JSON({
        'url' : url,
        'data' : data,
        'onSuccess' : function(responseJSON) {
          if( !responseJSON.status ) {
            $('welcomeuser-widget-request-' + notification_id).innerHTML = responseJSON.error;
          } else {
            $('welcomeuser-widget-request-' + notification_id).innerHTML = responseJSON.message;
          }
        }
      })).send();
    }
  </script>
  	<?php if( $this->friendRequests->getTotalItemCount() > 0 ): ?>  
    	<ul class='sesact_welcome_user_list' id="notifications_main">
        <?php foreach( $this->friendRequests as $friendRequest ): ?>
          <?php $user = Engine_Api::_()->getItem('user', $friendRequest->subject_id);?>
          <?php
            $tokenName = 'token_' . $user->getGuid();
            $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret');
            $tokenValue = $this->token(null, $tokenName, $salt);
          ?>
          <li id="welcomeuser-widget-request-<?php echo $friendRequest->notification_id ?>" class="sesbasic_clearfix"  value="<?php echo $friendRequest->getIdentity();?>">
            <div class="sesact_welcome_user_list_photo">
              <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile')) ?>
            </div>
            <div class="sesact_welcome_user_list_cont">
              <p class="sesact_welcome_user_list_title">
                <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
              </p>
              <p class="sesact_welcome_user_list_btns">
              <button type="submit" onclick='welcomewidget_request_send("confirm", <?php echo $this->string()->escapeJavascript($friendRequest->getSubject()->getIdentity()) ?>, <?php echo $friendRequest->notification_id ?>, event, "<?php echo $tokenName; ?>", "<?php echo $tokenValue; ?>")'>
                <?php echo $this->translate('Add Friend');?>
              </button>
              </p>
							<a href="javascript:void(0);" class="sesact_welcome_user_list_close fas fa-times sesbasic_text_light" onclick='welcomewidget_request_send("reject", <?php echo $this->string()->escapeJavascript($friendRequest->getSubject()->getIdentity()) ?>, <?php echo $friendRequest->notification_id ?>, event, "<?php echo $tokenName; ?>", "<?php echo $tokenValue; ?>")' title="<?php echo $this->translate('ignore request');?>"></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
  	<?php else:?>
    	<div class="tip"><span><?php echo $this->translate('You have no any friend request.');?></span></div>
  	<?php endif;?>
  	</div>
  </div>
<?php endif; ?>


<?php if($this->findfriends && $this->displaysections == 3 && $this->searchnumfriend > $this->friendsCount): ?>
	<div class="sesact_welcome_section sesact_welcome_section_search">
  	<div class="sesact_welcome_section_head">
    	<i class="fa fa-search"></i>
  		<span><?php echo $this->translate("Find Friends"); ?></span>
  	</div>
    <div class="sesact_welcome_section_cont">
      <div class="sesact_welcome_search">	
        <div class="sesact_welcome_search_label">
          <?php echo $this->translate('Enter name of member.'); ?>
        </div>
        <div class="sesact_welcome_search_field">
          <form action="<?php echo $this->url(array(), 'user_extended', true); ?>">
            <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')): ?>
              <input type="text" name="search_text" id="search_text" />
            <?php else: ?>
              <input type="text" name="displayname" id="displayname" />
            <?php endif; ?>
            <button type='submit'><?php echo $this->translate("Find"); ?></button>
          </form>
        </div>
      </div>
    </div>	
  </div>
<?php endif; ?>