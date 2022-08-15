<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: preview-reaction.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<div class="ses_emoji_store_preview sesbasic_clearfix">
	<div class="ses_emoji_store_preview_back_link">
  	<a href="javascript:;" class="sesact_back_store">
    	<i class="fa fa-chevron-left"></i>
    	<span><?php echo $this->translate("Sticker Store"); ?></span>
    </a>
  </div>
  <?php $gallery = $this->gallery; ?>
  <div class="sesbasic_custom_scroll sesbasic_clearfix ses_emoji_store_preview_cont">
  	<div class="ses_emoji_store_preview_info sesbasic_clearfix">
      <div class="floatL ses_emoji_store_preview_info_img">
        <img src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>">
      </div>
      <div class="ses_emoji_store_preview_info_cont">
        <div class="ses_emoji_store_preview_title">
          <?php echo $gallery->getTitle(); ?>
        </div>
        <div class="ses_emoji_store_preview_des">
        	 <?php echo $gallery->getDescription(); ?>
        </div>
        <div class="ses_emoji_store_preview_btn">
          <?php if($this->useremotions){ ?>
            <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>" data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>" class="sesadv_reaction_remove_emoji  sesadv_reaction_remove_emoji_<?php echo $gallery->getIdentity(); ?>" data-title="<?php echo $gallery->getTitle(); ?>" data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Remove'); ?></button>
          <?php }else{ ?>
           <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>" data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>"  class="sesadv_reaction_add_emoji  sesadv_reaction_add_emoji_<?php echo $gallery->getIdentity(); ?>" data-title="<?php echo $gallery->getTitle(); ?>" data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Add'); ?></button>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="ses_emoji_store_preview_stickers sesbasic_clearfix">
      <?php 
        $files =  Engine_Api::_()->getItemTable('sesadvancedcomment_emotionfile')->getFiles(array('fetchAll'=>true,'gallery_id'=>$gallery->getIdentity()));
        foreach($files as $file){ ?>
        	<div class="ses_emoji_store_preview_stickers_icon">
          	<span style="background-image:url(<?php echo Engine_Api::_()->storage()->get($file->photo_id, '')->getPhotoUrl(); ?>);"></span>
          </div>
        <?php } ?>
    </div>
  </div>
</div>
<?php die; ?>