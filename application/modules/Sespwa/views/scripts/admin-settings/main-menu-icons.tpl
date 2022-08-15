<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: main-menu-icons.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php include APPLICATION_PATH .  '/application/modules/Sespwa/views/scripts/dismiss_message.tpl';?>
<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'menu-settings'), $this->translate('Menus Settings')) ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'main-menu-icons'), $this->translate('Main Menu Icons')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'settings', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
    </li>
  </ul>
</div>
<h3><?php echo "Manage Main Menu Icons"; ?></h3>
<p><?php echo "Here, you can add icons for the Main Navigation Menu Items of your website. You can also edit and delete the icons."; ?> </p>
<br />

<table class='admin_table sespwa_manangemenu_table'>
  <thead>
    <tr>
      <th><?php echo $this->translate("Menu Item") ?></th>
      <th><?php echo $this->translate("Icon") ?></th>
      <th><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->paginator as $item): ?>
      <tr id="<?php echo $item->id; ?>">
        <td><?php echo $item->label ?></td>
        
        <?php $getRow = Engine_Api::_()->getDbTable('menusicons','sesbasic')->getRow($item->id); ?>
        
        <td><?php if(($getRow)):
          $photo = $this->storage->get($getRow->sespwa_icon_id, '');
        ?>
          <?php $label = 'Edit';?>
          <img alt="" src="<?php echo $photo ? $photo->getPhotoUrl() : ""; ?>" />
									<?php else:?>
          <?php $label = 'Add';?>
              -
									<?php endif;?></td>
        <td>
          <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'sespwa', 'controller' => 'admin-settings', 'action' => 'upload-icon', 'id' => $item->id,'type' => 'main'),
                $label,
                array('class' => 'smoothbox')) ?>
          <?php if(($getRow)):?>
          | 
          <?php echo $this->htmlLink(
            array('route' => 'default', 'module' => 'sespwa', 'controller' => 'admin-settings', 'action' => 'delete-menu-icon', 'id' => $item->id, 'file_id' => $getRow->sespwa_icon_id),
            $this->translate("Delete"),
            array('class' => 'smoothbox')) ?>
          <?php endif;?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
