<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
<?php if(empty($this->isajax)){ ?>
<div class="sesact_msg_pupup sesbasic_bxs">
<?php echo $this->form->render($this); ?>
</div>
<div id="attachment_content" style="display:none;">
<?php } ?>

<div class="sessct_sell_item_attachment sesbasic_bxs sesbasic_clearfix">
  <?php $attachment = $this->action->getAttachments(); ?>
  <?php if(count($attachment)){  
        $firstAttachment = $attachment[0]->item;
  ?>
    <div class="sessct_sell_item_attachment_img floatL">
      <a href="<?php echo $this->item->getHref(); ?>"><img src="<?php echo $firstAttachment->getPhotoUrl(); ?>" class="floatL" /></a>
    </div>
  <?php } ?>
  <div class="sessct_sell_item_attachment_cont">
    <div class="sessct_sell_item_attachment_title"><a href="<?php echo $this->item->getHref(); ?>"><?php echo $this->item->getTitle(); ?></a></div>
    <div class="sessct_sell_item_attachment_price"><?php echo Engine_Api::_()->sesadvancedactivity()->getCurrencySymbol().$this->item->price; ?></div>
    <?php $location = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('sesadvancedactivity_buysell',$this->item->getIdentity()); ?>
    <?php if($location){?>
    	<div class="sessct_sell_item_attachment_location sesbasic_text_light">
      	<i class="fas fa-map-marker-alt floatL"></i>
      	<span><?php echo $location->venue; ?></span>
      </div>
    <?php } ?>
  </div>
</div>

<?php if(empty($this->isajax)){ ?>
</div>
<script type="application/javascript">
sesJqueryObject('#attachment_content_div-wrapper').html(sesJqueryObject('#attachment_content').html());
</script>
<?php } ?>
