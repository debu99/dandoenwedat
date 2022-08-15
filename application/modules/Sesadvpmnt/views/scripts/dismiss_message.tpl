<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: dismiss_message.tpl  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<h2><?php echo $this->translate("Stripe Payment Gateway Plugin") ?></h2>
<?php $sesadvpmnt_adminmenu = Zend_Registry::isRegistered('sesadvpmnt_adminmenu') ? Zend_Registry::get('sesadvpmnt_adminmenu') : null; ?>
<?php if($sesadvpmnt_adminmenu) { ?>
  <?php if(count($this->navigation) ): ?>
    <div class='sesbasic-admin-navgation'>
      <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
    </div>
  <?php endif; ?>
<?php } ?>
