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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="sesevent_checkout_form">
<form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>" class="global_form" enctype="application/x-www-form-urlencoded">
  <div>
    <div>
      <h3> <?php echo $this->translate('Pay') ?> </h3>
      <p style="font-weight: bold; padding-top: 15px; padding-bottom: 15px;"></p>
      <div class="form-elements">
        <div id="buttons-wrapper" class="form-wrapper">
          <?php foreach( $this->gateways as $gatewayInfo ):
                $gateway = $gatewayInfo['gateway'];
                $plugin = $gatewayInfo['plugin'];
                $first = ( !isset($first) ? true : false );
                ?>
          <?php if( !$first ): ?>
          <?php echo $this->translate('or') ?>
          <?php endif; ?>
          <button type="submit" name="execute" onclick="$('gateway_id').set('value', '<?php echo $gateway->gateway_id ?>')"> <?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?> </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" name="gateway_id" id="gateway_id" value="" />
</form>
<div class="sesbasic_loading_cont_overlay" style="display:none"></div>
</div>