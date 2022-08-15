<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: account-details.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
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
<div class="sesbasic_dashboard_form sesevent_dashboard_account_details">
  <ul class="sesevent_dashboard_sub_tabs">
      <li class="<?php echo $this->gateway_type == 'paypal' ? '_active' : ''; ?>"><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'gateway_type'=>"paypal"), 'sesevent_account_details', true); ?>" class="sesbasic_dashboard_nopropagate_content"><i class="fab fa-cc-paypal"></i><span><?php echo $this->translate('Paypal Details'); ?></span></a></li>
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvpmnt') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvpmnt.enable.package', 1)){ ?>
          <li class="<?php echo $this->gateway_type == 'stripe' ? '_active' : ''; ?>"><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'gateway_type'=>"stripe"), 'sesevent_account_details', true); ?>" class="sesbasic_dashboard_nopropagate_content"><i class="fab fa-cc-stripe"></i><span><?php echo $this->translate('Stripe Details'); ?></span></a></li>
      <?php } ?>
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('epaytm')){ ?>
          <li class="<?php echo $this->gateway_type == 'paytm' ? '_active' : ''; ?>"><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'gateway_type'=>"paytm"), 'sesevent_account_details', true); ?>" class="sesbasic_dashboard_nopropagate_content"><i class="fab fa-cc-paytm"></i><span><?php echo $this->translate('Paytm Details'); ?></span></a></li>
      <?php } ?>
  </ul>
    
<?php echo $this->form->render() ?>
</div>
<?php if(!$this->is_ajax){ ?>
    </div>
		</div>
	</div>
</div>
<?php  } ?>
<?php if($this->is_ajax) die; ?>
