<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesalbum
 * @package    Sesalbum
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: image-viewer-detail-advance.tpl 2015-06-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php $baseUrl = $this->layout()->staticBaseUrl; ?>

<?php 
$previousURL = $this->previousPhoto;
if($previousURL != '') {
  $previousURL =$previousURL->getHref();
  if(isset($this->imagePrivateURL))
  $previousImageURL = $this->imagePrivateURL;
  else
  $previousImageURL = $this->previousPhoto->getPhotoUrl();
?>
  <a class="pswp__button pswp__button--arrow--left" style="display:block" href="<?php echo $this->previousPhoto->getHref(); ?>" title="<?php echo $this->translate('Previous'); ?>" onclick="openLightBoxForSesPlugins('<?php echo $previousURL; ?>','<?php echo $previousImageURL; ?>');return false;" id="nav-btn-prev"></a>
<?php } else { ?>
  <a class="pswp__button pswp__button--arrow--left" style="display:block" href="javascript:;" title="<?php echo $this->translate('Previous'); ?>" id="first-element-btn"></a>
<?php } ?>
<?php 
$nextURL = $this->nextPhoto;
if($nextURL != '') {
  $nextURL = $nextURL->getHref();
  if(isset($this->imagePrivateURL))
  $nextImageURL = $this->imagePrivateURL;
  else
  $nextImageURL = $this->nextPhoto->getPhotoUrl();
?>
  <a class="pswp__button pswp__button--arrow--right" style="display:block" href="<?php echo $this->nextPhoto->getHref(); ?>" title="<?php echo $this->translate('Next'); ?>" onclick="openLightBoxForSesPlugins('<?php echo $nextURL; ?>','<?php echo $nextImageURL; ?>');return false;" id="nav-btn-next"></a>
<?php } else { ?>
  <a class="pswp__button pswp__button--arrow--right" style="display:block" href="javascript:;" title="<?php echo $this->translate('Next'); ?>"  id="last-element-btn" data-resource-type="<?php echo $this->photo->getType() ?>" data-resource-id="<?php echo $this->photo->getIdentity(); ?>"></a>
<?php } ?>

<div class="ses_pswp_information" id="ses_pswp_information">
  <div id="heightOfImageViewerContent">
    <div id="flexcroll" >
      <div class="ses_pswp_info" id="ses_pswp_info">
        <div class="ses_pswp_information_top sesbasic_clearfix">
          <?php if(isset($this->photo->user_id)) { ?>
            <?php $albumUserDetails = Engine_Api::_()->user()->getUser($this->photo->user_id); ?>
          <?php } else { ?>
            <?php $albumUserDetails = Engine_Api::_()->user()->getUser($this->photo->owner_id); ?>
          <?php } ?>
          <div class="ses_pswp_author_photo"> <?php echo $this->htmlLink($albumUserDetails->getHref(), $this->itemPhoto($albumUserDetails, 'thumb.icon')); ?> </div>
          <div class="ses_pswp_author_info"> <span class="ses_pswp_author_name"> <?php echo $this->htmlLink($albumUserDetails->getHref(), $albumUserDetails->getTitle()); ?> </span> <span class="ses_pswp_item_posted_date sesbasic_text_light"> <?php echo date('F j',strtotime($this->photo->creation_date)); ?> </span> </div>
        </div>
        <div class="ses_pswp_item_title" id="ses_title_get"> <?php echo $this->photo->getTitle(); ?></div>
        <div class="ses_pswp_item_description" id="ses_title_description"><?php echo nl2br($this->photo->getDescription()) ?></div>
        
        <?php if($this->canEdit) { ?>
          <div class="ses_pswp_item_edit_link"> <a id="editDetailsLink" href="javascript:void(0)" class="sesbasic_button"> <i class="fa fa-edit sesbasic_text_light"></i> <?php echo $this->translate('Edit Details'); ?> </a> </div>
        <?php } ?>
      </div>
      <?php if($this->canEdit) { ?>
        <div class="ses_pswp_item_edit_form" id="editDetailsForm" style="display:none;">
          <form id="changePhotoDetails">
            <input  name="title" id="titleSes" type="text" placeholder="<?php echo $this->translate('Title'); ?>" />
            <textarea id="descriptionSes" name="description" value="" placeholder="<?php echo $this->translate('Description'); ?>"></textarea>
            <input type="hidden" id="photo_id_ses" name="photo_id" value="<?php echo $this->photo->getIdentity(); ?>" />
            <input type="hidden" id="photo_type_ses" name="photo_type" value="<?php echo $this->photo->getType(); ?>" />
            <input type="hidden" id="album_id_ses" name="album_id" value="<?php echo $this->photo->album_id; ?>" />
            <button id="saveDetailsSes"><?php echo $this->translate('Save Changes'); ?></button>
            <button id="cancelDetailsSes"><?php echo $this->translate('Cancel'); ?></button>
          </form>
        </div>
      <?php } ?>
      <div class="ses_pswp_comments clear"> 
        <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) { ?>
          <?php $_SESSION['sesfromLightbox'] = "lightboxWidget"; ?>
          <?php echo $this->action("list", "comment", "sesadvancedcomment", array("type" => "album_photo", "id" => $this->photo->getIdentity())); ?>
        <?php } else { ?>
          <?php echo $this->action("list", "comment", "core", array("type" => $this->photo->getType(), "id" => $this->photo->getIdentity())); ?>
        <?php } ?>
     </div>
    </div>
  </div>
</div>
</div>
<div class="pswp__top-bar" style="display:none" id="imageViewerId"> 
	<a title="<?php echo $this->translate('Close (Esc)'); ?>" class="pswp__button pswp__button--close"></a> 
  <a title="<?php echo $this->translate('Toggle Fullscreen'); ?>" onclick="toogle()" href="javascript:;" class="pswp__button sesalbum_toogle_screen"></a>
  <a <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.show.information', 1)) { ?> title="<?php echo $this->translate('Close'); ?>" <?php } else { ?> title="<?php echo $this->translate('Show Info'); ?>" <?php } ?> id="pswp__button--info-show" class="pswp__button pswp__button--info pswp__button--info-show <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.show.information', 1)) { ?> active <?php } ?>"></a>
  <a title="<?php echo $this->translate('Show All Photos'); ?>" id="show-all-photo-container" class="pswp__button pswp__button--show-photos"></a>
  <a title="<?php echo $this->translate('Zoom in/out'); ?>" id="pswp__button--zoom" class="pswp__button pswp__button--zoom"></a>
  <div class="pswp__top-bar-action">
    
    <div class="pswp__top-bar-albumname"><?php echo $this->translate('In %1$s', $this->htmlLink($this->album, $this->string()->truncate($this->album->getTitle(),Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.title.truncate',35)))); ?> </div>
    <div class="pswp__top-bar-share">
      <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.share',1) == 1) { ?>
        <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('module'=>'activity', 'controller'=>'index', 'action'=>'share', 'route'=>'default', 'type'=>$this->photo->getType(), 'id'=>$this->photo->getIdentity(), 'format' => 'smoothbox'), 'default', true); ?>')"><?php echo $this->translate('Share'); ?></a>
      <?php } ?>
    </div>
    <div class="pswp__top-bar-more"> <a href="javascript:;" class="optionOpenImageViewer" id="overlay-model-class"><?php echo $this->translate('Options') ?> <i class="fa fa-angle-down" id="overlay-model-class-down"></i></a>
      <div class="pswp__top-bar-more-tooltip" style="display:none">
        <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && $this->canDelete) { ?>
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.delete',1) == 1){ ?>
            <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('action' => 'delete', 'format' => 'smoothbox')); ?>')"><?php echo $this->translate('Delete'); ?></a>
          <?php } ?>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.report',1) == 1 && Engine_Api::_()->user()->getViewer()->getIdentity() != 0){ ?>
          <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->photo->getGuid(), 'format' => 'smoothbox'),'default',true); ?>')"><?php echo $this->translate('Report'); ?></a>
        <?php } ?>
        <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.profilepic',1) == 1){ ?>
          <a href="javascript:;" class='smoothbox' onclick="openURLinSmoothBox('<?php echo $this->url(array('route' => 'user_extended', 'controller' => 'edit', 'action' => 'external-photo', 'photo' => $this->photo->getGuid(), 'format' => 'smoothbox'),'user_extended',true); ?>')"><?php echo $this->translate('Make Profile Photo'); ?></a>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.add.download',1) == 1 && isset($this->canDownload) && $this->canDownload == 1) { ?>
          <a class="ses-album-photo-download" href="<?php echo $this->url(array('module' =>'sesbasic','controller' => 'lightbox', 'action' => 'download'), 'default', true).'?filePath='.urlencode($this->photo->getPhotoUrl()) . '&file_id=' . $this->photo->file_id  ;?>"><?php echo $this->translate('Download'); ?></a>
        <?php } ?>
        <a href="javascript:;" onclick="slideShow()"><?php echo $this->translate("Slideshow"); ?></a> 
      </div>
    </div>
    <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0) { ?>
      <div class="pswp__top-bar-msg pswp__top-bar-btns">
        <!--<a class="sesbasic_icon_btn sesalbum_icon_msg_btn smoothbox" href="<?php //echo $this->url(array('module'=> 'sesalbum', 'controller' => 'index', 'action' => 'message','photo_id' => $this->photo->getIdentity(), 'format' => 'smoothbox'),'sesalbum_extended',true); ?>" onclick="openURLinSmoothBox(this.href);return false;"><i class="fa fa-envelope"></i></a>-->
        
        <?php if($this->canComment) { ?>
          <?php $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($this->photo->getIdentity(), $this->photo->getType()); ?>
          <a href="javascript:void(0);" id="sesbasicLightboxLikeUnlikeButton" data-id="<?php echo $this->photo->getIdentity(); ?>" data-src="albumLike" class="sesbasic_icon_btn nocount sesbasic_icon_like_btn sesalbum_othermodule_like_button<?php echo $LikeStatus ? ' button_active' : '' ;  ?>"><i class="fa fa-thumbs-up"></i><span id="like_unlike_count"></span></a>
        <?php } ?>
        
        <?php //if(isset($this->photo->favourite_count) && $this->canFavourite){ ?>
        <?php if(isset($this->photo->favourite_count)) { ?>
          <?php if($this->photo->getType() == 'sespage_photo') { ?>
          <?php $albumFavStatus = Engine_Api::_()->getDbtable('favourites', 'sespage')->isFavourite(array('resource_type' => $this->photo->getType(), 'resource_id' => $this->photo->getIdentity())); ?>
          <a href="javascript:;" id="sesJqueryObject_favourite" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn sesbasic_favourite_sesbasic_photo<?php echo ($albumFavStatus)  ? ' button_active' : '' ?>"  data-url="<?php echo $this->photo->getIdentity(); ?>" data-type="<?php echo $this->photo->getType(); ?>"><i class="fa fa-heart"></i><span><?php echo $this->photo->favourite_count; ?></span></a>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } ?>
    
    <?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
    <?php if($settings->getSetting('sesalbum.enablesessocialshare', 1)) { ?>
      <div class="pswp__top-bar-share-btns seslightbox_share_buttons">
        <?php echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $this->photo, 'socialshare_enable_plusicon' => $settings->getSetting('sesalbum.enableplusicon', 1), 'socialshare_icon_limit' => $settings->getSetting('sesalbum.iconlimit', 3))); ?>
      </div>
    <?php } ?>
    
    <?php if(0 && $this->canEdit && Engine_Api::_()->user()->getViewer()->getIdentity() != 0): ?>
      <div class="pswp_rotate_option">
        <a class="sesalbum_icon_photos_rotate_ccw" id="ses-rotate-90" href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','90')">&nbsp;</a>
        <a class="sesalbum_icon_photos_rotate_cw" id="ses-rotate-270" href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','270')">&nbsp;</a>
        <a class="sesalbum_icon_photos_flip_horizontal" id="ses-rotate-horizontal"  href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','horizontal')">&nbsp;</a>
        <a class="sesalbum_icon_photos_flip_vertical" id="ses-rotate-vertical"  href="javascript:void(0)" onclick="sesRotate('<?php echo $this->photo->getIdentity() ?>','vertical')">&nbsp;</a>
      </div>
    <?php endif ?>
    
  </div>
 <div id="" class="media_photo_next_ses_btn" style="display:inline;">
 
 				<?php $className= '';
                if($this->locked){
                   $imageUrl = 'application/modules/Sesalbum/externals/images/locked-album.jpg';
                   $className = 'ses-blocked-album';
                 }
          ?>
 					<?php if(isset($this->imagePrivateURL)){
          				  $imageUrl = $this->imagePrivateURL;
                    $className = 'ses-private-image';
                 }else if(empty($imageUrl)){
                 		$imageUrl = $this->photo->getPhotoUrl('','',$this->stringD);
                 	  $className = '';
                  }
          ?>
          <?php echo $this->htmlImage($imageUrl, $this->photo->getTitle(), array(
                'id' => 'gallery-img',
                'style'=>'display:none;',
                'class'=>$className
              )); ?>
        </div>
  <div id="sesalbum_check_privacy_album" class="<?php echo $className; ?>"></div>
  <div id="sesalbum_album_password" style="display:none;"><?php echo $this->password; ?></div>
  <div id="sesalbum_album_title" style="display:none;"><?php echo $this->album->getTitle(); ?></div>
   <div id="sesalbum_photo_id_data_org" data-src="<?php echo $this->photo->getPhotoUrl('','','string'); ?>" style="display:none;"></div>
   <div id="sesalbum_album_album_id" data-src="<?php echo $this->photo->album_id; ?>" style="display:none;"></div>    
   <div id="sesalbum_photo_id_data_src" data-src="<?php echo $this->photo->photo_id; ?>" style="display:none;"></div>
  <div class="pswp__preloader">
    <div class="pswp__preloader__icn">
      <div class="pswp__preloader__cut">
        <div class="pswp__preloader__donut"></div>
      </div>
    </div>
  </div>
</div>
<div id="content-from-element" style="display:none;">
<div class="ses_ml_overlay"></div>
<div class="ses_ml_more_popup sesbasic_bxs sesbasic_clearfix">
	<div class="ses_ml_more_popup_header">
  	<span><?php echo $this->translate("You've finished Photos") ?></span>
    <a href="javascript:;" class="morepopup_bkbtn"><i id="morepopup_bkbtn_btn" class="fa fa-sync"></i></a>
    <a href="javascript:;" class="morepopup_closebtn" id="morepopup_closebtn"><i id="morepopup_closebtn_btn" class="fas fa-times"></i></a>
  </div>
<div id="content_last_element_lightbox"></div>
</div>
