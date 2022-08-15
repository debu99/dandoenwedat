<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: reaction-add.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<div class="sesact_reaction_add_cnt ses_emoji_store sesbasic_bxs">
	<div id="sesact_reaction_gallery_cnt" class="sesbasic_clearfix">
    <div class="ses_emoji_store_header sesbasic_clearfix" style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->getFileUrl($this->storebackgroundimage); ?>)">
      <div class="ses_emoji_store_header_cont">
        <div class="ses_emoji_store_header_cont_title centerT" style="color: #<?php echo $this->stickerstextcolor; ?>;">
          <i class="fa fa-shopping-cart "></i>
          <span><?php echo $this->translate($this->storepopupTitle); ?></span>
        </div>
        <div class="ses_emoji_store_header_cont_des sesbasic_text_light centerT" style="color: #<?php echo $this->stickerstextcolor; ?>;"><?php echo $this->translate($this->storepopupDesciption); ?></div>
      </div>
    </div>
    <div class="ses_emoji_store_content sesbasic_custom_scroll">
      <div class="ses_emoji_store_content_inner sesbasec_clearfix">
        <?php foreach($this->gallery as $gallery){ ?>
          <div class="ses_emoji_store_item _emoji_cnt">
            <div>
              <a href="javascript:;" data-gallery="<?php echo $gallery->getIdentity(); ?>" class="anc_sesact_reaction sesact_reaction_preview_btn">
                <div class="ses_emoji_store_item_top sesbasec_clearfix">
                  <div class="ses_emoji_store_item_main_icon centerT floatL">
                    <img src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>">
                  </div>
                  <div class="ses_emoji_store_item_top_icons">
                    <?php 
                      $files =  Engine_Api::_()->getItemTable('sesadvancedcomment_emotionfile')->getFiles(array('fetchAll'=>true,'gallery_id'=>$gallery->getIdentity(),'limit'=>8));
                      foreach($files as $file){ ?>
                        <div class="centerT floatL">
                          <img src="<?php echo Engine_Api::_()->storage()->get($file->photo_id, '')->getPhotoUrl(); ?>" />
                        </div>
                      <?php } ?>
                  </div>
                </div>
              </a>
              <div class="ses_emoji_store_item_btm sesbasic_clearfix">
                <div class="ses_emoji_store_item_btm_btns floatR">
                  <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>" class="sesact_reaction_preview_btn"><?php echo $this->translate("Preview"); ?></button>
                  <?php if(in_array($gallery->getIdentity(),$this->useremotions)){ ?>
                    <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>" data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>" class="sesadv_reaction_remove_emoji sesadv_reaction_remove_emoji_<?php echo $gallery->getIdentity(); ?>" data-title="<?php echo $gallery->getTitle(); ?>" data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Remove'); ?></button>
                  <?php }else{ ?>
                   <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>" data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>"  class="sesadv_reaction_add_emoji sesadv_reaction_add_emoji_<?php echo $gallery->getIdentity(); ?>" data-title="<?php echo $gallery->getTitle(); ?>" data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Add'); ?></button>
                  <?php } ?>
                </div>
                <div class="ses_emoji_store_item_btm_title">
                  <?php echo $gallery->getTitle(); ?>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>	
  </div>
	<div class="sesact_reaction_gallery_preview_cnt" style="display:none;height:100%;"></div>
</div>
<script type="text/javascript">
function sessmoothboxcallback (){
		sesJqueryObject('#sessmoothbox_main').css('z-index', '101');
	}
</script>
<?php die; ?>
