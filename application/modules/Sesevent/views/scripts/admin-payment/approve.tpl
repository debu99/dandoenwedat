<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: approve.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->disable_gateway){ ?>
<div class='sesevent_approve_payment_popup'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php }else{?>
	  <div class="tip">
    <span>
      <?php echo $this->translate("no payment gateway enable.") ?>
    </span>
  </div>
<?php } ?>
