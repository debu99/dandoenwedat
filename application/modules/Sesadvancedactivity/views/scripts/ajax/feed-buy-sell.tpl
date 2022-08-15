<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: feed-buy-sell.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesact_sellitem_popup sesbasic_clearfix sesbasic_bxs">
	<div class="sesact_sellitem_popup_header">
  	<?php echo $this->translate("Item Details");?>
  </div>
  <div class="sesact_sellitem_popup_content sesbasic_clearfix">
  	<div class="sesact_sellitem_popup_right sesbasic_custom_scroll">
    	<div class="sesact_sellitem_popup_right_inner sesbasic_clearfix">
        <div class="sesact_sellitem_popup_right_cont sesbasic_clearfix">
          <div class="sesact_sellitem_popup_owner sesbasic_clearfix">
            <div class="sesact_sellitem_popup_owner_photo">
              <?php 
              $action = $this->action;
              $owner = Engine_Api::_()->getItem('user',$this->action->subject_id);
              echo $this->htmlLink($owner->getHref(), $this->itemPhoto($owner, 'thumb.icon', $owner->getTitle()), array()) ?>
            </div>
            <div class="sesact_sellitem_popup_owner_info">
              <div class="sesact_sellitem_popup_owner_name">
                <a href="<?php echo $owner->getHref(); ?>"><?php echo $owner->getTitle(); ?></a>
              </div>
              <div class="sesact_sellitem_popup_time sesbasic_text_light">
                	<?php echo $this->timestamp($this->main_action->getTimeValue()) ?>
              </div>
            </div>
          </div>
          <div class="sesact_sellitem_popup_item_info sesbasic_clearfix">
            <div class="sesact_sellitem_popup_item_title"><?php echo $this->item->getTitle(); ?></div>
            <div class="sesact_sellitem_popup_item_price">
              <?php echo Engine_Api::_()->sesadvancedactivity()->getCurrencySymbol().$this->item->price; ?>
            </div>
            <?php $locationBuySell = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('sesadvancedactivity_buysell',$this->item->getIdentity()) ?>
            <?php if($locationBuySell){ ?>            
              <div class="sesact_sellitem_popup_item_location sesbasic_text_light">
                <i class="fas fa-map-marker-alt"></i>
                <span>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                  <a href="<?php echo $this->url(array('resource_id' => $this->item->getIdentity(),'resource_type'=>$this->item->getType(),'action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" onClick="openSmoothBoxInUrl(this.href);return false;"><?php echo $locationBuySell->venue; ?></a>
                <?php } else { ?>
                  <?php echo $locationBuySell->venue; ?>
                <?php } ?>
                </span>
              </div>
            <?php } ?>
            <?php if($this->item->description){ ?>
            <div class="sesact_sellitem_popup_item_des">
             <?php echo $this->viewMoreActivity($this->item->description); ?>
            </div>
            <?php } ?>
            
            <div class="sesact_sellitem_popup_item_button">
            <?php if($this->item->buy){ ?>
              <div>
                <a class="sesbasic_link_btn" href="<?php echo $this->item->buy; ?>" target="_blank" ><i class="fa fa-shopping-cart"></i><?php echo $this->translate("Buy Now"); ?></a>
              </div>
              <?php } ?>
              <?php if($this->viewer()->getIdentity() != 0){ ?>
              <div>
            	<?php if(!$this->item->is_sold){ ?>
              <?php if($action->subject_id != $this->viewer()->getIdentity()){ ?>
                <button onClick="openSmoothBoxInUrl('sesadvancedactivity/ajax/message/action_id/<?php echo $action->getIdentity(); ?>');return false;"><i class="fa fa-comment"></i><?php echo $this->translate("Message Seller"); ?></button>
              <?php }else{ ?>
                <button class="mark_as_sold_buysell mark_as_sold_buysell_<?php echo $action->getIdentity(); ?>" data-sold="<?php echo $this->translate('Sold'); ?>" data-href="<?php echo $action->getIdentity(); ?>"><i class="fa fa-check"></i><?php echo $this->translate("Mark as Sold") ?></button>
              <?php } ?>
             <?php }else{ ?>
                <button><i class="fa fa-check"></i><?php echo $this->translate("Sold"); ?></button>
             <?php } ?>
             </div>
            <?php } ?>
            
            </div>
          </div>
        </div>
        <div class="sesact_sellitem_popup_right_comments"> 
         <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?>
            <div class="sesact_feed sesbasic_bxs sesbasic_clearfix">
            	<ul class="feed"><?php echo $this->activity($action, array('noList' => true,'isOnThisDayPage'=>false), 'update',false);?></ul>
          	</div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="sesact_sellitem_popup_photos">
    	<div class="sesact_sellitem_popup_photos_strip">
				<div class="sesbasic_clearfix">
        <?php foreach( $action->getAttachments() as $attachment){ ?>
        	<a href="javascript:;" class="buysell_img_a"><img <?php  if($attachment->item->getIdentity() == $this->photo_id){ ?> class="selected" <?php } ?>src="<?php echo $attachment->item->getPhotoUrl('thumb.normalmain');  ?>" alt=""></a>
          <?php } ?>
      	</div>
      </div>
      <div class="sesact_sellitem_popup_photo_container">
      	<div>
        	<div>
          	<img class="selected_image_buysell" src="<?php echo Engine_Api::_()->getItem('album_photo',$this->photo_id)->getPhotoUrl(); ?>" />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
