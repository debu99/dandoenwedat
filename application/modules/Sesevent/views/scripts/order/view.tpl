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
<?php if($this->format == 'smoothbox' && empty($_GET['order'])){ ?>
<link href="<?php $this->layout()->staticBaseUrl ?>application/modules/Sesevent/externals/styles/print.css" rel="stylesheet" media="print" type="text/css" />
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/print.css'); ?>
<?php } ?>
<a href="javascript:;" onclick= "javascript:parent.Smoothbox.close();" class="fa fa-times sesevent_orderview_popup_close"></a>
<div class="layout_middle">
  <div class="sesevent_ticket_order_view_page generic_layout_container layout_core_content"> 
  <?php if($this->format != 'smoothbox'){ ?>
    <div class="clear sesevent_order_view_top">
      <a href="<?php echo $this->url(array('action'=>'my-tickets'), 'sesevent_my_ticket', true); ?>" class="buttonlink sesbasic_icon_back"><?php echo $this->translate("Back To My Tickets"); ?></a>
    </div>
    <?php } ?>
    <div class="sesevent_order_container sesevent_invoice_container sesbasic_bxs sesbasic_clearfix">
      <div class="sesevent_invoice_header sesbasic_clearfix">
        <div class="floatL">
         <?php echo $this->translate("Order Id:#%s",$this->order->order_id); ?>
        </div>
        <div class="floatR">
          <?php $totalAmount = $this->order->total_amount+$this->order->total_service_tax+$this->order->total_entertainment_tax; ?>
          [<?php echo $this->translate('Total:'); ?><?php echo $totalAmount <= 0 ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount,$this->order->currency_symbol,$this->order->change_rate); ?>]
        </div>
      </div>
      <div class="sesevent_invoice_content_wrap sesbm sesbasic_clearfix clear">
        <div class="sesevent_invoice_content_left sesbm">
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold"><?php echo $this->translate("Ordered For"); ?></b>
            <div class="sesevent_invoice_content_detail">
              <span><?php echo $this->htmlLink($this->event->getHref(),$this->event->getTitle()); ?></span>
              <span> 
        <?php $dateinfoParams['starttime'] = true; ?>
        <?php $dateinfoParams['endtime']  =  true; ?>
        <?php $dateinfoParams['timezone']  =  true; ?>
        <?php echo $this->eventStartEndDates($this->event,$dateinfoParams); ?>
	      </span>
            </div>
          </div>
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold"><?php echo $this->translate("Ordered By"); ?></b>
            <div class="sesevent_invoice_content_detail">
              <span><?php echo $this->htmlLink($this->viewer->getHref(), $this->viewer->getTitle()) ?></span>
              <span><?php echo $this->viewer->email; ?></span>
            </div>
          </div>
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold"><?php echo $this->translate("Payment Information"); ?></b>
            <div class="sesevent_invoice_content_detail">
              <span><?php echo $this->translate("Payment method: %s",$this->order->gateway_type); ?></span>
            </div>
          </div>
        </div>
        <div class="sesevent_invoice_content_right">
          <div class="sesevent_invoice_content_box sesbm">
            <b class="bold"><?php echo $this->translate("Order Information"); ?></b>
            <div class="sesevent_invoice_content_detail">	
              <span><?php echo $this->translate("Ordered Date :"); ?> <?php echo Engine_Api::_()->sesevent()->dateFormat($this->order->creation_date); ?></span>
              <?php if($this->order->total_service_tax > 0){ ?>
              <span><?php echo $this->translate("Service Tax :"); ?> <?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_service_tax,$this->order->currency_symbol,$this->order->change_rate); ?></span>
              <?php } ?>
              <?php if($this->order->total_entertainment_tax > 0){ ?>
              <span><?php echo $this->translate("Entertainment Tax :"); ?><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_entertainment_tax,$this->order->currency_symbol,$this->order->change_rate); ?></span>
              <?php } ?>
            </div>
          </div>
          <div class="sesevent_invoice_content_qr">
          	<?php
              if($this->order->ragistration_number){
              		$fileName = $this->order->getType().'_'.$this->order->getIdentity().'.png'; 
             	 		if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
                  	$fileName = Engine_Api::_()->sesevent()->generateQrCode($this->order->ragistration_number,$fileName);
                  }else{ 
                  	$fileName = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
                  }
                if($fileName){ ?>
                	<img src="<?php echo $fileName; ?>" style="height:150px;width:150px;" />
         		<?php   
         				}
             	}
             ?>
          </div>
          </div>
        </div>
      <div class="sesevent_invoice_header"><b class="bold"><?php echo $this->translate("Order Details"); ?></b></div>
      <div class="sesevent_table sesevent_invoice_order_table">
         <table>
          <tr>
            <th><?php echo $this->translate("Ticket Name"); ?></th>
            <th class="rightT"><?php echo $this->translate("Price"); ?></th>
            <th class="centerT"><?php echo $this->translate("Quantity"); ?></th>
            <th class="rightT"><?php echo $this->translate("Sub Total"); ?></th>
          </tr>
          <?php foreach($this->orderTickets as $orderTicket){ ?>
          <tr>
            <td><?php echo $orderTicket['title']; ?></td>
            <td class="rightT">
                <?php echo $orderTicket->price <= 0 ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice($orderTicket->price,$this->order->currency_symbol,$this->order->change_rate); ?><br />
                <?php if($orderTicket->service_tax >0){ ?>
                  <?php echo $this->translate("Service Tax :"); ?> <?php echo @round($orderTicket->service_tax,2).'%'; ?><br />
                <?php } ?>
                <?php if($orderTicket->entertainment_tax >0){ ?>
                  <?php echo $this->translate("Entertainment Tax :"); ?><?php echo @round($orderTicket->entertainment_tax,2).'%'; ?>
                <?php } ?>
            </td>
            <td class="centerT"><?php echo $orderTicket->quantity; ?></td>
            <td class="rightT">
              <?php $price= $orderTicket->price; ?>
              <?php echo $price <= 0 ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice(round($price*$orderTicket->quantity,2),$this->order->currency_symbol,$this->order->change_rate); ?><br />
                <?php if($orderTicket->service_tax > 0){ ?>
                  <?php $serviceTax = round(($price *($orderTicket->service_tax/100) )*$orderTicket->quantity,2); ?>
                   <?php echo $this->translate("Service Tax :"); ?> <?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($serviceTax,$this->order->currency_symbol,$this->order->change_rate); ?><br />
                <?php } ?>
                <?php if($orderTicket->entertainment_tax > 0){ ?>
                 <?php $entertainmentTax = round(($price *($orderTicket->entertainment_tax/100) ) * $orderTicket->quantity,2); ?>
                   <?php echo $this->translate("Entertainment Tax :"); ?> <?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($entertainmentTax,$this->order->currency_symbol,$this->order->change_rate); ?>
                <?php } ?>
            </td>
           </tr>
          <?php } ?>
        </table>
        <div class="sesevent_invoice_total_price_box sesbm">
          <div>
            <span><?php echo $this->translate("Subtotal:"); ?></span>
            <span><?php echo $this->order->total_amount <= 0 ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_amount,$this->order->currency_symbol,$this->order->change_rate); ?></span>
          </div>
          <?php if($this->order->total_service_tax > 0){ ?>
          <div>
            <span><?php echo $this->translate("Service Taxes :"); ?></span>
            <span><?php echo $this->order->total_service_tax > 0 ? Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_service_tax,$this->order->currency_symbol,$this->order->change_rate) : "-"; ?></span>
          </div>
          <?php } ?>
          <?php if($this->order->total_entertainment_tax > 0){ ?>
          <div>
            <span><?php echo $this->translate("Entertainment Taxes :"); ?></span>
            <span><?php echo $this->order->total_entertainment_tax > 0 ? Engine_Api::_()->sesevent()->getCurrencyPrice($this->order->total_entertainment_tax,$this->order->currency_symbol,$this->order->change_rate) : "-"; ?></span>
          </div>
         <?php } ?>
          <div class="sesevent_invoice_total_price_box_total">
            <span><?php echo $this->translate("Grand Total :"); ?></span>
            <span><?php echo $totalAmount <= 0  ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice($totalAmount,$this->order->currency_symbol,$this->order->change_rate); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if($this->format == 'smoothbox' && empty($_GET['order'])){ ?>
<style type="text/css" media="print">
  @page { size: landscape; }
</style>
<script type="application/javascript">
sesJqueryObject(document).ready(function(e){
		window.print();
});
</script>
<?php } ?>