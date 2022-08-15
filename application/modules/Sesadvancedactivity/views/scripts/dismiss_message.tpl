<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: dismiss_message.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<h2>
  <?php echo $this->translate("Professional Activity & Nested Comments Plugin") ?>
</h2>
<?php include APPLICATION_PATH .  '/application/modules/Sesbasic/views/scripts/_mapKeyTip.tpl';?>
<?php
$sesadvancedactivity_adminmenu = Zend_Registry::isRegistered('sesadvancedactivity_adminmenu') ? Zend_Registry::get('sesadvancedactivity_adminmenu') : null;
if(!empty($sesadvancedactivity_adminmenu)) { ?>
  <?php if( count($this->navigation) ): ?>
    <div class='tabs'>
      <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
    </div>
  <?php endif; ?>
<?php } ?>
