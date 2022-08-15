<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="sesevent_sponsorship_listing sesbasic_bxs sesbasic_clearfix sesbasic_sidebar_block">
  <ul class="sesbasic_clearfix">
	 <?php foreach($this->sponsorship as $item): ?>
    	<li class="sesbm sesbasic_clearfix sesbm">
      	<div class="sesevent_sponsorship_listing_photo">
        	<a href="<?php echo $item->getHref() ?>"><img src="<?php echo $item->getPhotoUrl(); ?>" /></a>
       	</div>
        <div class="ticket_info sesbasic_clearfix clear">
        	<div class="sponsorship_price">
            <?php if($item->price <= 0){ ?>
                <?php echo $this->translate("FREE"); ?>
            <?php }else{ ?>
              <?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?>
            <?php } ?>
          </div>
          <div class="sponsorship_name"><a href="<?php echo $item->getHref() ?>"><?php echo $item->title; ?></a></div>
          <p class="sponsorship_des clear"><?php echo $this->viewMore($item->description); ?></p>
      	</div>
       <div class="sesbasic_clearfix sesevent_tickets_booking_btn">
          <a class="sesbasic_link_btn" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'id'=>$item->getIdentity(),'action'=>'details'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate("Buy Now"); ?></a>
        </div>
      </li>    
   <?php endforeach; ?>
  </ul>
</div>
