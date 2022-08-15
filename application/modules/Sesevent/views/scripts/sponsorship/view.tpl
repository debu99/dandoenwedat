<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="layout_middle">
  <div class="sesevent_ticket_order_view_page generic_layout_container layout_core_content"> 
  <?php if(empty($_GET['smoothbox'])){ ?>
    <div class="clear sesevent_order_view_top">
      <a href="<?php echo $this->url(array('action'=>'my-tickets'), 'sesevent_my_ticket', true); ?>" class="buttonlink sesbasic_icon_back"><?php echo $this->translate("Back To My Tickets");?></a>
    </div>
  <?php } ?>
    <div class="sesevent_order_container sesevent_invoice_container sesbasic_bxs sesbasic_clearfix">
      <div class="sesevent_invoice_header sesbasic_clearfix">
        <div class="floatL">
          Order Id:#<?php echo $this->order->sponsorshiporder_id; ?>
        </div>
        <div class="floatR">
          <?php $totalAmount = $this->order->total_amount+$this->order->total_service_tax+$this->order->total_entertainment_tax; ?>
          [Total:<?php echo $totalAmount <= 0 ? 'FREE' : Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount,$this->order->currency_symbol,$this->order->change_rate); ?>]
        </div>
      </div>
      <div class="sesevent_invoice_content_wrap sesbm sesbasic_clearfix clear">
        <div class="sesevent_invoice_content_left sesbm">
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold">Ordered For</b>
            <div class="sesevent_invoice_content_detail">
            	<span><?php echo $this->translate('Sponsorship').' : '.$this->sponsorship->title; ?></span>
              <span><?php echo $this->htmlLink($this->event->getHref(),$this->event->getTitle()); ?></span>
              <span><?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->starttime); ?> - <?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->endtime); ?></span>
            </div>
          </div>
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold">Ordered By</b>
            <div class="sesevent_invoice_content_detail">
              <span><?php echo $this->htmlLink($this->viewer->getHref(), $this->viewer->getTitle()) ?></span>
              <span><?php echo $this->viewer->email; ?></span>
            </div>
          </div>
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold">Payment Information</b>
            <div class="sesevent_invoice_content_detail">
              <span>Payment method : <?php echo $this->order->gateway_type ?></span>
            </div>
          </div>
        </div>
        <div class="sesevent_invoice_content_right">
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold">Order Information</b>
            <div class="sesevent_invoice_content_detail">	
              <span>Ordered Date : <?php echo Engine_Api::_()->sesevent()->dateFormat($this->order->creation_date); ?></span>
              <?php if($this->order->total_service_tax > 0){ ?>
              <span>Service Tax : <?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_service_tax,$this->order->currency_symbol,$this->order->change_rate); ?></span>
              <?php } ?>
              <?php if($this->order->total_entertainment_tax > 0){ ?>
              <span>Entertainment Tax :<?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_entertainment_tax,$this->order->currency_symbol,$this->order->change_rate); ?></span>
              <?php } ?>
            </div>
          </div>
          </div>
        </div>
      <div class="sesevent_table sesevent_invoice_order_table">
        <div class="sesevent_invoice_total_price_box sesbm">
          <div>
            <span>Subtotal:</span>
            <span><?php echo $this->order->total_amount <= 0 ? 'FREE' : Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_amount,$this->order->currency_symbol,$this->order->change_rate); ?></span>
          </div>
          <?php if($this->order->total_service_tax > 0){ ?>
          <div>
            <span>Service Taxes:</span>
            <span><?php echo $this->order->total_service_tax > 0 ? Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_service_tax,$this->order->currency_symbol,$this->order->change_rate) : "-"; ?></span>
          </div>
          <?php } ?>
          <?php if($this->order->total_entertainment_tax > 0){ ?>
          <div>
            <span>Entertainment Taxes:</span>
            <span><?php echo $this->order->total_entertainment_tax > 0 ? Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_entertainment_tax,$this->order->currency_symbol,$this->order->change_rate) : "-"; ?></span>
          </div>
         <?php } ?>
          <div class="sesevent_invoice_total_price_box_total">
            <span>Grand Total:</span>
            <span><?php echo $totalAmount <= 0  ? 'FREE' : Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount,$this->order->currency_symbol,$this->order->change_rate); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>