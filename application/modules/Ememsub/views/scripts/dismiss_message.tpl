<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: dismiss_message.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php $ememsub_adminmenu = Zend_Registry::isRegistered('ememsub_adminmenu') ? Zend_Registry::get('ememsub_adminmenu') : null; ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/styles/admin/styles.css'); ?>
    
<h2><?php echo $this->translate("SNS - Membership Subscription Pricing Table & Plan Layout Plugin"); ?></h2>
<div class="ememsub_nav_btns" style="float:right;margin-top:-40px;">
  <a href="<?php echo $this->url(array('module' => 'ememsub', 'controller' => 'settings', 'action' => 'support'),'admin_default',true); ?>" class="help-btn" style="	background-color:#f36a33;border-radius:3px;background-position:10px center;background-repeat:no-repeat;color:#fff;float:left;font-weight:700;padding:7px 15px 7px 30px;margin-left:10px;position:relative;text-decoration:none;">Help &amp; Support</a>
</div>
<?php if( count($this->navigation) && $ememsub_adminmenu): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

