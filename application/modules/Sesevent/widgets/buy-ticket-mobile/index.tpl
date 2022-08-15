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
<div class="sesevent_mobile_tickets_listing sesbasic_bxs sesbasic_clearfix sesbasic_sidebar_block">
  <ul class="sesbasic_clearfix">
  	<li>
			<?php foreach($this->ticket as $item): ?>
        <?php $minQuatity = (int) $item->min_quantity == 0 ? 0 : (int) $item->min_quantity; 
          $maxQuatity = (int) $item->max_quantity == 0 ? 10 : (int) $item->max_quantity;
        ?>
        <?php 
         if($item->total > 0){
          $availableTicketSold =  Engine_Api::_()->sesevent()->purchaseTicketCount($this->event->event_id,$item->ticket_id); 
          $availableTicket = $item->total - $availableTicketSold;
         }else{
          $availableTicketSold = 0;
          $availableTicket  = 0;
          }
        ?>
        <div class="ticket_price">
        	<?php if($item->price <= 0){ ?>
          		<?php echo $this->translate("FREE"); ?>
          <?php }else{ ?>
          	<?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?>
          <?php } ?>
        </div>
     	<?php endforeach; ?> 
      <div>
        <a class="sesbasic_link_btn" href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_ticket', true); ?>"><?php echo $this->translate("Book Now"); ?></a>
      </div>
    </li>       
  </ul>
</div>
