<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: gateway.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<?php if( $this->status == 'pending' ): // Check for pending status ?>
  Your subscription is pending payment. You will receive an email when the
  payment completes.
<?php else: ?>
  <form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process'))) ?>"
        class="global_form" enctype="application/x-www-form-urlencoded">
    <div>
      <div>
        <h3>
          <?php echo $this->translate('Pay for Access') ?>
        </h3>
        <?php if( $this->package->recurrence ): ?>
        <p class="form-description">
          <?php echo $this->translate('You have selected an account type that requires ' .
            'recurring subscription payments. You will be taken to a secure ' .
            'checkout area where you can setup your subscription. Remember to ' .
            'continue back to our site after your purchase to sign in to your ' .
            'account.') ?>
        </p>
        <?php endif; ?>
        <p style="font-weight: bold; padding-top: 15px; padding-bottom: 15px;">
          <?php if( $this->package->recurrence ): ?>
            <?php echo $this->translate('Please setup your subscription to continue:') ?>
          <?php else: ?>
            <?php echo $this->translate('Please pay a one-time fee to continue:') ?>
          <?php endif; ?>
          <?php echo $this->package->getPackageDescription() ?>
        </p>
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
                
                <button type="submit" name="execute" onclick="$('gateway_id').set('value', '<?php echo $gateway->gateway_id ?>')">
                  <?php if($gateway->title === "Stripe") {?>
                    <?php echo $this->translate('Pay with IDeal or Creditcard') ?>
                  <?php } else {?>
                    <?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?>
                  <?php }?>
                </button>
              <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <input type="hidden" name="gateway_id" id="gateway_id" value="" />
  </form>
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')): ?>
    <?php  echo $this->partial('have_coupon.tpl','ecoupon',array('id'=>$this->package->package_id,'params'=>json_encode(array('resource_type'=>$this->subscription->getType(),'resource_id'=>$this->subscription->subscription_id,'is_package'=>1,'package_type'=>$this->package->getType(),'package_id'=>$this->package->package_id)))); ?> 
  <?php endif; ?>
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')) { ?>
    <?php  echo $this->partial('apply_credit.tpl','sescredit',array('id'=>$this->package->package_id,'moduleName'=>'payment','item_price'=>$this->itemPrice,'item_id'=>$this->subscription->subscription_id)); ?> 
  <?php } ?>
  <script type="application/javascript">
    var itemPrice<?php echo $this->package->package_id; ?> = '<?php echo $this->itemPrice; ?>';
  </script>
<?php endif; ?>
