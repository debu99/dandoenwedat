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
		The payment has been successfully sent to the event owner. <?php echo $this->htmlLink($this->url(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment','action'=>'index')), $this->translate("Back to Payment Requests")); ?>
<?php }else{ ?>
	The payment has been failed or cancelled. <?php echo $this->htmlLink($this->url(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment','action'=>'index')), $this->translate("Back to Payment Requests")); ?>
<?php } ?>
 