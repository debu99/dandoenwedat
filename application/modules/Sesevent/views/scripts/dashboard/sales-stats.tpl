<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: sales-stats.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ 
  echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
 	 'event' => $this->event,
  ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
	echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
  <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
    <h3><?php echo $this->translate("Sales Stats"); ?></h3>
  </div>
  <div class="sesevent_sale_stats_container sesbasic_bxs sesbasic_clearfix">
  	<div class="sesevent_sale_stats">
    	<span><?php echo $this->translate("Today"); ?></span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->todaySale,$defaultCurrency); ?></span>
    </div>
  	<div class="sesevent_sale_stats">
    	<span><?php echo $this->translate("This Week"); ?></span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->weekSale,$defaultCurrency); ?></span>
    </div>
  	<div class="sesevent_sale_stats">
    	<span><?php echo $this->translate("This Month"); ?></span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->monthSale,$defaultCurrency); ?></span>
    </div>
  </div>
  
  <div class="sesevent_dashboard_ticket_statics sesbasic_bxs sesbasic_clearfix">
   <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
      <h3><?php echo $this->translate("Ticket Statistics"); ?></h3>
    </div>
    <div class="sesevent_sale_stats_container sesbasic_bxs sesbasic_clearfix">
      <div class="sesevent_sale_stats"><span><?php echo $this->translate("Total Order"); ?></span><span><?php echo $this->eventStatsSale['totalOrder'] ?></span></div>
      <div class="sesevent_sale_stats"><span><?php echo $this->translate("Total Commission Amount"); ?></span><span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->eventStatsSale['commission_amount'],$defaultCurrency) ?> </span></div>
      <div class="sesevent_sale_stats"><span><?php echo $this->translate("Total Tax Amount"); ?></span><span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->eventStatsSale['totalTaxAmount'],$defaultCurrency); ?></span></div>
      <div class="sesevent_sale_stats"><span><?php echo $this->translate("Total Tickets"); ?></span><span><?php echo $this->eventStatsSale['total_tickets']; ?></span></div>
    </div>
  </div>
<?php if(!$this->is_ajax){ ?>
</div>
</div>
</div>
</div>
<?php  } ?>
<?php if($this->is_ajax) die; ?>