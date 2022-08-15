<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesalbum
 * @package    Sesalbum
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: last-element-data.tpl 2015-06-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="ses_ml_more_popup_container sesbasic_clearfix">
  <div class="ses_ml_more_popup_lb">
    <div class="ses_ml_more_popup_bh">
      <i class="fa fa-file-image-o"></i>
      <span><?php echo $this->translate("Popular Albums"); ?></span>
    </div>
    <div class="ses_ml_more_popup_bc sesbasic_clearfix">
      <?php foreach($this->paginator as $albumItem) { ?>
        <div class="ses_ml_more_popup_a_list sesbasic_clearfix">
          <?php $photo = Engine_Api::_()->getItem($this->resource_type, $albumItem->photo_id); ?>
          <?php if($photo) { ?>
            <a class="ses-image-viewer" href="<?php echo $photo->getHref(); ?>" onclick="openLightBoxForSesPlugins('<?php echo $photo->getHref(); ?>', '<?php echo $albumItem->getPhotoUrl(); ?>','change')">
              <span class="ses_ml_more_popup_a_list_img" style="background-image:url(<?php echo $albumItem->getPhotoUrl('thumb.normalmain'); ?>);"></span>
              <span class="ses_ml_more_popup_a_list_title">
                <?php echo $this->string()->truncate($albumItem->getTitle(), 30) ; ?>
                <span class="ses_ml_more_popup_a_list_owner"><?php echo $this->translate('by').' '.$albumItem->getOwner()->getTitle(); ?></span>
              </span>
            </a>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
  <div class="ses_ml_more_popup_rb">
    <div class="ses_ml_more_popup_bh">
      <i class="far fa-image"></i>
      <span><?php echo $this->translate("Popular Photos"); ?></span>
    </div>
    <div class="ses_ml_more_popup_bc sesbasic_clearfix">
      <?php foreach($this->photoPaginator as $photoItem){ ?>
        <?php if($photoItem) { ?>
          <a onclick="openLightBoxForSesPlugins('<?php echo $photoItem->getHref(); ?>', '<?php echo $photoItem->getPhotoUrl(); ?>','change')" href="<?php echo $photoItem->getHref(); ?>" class="ses_ml_more_popup_photo_list ses-image-viewer"><span style="background-image:url(<?php echo $photoItem->getPhotoUrl('thumb.normalmain'); ?>);"></span></a>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
</div>
