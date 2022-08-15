<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: settings.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>

<?php if( count($this->navigation)): ?>
  <div class='sesiosapp-admin-navgation'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?> </div>
<?php endif; ?>
<?php if( count($this->subnavigation)): ?>
  <div class='sesiosapp-admin-navgation'> <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render(); ?> </div>
<?php endif; ?>
<div class="settings sesiosapp_admin_form sesiosapp_notification">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
