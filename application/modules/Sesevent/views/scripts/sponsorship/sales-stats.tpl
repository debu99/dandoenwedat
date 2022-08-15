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
    <h3>Sponsorship Sales Stats</h3>
  </div>
  <div class="sesevent_sale_stats_container sesbasic_bxs sesbasic_clearfix">
  	<div class="sesevent_sale_stats">
    	<span>Today</span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->todaySale,$defaultCurrency); ?></span>
    </div>
  	<div class="sesevent_sale_stats">
    	<span>This Week</span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->weekSale,$defaultCurrency); ?></span>
    </div>
  	<div class="sesevent_sale_stats">
    	<span>This Month</span>
      <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->monthSale,$defaultCurrency); ?></span>
    </div>
  </div>
  
  <div class="sesevent_dashboard_ticket_statics sesbasic_bxs sesbasic_clearfix">
   <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
      <h3>Sponsorship Statistics</h3>
    </div>
    <div class="sesevent_sale_stats_container sesbasic_bxs sesbasic_clearfix">
      <div class="sesevent_sale_stats"><span>Total Order</span><span><?php echo $this->sponsorshipStatsSale['totalOrder'] ?></span></div>
      <div class="sesevent_sale_stats"><span>Total Commission Amount</span><span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->sponsorshipStatsSale['commission_amount'],$defaultCurrency) ?> </span></div>
      <div class="sesevent_sale_stats"><span>Total Sponsorship Solds</span><span><?php echo !$this->sponsorshipStatsSale['totalOrder'] ? '0' : $this->sponsorshipStatsSale['totalOrder']; ?></span></div>
    </div>
  </div>
<?php if(!$this->is_ajax){ ?>
</div>
</div>
<?php  } ?>
<?php if($this->is_ajax) die; ?>