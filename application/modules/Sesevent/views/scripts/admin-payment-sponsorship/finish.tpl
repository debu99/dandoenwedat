<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: finish.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(empty($this->error)){ ?>
		Order payment done successfully.
<?php }else{ ?>
	Order payment failed or cancelled successfully.
<?php } ?>
 <?php echo $this->htmlLink($this->url(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment-sponsorship','action'=>'index')), $this->translate("Back to payment request page")); ?>