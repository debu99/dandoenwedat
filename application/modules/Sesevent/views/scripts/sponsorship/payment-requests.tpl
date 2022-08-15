<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: payment-requests.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
if(!$this->is_ajax){
	echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));?>
<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<div class="sesbasic_dashboard_content_header sesbasic_clearfix">	
  <h3>Sponsorship Payment Request</h3>
    <?php if($this->thresholdAmount > 0){ ?>
      <div>Threshold Amount:<?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->thresholdAmount,$defaultCurrency); ?></div>
    <?php } ?>
</div>

<?php if(!count($this->userGateway)){ ?>
	<div class="tip">
  <span>
  	<?php echo $this->translate("Payment details not submited yet <a href='%s' onclick='paymentDetail();return false;'>Click Here</a> to submit",$this->url(array('event_id' => $this->event->custom_url,'action'=>'account-details'), 'sesevent_dashboard', true)); ?>  
  </span>
</div>
<?php } ?>
<?php $orderDetails = $this->orderDetails; ?>
<div class="sesevent_sale_stats_container sesbasic_bxs sesbasic_clearfix">
	<div class="sesevent_sale_stats">
  	<span>Total Orders</span>
    <span><?php echo $orderDetails['totalOrder'];?></span>
  </div>
  <div class="sesevent_sale_stats">
  	<span>Total Sponsorship Sold</span>
    <span><?php echo $orderDetails['totalOrder']; ?></span>
  </div>
	<div class="sesevent_sale_stats">
  	<span>Total Amount</span>
    <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['totalAmountSale'],$defaultCurrency); ?></span>
  </div>
  <div class="sesevent_sale_stats">
  	<span>Total Commission Amount</span>
    <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($orderDetails['commission_amount'],$defaultCurrency); ?></span>
  </div>
  <div class="sesevent_sale_stats">
  	<span>Total Remaining Amount</span>
    <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->remainingAmount,$defaultCurrency); ?></span>
	</div>
</div>

<?php if($this->remainingAmount >= $this->thresholdAmount && count($this->userGateway)){ ?>
<div class="sesevent_request_payment_link ">	
	<a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-request'), 'sesevent_sponsorship', true); ?>" class="openSmoothbox sesbasic_button fa fa-money"><?php echo $this->translate("Make Request For Payment."); ?></a>
</div>
<?php } ?>
<?php if( isset($this->paymentRequests) && count($this->paymentRequests) > 0): ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form method="post" >
    <table>
      <thead>
        <tr>
          <th class="centerT"><?php echo $this->translate("Request Id"); ?></th>
           <th><?php echo $this->translate("Amount Requested") ?></th>
          <th><?php echo $this->translate("Requested Date") ?></th>
          <th><?php echo $this->translate("Requested Message") ?></th>
          <th><?php echo $this->translate("Release Amount") ?></th>
          <th><?php echo $this->translate("Release Date") ?></th>
          <th><?php echo $this->translate("Release Message") ?></th>
          <th><?php echo $this->translate("Status") ?></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
          foreach ($this->paymentRequests as $item): ?>
        <tr>
          <td class="centerT"><?php echo $item->usersponsorshippayrequest_id; ?></td>
          <td class="centerT"><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->requested_amount,$defaultCurrency); ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->dateFormat($item->creation_date	); ?></td> 
          <td class="centerT"><?php echo $this->string()->truncate(empty($item->user_message) ? '-' : $item->user_message, 30) ?></td>
          <td class="centerT"><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->release_amount	,$defaultCurrency); ?></td>
          <td><?php echo $item->release_date && (bool)strtotime($item->release_date) ? Engine_Api::_()->sesevent()->dateFormat($item->release_date) :  '-'; ?></td> 
          <td class="centerT"><?php echo $this->string()->truncate(empty($item->admin_message	) ? '-' : $item->admin_message, 30) ?></td>
          <td><?php echo $item->state; ?></td>
          <td class="table_options">
          	<?php if ($item->state == 'pending'){ ?>
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-request','id'=>$item->usersponsorshippayrequest_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Edit Request"), 'class' => 'openSmoothbox fa fa-edit')); ?>
                <?php echo $this->htmlLink($this->url(array('action' => 'delete-payment', 'id' => $item->usersponsorshippayrequest_id, 'event_id' => $this->event->custom_url), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Delete Request"), 'class' => 'openSmoothbox fa fa-trash')); ?>
            <?php } ?>
            		<?php echo $this->htmlLink($this->url(array('action' => 'detail-payment', 'id' => $item->usersponsorshippayrequest_id, 'event_id' => $this->event->custom_url), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("View Details"), 'class' => 'openSmoothbox fa fa-eye')); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("No sponsorship payment request made yet.") ?>
  </span>
</div>
<?php endif; ?>
</div>
</div>