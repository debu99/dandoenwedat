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
<?php if(!$this->is_ajax){ ?>
<h3><?php echo $this->translate("My Tickets"); ?></h3>
<div class="layout_core_container_tabs">
  <div class="tabs_alt tabs_parent">
    <ul class="sesevent_my_tickets_tabs">
      <li style="display:inline-block" class="active">
      	<a href="javascript:;" data-id="current" class="switch_type"><?php echo $this->translate("Current Tickets"); ?>
        <span><?php echo $this->currentOrderCount; ?></span></a>
      </li>
      <li style="display:inline-block"><a href="javascript:;"  data-id="past" class="switch_type"><?php echo $this->translate("Past Tickets"); ?>
      	<span><?php echo $this->pastOrderCount; ?></span></a>
      </li>
    </ul>
	</div>
	<div class="sesevent_my_tickets_content sesbasic_clearfix">
<?php } ?>
<?php if($this->paginator->getTotalItemCount() > 0){ ?>
<?php foreach($this->paginator as $order){ ?>
<?php $event = Engine_Api::_()->getItem('sesevent_event', $order->event_id); ?>
	<div class="sesevent_mytickets_list sesbm sesbasic_bxs sesbasic_clearfix">
    <div class="sesevent_mytickets_list_photo">
    	<?php echo $this->htmlLink($event->getHref(), $this->itemPhoto($event, 'thumb.profile', '', array('align' => 'center'))) ?>
    </div>
    <div class="sesevent_mytickets_list_info">
    	<div class="sesevent_mytickets_list_info_title">
     		<?php echo $this->htmlLink($event->getHref(),$event->getTitle()); ?>
     	</div>   
      <?php $orderTicketDetails = Engine_Api::_()->getDbtable('orderTickets', 'sesevent')->getOrderTicketDetails(array('columns' => array('title', 'quantity'),'order_id' => $order->order_id)); ?>
      <div class="sesevent_mytickets_list_info_stat sesbasic_text_light"><?php Engine_Api::_()->sesevent()->dateFormat($event->starttime); ?></div>
      <?php foreach($orderTicketDetails as $orderTicketDetail): ?>
        <div class="sesevent_mytickets_list_info_stat sesbasic_text_light">
          <?php echo "$orderTicketDetail->title : $orderTicketDetail->quantity"?>
        </div>
      <?php endforeach; ?>
      <div class="sesevent_mytickets_list_options">
        <div>
          <a href="<?php echo $this->url(array('action' => 'view', 'order_id' => $order->order_id, 'event_id' => $event->custom_url), 'sesevent_order', true) ?>">
            <i class="fas fa-eye sesbasic_text_light"></i><?php echo $this->translate("View Order"); ?>
          </a>
        </div>
        <div>
          <a target="_blank" href="<?php echo $this->url(array('action' => 'print-ticket', 'order_id' => $order->order_id, 'event_id' => $event->custom_url,'format'=>'smoothbox'), 'sesevent_order', true) ?>">
            <i class="fas fa-print sesbasic_text_light"></i><?php echo $this->translate("Print Ticket"); ?>
          </a>
        </div>
        <div>
          <a href="<?php echo $this->url(array('action' => 'view', 'order_id' => $order->order_id, 'event_id' => $event->custom_url,'format'=>'smoothbox'), 'sesevent_order', true) ?>" target="_blank">
            <i class="fas fa-print sesbasic_text_light"></i><?php echo $this->translate("Print Invoice"); ?>
          </a>
        </div>
        <div>
            <a href="<?php echo $this->url(array('action' => 'email-ticket', 'order_id' => $order->order_id, 'event_id' => $event->custom_url), 'sesevent_order', true) ?>"><i class="fas fa-envelope sesbasic_text_light"></i><?php echo $this->translate("Email Ticket"); ?></a>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
<?php }else{ ?>
	<div class="tip"><span><?php echo $this->translate("There are no tickets to display."); ?></span></div>
<?php } ?>
<?php if(!$this->is_ajax){ ?>
</div>
</div>

<script type="application/javascript">
sesJqueryObject(document).on('click','.switch_type',function(){
	var type = sesJqueryObject(this).attr('data-id');
	if(sesJqueryObject(this).parent().hasClass('active') || !type)
		return;
	sesJqueryObject('.sesevent_my_tickets_tabs li').removeClass('active');
	sesJqueryObject(this).parent().addClass('active');
	sesJqueryObject('.sesevent_my_tickets_content').html('<div class="sesbasic_loading_container"></div>');
	 new Request.HTML({
      method: 'post',
      url : en4.core.baseUrl + "widget/index/mod/sesevent/name/my-tickets",
      data : {
        format : 'html',
				is_ajax:true,
				view_type:type,
      },
      onComplete: function(response) {
				sesJqueryObject('.sesevent_my_tickets_content').html(response);
			}
    }).send();
});
</script>
<?php } ?>