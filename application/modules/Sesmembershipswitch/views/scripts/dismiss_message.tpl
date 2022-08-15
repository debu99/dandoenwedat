<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: dismiss_message.tpl  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<h2><?php echo $this->translate("Auto Switching / Notification of Subscription Plans") ?></h2>
<div class="sesbasic_nav_btns">
  <a href="<?php echo $this->url(array('module' => 'sesmembershipswitch', 'controller' => 'settings', 'action' => 'support'),'admin_default',true); ?>" class="request-btn">Help</a>
</div>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
