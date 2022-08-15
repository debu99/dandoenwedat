<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: checkout.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $givenSymbol = Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="layout_middle sesbasic_bxs">
	<div class="generic_layout_container layout_core_content">
    <div class="sesevent_ticket_checkout_page sesbasic_clearfix">
      <div class="sesevent_ticket_order_page_right">
        <div class="sesevent_ticket_order_info_box sesbm sesbasic_bxs">
          <span class="sesevent_ticket_order_info_box_title"><?php echo $this->translate("Order Information"); ?>
            <a href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_ticket', true); ?>" class="fa fa-times" title="Cancel Order"></a>
          </span>
          <div class="sesevent_ticket_order_info">
            <div class="sesbasic_clearfix">
            <div class="sesevent_ticket_order_info_photo">
              <?php echo $this->htmlLink($this->event->getHref(), $this->itemPhoto($this->event, 'thumb.icon')) ?>
            </div>
            <div class="sesevent_ticket_order_info_name"><?php echo $this->htmlLink($this->event->getHref(),$this->event->getTitle()); ?></div>
            </div>
            <div class="sesevent_ticket_order_info_stats sesbasic_clearfix">
            <?php if($this->event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){ ?>
              <span>
                <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->event->location; ?>"></i>
                <span>
                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                    <a href="<?php echo $this->url(array('resource_id' => $this->event->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->event->location ?></a>
                  <?php } else { ?>
                    <?php echo $this->event->location;?>
                  <?php } ?>
                </span>
              </span>
              <?php } ?>
              <span>
                <i class="far fa-calendar-alt sesbasic_text_light" title=""></i>
                <span>
                  <?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->starttime,'changetimezone',$this->event); ?> to <?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->endtime,'changetimezone',$this->event); ?>
                 </span>
              </span>
            </div>
            <div class="sesevent_ticket_order_info_summary sesbm">
              <span class="sesevent_ticket_order_info_box_title"><?php echo $this->translate("Order Summary"); ?></span>
            <?php foreach($this->ticketDetail->toArray() as $valTicket){ ?>
              <p class="sesbasic_clearfix">
                <span><?php echo $valTicket['title'] .' X '.$valTicket['quantity']; ?></span>
                <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($valTicket['price']*$valTicket['quantity']); ?></span>
              </p>
            <?php } ?>
              <p class="sesbasic_clearfix">
                <span><?php echo $this->translate("Total Tax"); ?></span>
                <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(($this->order->total_service_tax+$this->order->total_entertainment_tax)); ?></span>
              </p>
               <?php
                $CurrentAmount = $totalAmount = $this->order->total_service_tax+$this->order->total_entertainment_tax+$this->order->total_amount;
                if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')):
                $couponSessionCode = '-'.'-'.$this->event->getType().'-'.$this->event->event_id.'-0'; 
                $CurrentAmount = @isset($_SESSION[$couponSessionCode]) ? round($totalAmount - $_SESSION[$couponSessionCode]['discount_amount']) : $totalAmount;
              ?> 
                <?php  if(isset($_SESSION[$couponSessionCode]['discount_amount'])): ?>
                  <p class="sesbasic_clearfix">
                    <span><?php echo $this->translate("Coupon Applied"); ?></span>
                    <span><?php echo "-".Engine_Api::_()->sesevent()->getCurrencyPrice($_SESSION[$couponSessionCode]['discount_amount']); ?></span>
                  </p>
                 <?php endif; ?>
              <?php endif; ?>
               <?php
                if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')):
                  $creditCode =  'credit'.'-sesevent-'.$this->event->event_id.'-'.$this->event->event_id;
                  $sessionCredit = new Zend_Session_Namespace($creditCode);
              ?> 
              <?php  if(isset($sessionCredit->total_amount) && $sessionCredit->total_amount > 0): ?>
                <?php  $CurrentAmount = round(($CurrentAmount - $sessionCredit->purchaseValue),2); ?>
                  <p class="sesbasic_clearfix">
                    <span><?php echo $this->translate("Credit Points Redeemed"); ?></span>
                    <span><?php echo "-".Engine_Api::_()->sesevent()->getCurrencyPrice($sessionCredit->purchaseValue); ?></span>
                  </p>
                 <?php endif; ?>
              <?php endif; ?>
              <p class="sesbasic_clearfix">
                <span><?php echo $this->translate("Grand Total"); ?></span>
                <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($CurrentAmount); ?></span>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="sesevent_checkout_form">
      <form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>" enctype="application/x-www-form-urlencoded">
          <div class="sesevent_checkout_form_title">
            <?php echo $this->translate('Pay') ?>
          </div>
          <div id="buttons-wrapper" class="sesevent_checkout_form_btns">
            <?php foreach( $this->gateways as $gatewayInfo ):
                  $gateway = $gatewayInfo['gateway'];
                  $plugin = $gatewayInfo['plugin'];
                  $gatewayObject = $gateway->getGateway();
                  $supportedCurrencies = $gatewayObject->getSupportedCurrencies();
                  if(!in_array($givenSymbol,$supportedCurrencies))
                    continue;
                  ?>
          <button type="submit" name="execute"  onclick="$('gateway_id').set('value', '<?php echo $gateway->gateway_id ?>')">
            <?php if($gateway->title === "Stripe") {?>
              <?php echo $this->translate('Pay with IDeal or Creditcard') ?>
            <?php } else {?>
              <?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?>
            <?php }?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="gateway_id" id="gateway_id" value="" />
      </form>
      <div class="sesbasic_loading_cont_overlay" style="display:none"></div>
      </div>
    </div>
  </div>
</div>
