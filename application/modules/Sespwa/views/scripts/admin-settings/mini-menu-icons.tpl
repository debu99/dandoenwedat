<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: mini-menu-icons.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php include APPLICATION_PATH .  '/application/modules/Sespwa/views/scripts/dismiss_message.tpl';?>
<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'menu-settings'), $this->translate('Menus Settings')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'main-menu-icons'), $this->translate('Main Menu Icons')) ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
    </li>
  </ul>
</div>
<div class='clear sesbasic_admin_form ariana_header_settings_form'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
