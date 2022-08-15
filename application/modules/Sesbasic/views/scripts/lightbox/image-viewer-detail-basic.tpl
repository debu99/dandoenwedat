<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesalbum
 * @package    Sesalbum
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: image-viewer-detail-basic.tpl 2015-06-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">
	function initImage() {
				sesJqueryObject('#gallery-img').apImageZoom({
					  cssWrapperClass: 'custom-wrapper-class'
					, autoCenter: false
					, loadingAnimation: 'throbber'
					, minZoom: 'contain'
					, maxZoom: false
					, maxZoom: 1.0
					, hammerPluginEnabled: false
					, hardwareAcceleration: false
				});
			};
    en4.core.runonce.add(function() {
    var descEls = $$('.albums_viewmedia_info_caption');
    if( descEls.length > 0 ) {
      descEls[0].enableLinks();
    }
  });
</script>
<?php $baseUrl = $this->layout()->staticBaseUrl; ?>

<div class="ses_media_lightbox_left">
  <div class="ses_media_lightbox_item_wrapper">
    <div class="ses_media_lightbox_item">
      <div id="mainImageContainer">
        <div id="media_photo_next_ses" style="display:inline;">
        <?php 
         $className= '';
         $cssDisplay = 'block';
         if($this->locked){
         			$imageUrl = 'application/modules/Sesalbum/externals/images/locked-album.jpg';
              $className = 'ses-blocked-album';
              $cssDisplay = 'none';
            }
        ?>
        <?php if(isset($this->imagePrivateURL)){
                $imageUrl = $this->imagePrivateURL;
                $className = 'ses-private-image';
              }else if(empty($imageUrl)){
              	$imageUrl = $this->photo->getPhotoUrl('','',$this->stringD);
             	}
          ?>
          <?php echo $this->htmlImage($imageUrl, $this->photo->getTitle(), array(
                'id' => 'gallery-img',
                'class' =>$className,
                'display'=>$cssDisplay,
              )); ?>
        </div>
      </div>
    </div>
  </div>
  <?php if(isset($this->imagePrivateURL)) {
    $imageUrl = $this->imagePrivateURL;
  } else
    $imageUrl =	$this->photo->getPhotoUrl(); 
  ?>
  <span id="image-src-sesalbum-lightbox-hidden" style="display:none;"><?php echo $imageUrl; ?></span>
  <span id="image-main-sesalbum-lightbox-hidden" style="display:none;"><?php echo $this->photo->getPhotoUrl('','','string'); ?></span>
  <div class="ses_media_lightbox_nav_btns">
    <?php 
        $previousURL = $this->previousPhoto;
        if($previousURL != ''){
        	$previousURL = $previousURL->getHref();
        	if(isset($this->imagePrivateURL))
        	 $previousImageURL = $this->imagePrivateURL;
          else
           $previousImageURL = $this->previousPhoto->getPhotoUrl();
      ?>
      <a href="<?php echo $this->previousPhoto->getHref(); ?>" style="display:block" title="<?php echo $this->translate('Previous'); ?>" onclick="openLightBoxForSesPlugins('<?php echo $previousURL; ?>','<?php echo $previousImageURL; ?>');return false;" class="ses_media_lightbox_nav_btn_prev" id="nav-btn-prev"></a>
    <?php }
        $nextURL = $this->nextPhoto;
        if($nextURL != ''){	
        	$nextURL = $nextURL->getHref();
        	if(isset($this->imagePrivateURL))
        	 $nextImageURL = $this->imagePrivateURL;
          else
           $nextImageURL = $this->nextPhoto->getPhotoUrl();
       ?>
       <a href="<?php echo $this->nextPhoto->getHref(); ?>" style="display:block" title="<?php echo $this->translate('Next'); ?>" onclick="openLightBoxForSesPlugins('<?php echo $nextURL; ?>','<?php echo $nextImageURL ; ?>');return false;" class="ses_media_lightbox_nav_btn_next" id="nav-btn next" ></a>
    <?php } ?>
  </div>
  <div class="ses_media_lightbox_options">
    <div class="ses_media_lightbox_options_owner">
    	<?php $albumUserDetails = Engine_Api::_()->user()->getUser($this->photo->user_id); ?>  
      <?php echo $this->htmlLink($albumUserDetails->getHref(), $this->itemPhoto($albumUserDetails, 'thumb.icon'), array('class' => 'userthumb')); ?>
      <?php echo $this->htmlLink($albumUserDetails->getHref(), $albumUserDetails->getTitle()); ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;
    </div>
    <div class="ses_media_lightbox_options_name">
      <?php echo $this->translate('In %1$s', $this->htmlLink($this->album, $this->string()->truncate($this->album->getTitle(),Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.title.truncate',35)))); ?>
    </div>
    <?php if(0 && $this->canEdit && Engine_Api::_()->user()->getViewer()->getIdentity() != 0): ?>
      <div class="ses_media_lightbox_rotate_option">
        <a class="sesalbum_icon_photos_rotate_ccw" id="ses-rotate-90" href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','90')">&nbsp;</a>
        <a class="sesalbum_icon_photos_rotate_cw" id="ses-rotate-270" href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','270')">&nbsp;</a>
        <a class="sesalbum_icon_photos_flip_horizontal" id="ses-rotate-horizontal"  href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','horizontal')">&nbsp;</a>
        <a class="sesalbum_icon_photos_flip_vertical" id="ses-rotate-vertical"  href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','vertical')">&nbsp;</a>
      </div>
    <?php endif ?>
    <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0){ ?>
      <div class="ses_media_lightbox_options_btns">
        <?php if($this->canComment) { ?>
          <?php $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($this->photo->getIdentity(), $this->photo->getType()); ?>
          <a href="javascript:void(0);" id="sesbasicLightboxLikeUnlikeButton" data-src="albumLike" class="sesbasic_icon_btn nocount sesbasic_icon_like_btn sesalbum_othermodule_like_button<?php echo $LikeStatus ? ' button_active' : '' ;  ?>"><i class="fa fa-thumbs-up"></i><span id="like_unlike_count"></span></a>
        <?php } ?>
        <?php if(isset($this->photo->favourite_count)){ ?>
          <?php if($this->photo->getType() == 'sespage_photo') { ?>
            <?php $albumFavStatus = Engine_Api::_()->getDbtable('favourites', 'sespage')->isFavourite(array('resource_type'=>$this->photo->getType(),'resource_id'=>$this->photo->photo_id)); ?>
            <a href="javascript:;" id="sesJqueryObject_favourite" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn sesbasic_favourite_sesbasic_photo<?php echo ($albumFavStatus)  ? ' button_active' : '' ?>"  data-url="<?php echo $this->photo->getIdentity(); ?>" data-type="<?php echo $this->photo->getType(); ?>"><i class="fa fa-heart"></i><span><?php echo $this->photo->favourite_count; ?></span></a>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } ?>
    <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum') && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.share',1) == 1) { ?>
      <div class="ses_media_lightbox_options_btn ses_media_lightbox_share_btn">
        <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array("action" => "share", "type" => $this->photo->getType(), "photo_id" => $this->photo->getIdentity(),"format" => "smoothbox"), 'sesalbum_general', true); ?>')"><?php echo $this->translate('Share'); ?></a>
      </div>
    <?php } ?>
    <div class="ses_media_lightbox_options_btn ses_media_lightbox_more_btn">
      <div class="ses_media_lightbox_options_box">
        <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && $this->canDelete){ ?>
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.delete',1) == 1){ ?>
            <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('action' => 'delete', 'format' => 'smoothbox')); ?>')"><?php echo $this->translate('Delete'); ?></a>
          <?php } ?>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.report',1) == 1 && Engine_Api::_()->user()->getViewer()->getIdentity() != 0){ ?>
          <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->photo->getGuid(), 'format' => 'smoothbox'),'default',true); ?>')"><?php echo $this->translate('Report'); ?></a>
        <?php } ?>
        <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.profilepic',1) == 1){ ?>
          <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('route' => 'user_extended', 'controller' => 'edit', 'action' => 'external-photo', 'photo' => $this->photo->getGuid(), 'format' => 'smoothbox'),'user_extended',true); ?>')"><?php echo $this->translate('Make Profile Photo'); ?></a>
        <?php }  ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.download',1) == 1  && isset($this->canDownload) && $this->canDownload == 1) { ?>
          <a class="ses-album-photo-download" href="<?php echo $this->url(array('module' =>'sesbasic','controller' => 'lightbox', 'action' => 'download'), 'default', true).'?filePath='.urlencode($this->photo->getPhotoUrl()) . '&file_id=' . $this->photo->file_id  ;?>"><?php echo $this->translate('Download'); ?></a>
        <?php } ?>
        <a href="javascript:;" onclick="slideShow()"><?php echo $this->translate('Slideshow'); ?></a>
      </div>
      <a href="javascript:void(0);"><?php echo $this->translate('Option'); ?></a>
    </div>
  </div>
  <div class="ses_media_lightbox_fullscreen_btn">
    <a id="fsbutton" onclick="toogle()" href="javascript:;" title="<?php echo $this->translate('Enter Fullscreen'); ?>"><i class="fa fa-expand"></i></a>
  </div>
</div>
<div class="ses_media_lightbox_information">
<div id="heightOfImageViewerContent">
  <div id="flexcroll" >
    <div class="ses_media_lightbox_media_info" id="ses_media_lightbox_media_info">
      <div class="ses_media_lightbox_information_top sesbasic_clearfix">
        <?php $albumUserDetails = Engine_Api::_()->user()->getUser($this->photo->user_id); ?>
        <div class="ses_media_lightbox_author_photo">  
          <?php echo $this->htmlLink($albumUserDetails->getHref(), $this->itemPhoto($albumUserDetails, 'thumb.icon')); ?>
        </div>
        <div class="ses_media_lightbox_author_info">
          <span class="ses_media_lightbox_author_name">
            <?php echo $this->htmlLink($albumUserDetails->getHref(), $albumUserDetails->getTitle()); ?>
          </span>
          <span class="ses_media_lightbox_posted_date sesbasic_text_light">
            <?php echo date('F j',strtotime($this->photo->creation_date)); ?>
          </span>
        </div>
      </div>
      <div class="ses_media_lightbox_item_title" id="ses_title_get"> <?php echo $this->photo->getTitle(); ?></div>
      <div class="ses_media_lightbox_item_description" id="ses_title_description"><?php echo nl2br($this->photo->getDescription()) ?></div>

      <?php if($this->viewer()->getIdentity() == $this->photo->user_id) { ?>
        <div class="ses_media_lightbox_item_edit_link">
          <a id="editDetailsLink" href="javascript:void(0)" class="sesbasic_button">
            <i class="fa fa-edit sesbasic_text_light"></i>  
            <?php echo $this->translate('Edit Details'); ?>
          </a>
        </div>
      <?php } ?>
    </div>
  <?php if($this->canEdit){ ?>
    <div class="ses_media_lightbox_edit_form" id="editDetailsForm" style="display:none;">
      <form id="changePhotoDetails">
        <input  name="title" id="titleSes" type="text" placeholder="<?php echo $this->translate('Title'); ?>" />
        <textarea id="descriptionSes" name="description" value="" placeholder="<?php echo $this->translate('Description'); ?>"></textarea>
        <input type="hidden" id="photo_id_ses" name="photo_id" value="<?php echo $this->photo->photo_id; ?>" />
        <input type="hidden" id="photo_type_ses" name="photo_type" value="<?php echo $this->photo->getType(); ?>" />
        <input type="hidden" id="album_id_ses" name="album_id" value="<?php echo $this->photo->album_id; ?>" />
        <button id="saveDetailsSes"><?php echo $this->translate('Save Changes'); ?></button>
        <button id="cancelDetailsSes"><?php echo $this->translate('Cancel'); ?></button>
      </form>
    </div>
  <?php } ?>
  <?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
  <?php if($settings->getSetting('sesalbum.enablesessocialshare', 0)) { ?>
    <div class="ses_media_lightbox_share_btns seslightbox_share_buttons sesbasic_clearfix">
      <?php echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $this->photo, 'socialshare_enable_plusicon' => $settings->getSetting('sesalbum.enableplusicon', 1), 'socialshare_icon_limit' => $settings->getSetting('sesalbum.iconlimit', 3))); ?>
    </div>
  <?php } ?>

    <div class="ses_media_lightbox_comments clear">
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
      $_SESSION['sesfromLightbox'] = "lightboxWidget";
      ?>
      <?php echo $this->action("list", "comment", "sesadvancedcomment", array("type" => $this->photo->getType(), "id" => $this->photo->getIdentity())); 
      } else {
      echo $this->action("list", "comment", "core", array("type" => $this->photo->getType(), "id" => $this->photo->getIdentity())); 
      }
      ?> 
    </div>
  </div>
  </div>
</div>
<a href="javascript:;" class="cross ses_media_lightbox_close_btn exit_lightbox"><i class="fas fa-times sesbasic_text_light"></i></a>
<a href="javascript:;" class="ses_media_lightbox_close_btn exit_fullscreen" title="<?php echo $this->translate('Exit Fullscreen'); ?>" onclick="toogle()"><i class="fas fa-times sesbasic_text_light"></i></a>
<script type="application/javascript">
function sespromptPasswordCheck(){
	var password = prompt("Enter the password for album '<?php echo $this->album->getTitle(); ?>'");
	if(typeof password != 'object' && password.toLowerCase() == '<?php echo strtolower($this->password); ?>'){
			sesJqueryObject('.ses_media_lightbox_information').show();
			sesJqueryObject('#gallery-img').attr('src',sesJqueryObject('#image-main-sesalbum-lightbox-hidden').html());
			setCookieSesalbum('<?php echo $this->album->album_id; ?>');
	}else{
		sesJqueryObject('.ses_media_lightbox_options_btns').hide();
		sesJqueryObject('.ses_media_lightbox_tag_btn').hide();
		sesJqueryObject('.ses_media_lightbox_share_btn').hide();
		sesJqueryObject('.ses_media_lightbox_more_btn').hide();
	}
 }
</script>