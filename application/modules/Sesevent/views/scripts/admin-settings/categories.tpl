<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: categories.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>

<div class='clear'>
  <div class='settings'>
    <form class="global_form">
      <div>
        <h3> <?php echo $this->translate("Event Categories") ?> </h3>
        <p class="description">
          <?php echo $this->translate("SESEVENT_VIEWS_SCRIPTS_ADMINSETTINGS_CATEGORIES_DESCRIPTION") ?>
        </p>
        <?php if(count($this->categories)>0):?>

        <table class='admin_table'>
          <thead>
            <tr>
              <th><?php echo $this->translate("Category Name") ?></th>
              <?php //              <th># of Times Used</th>?>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>

          </thead>
          <tbody>
            <?php foreach ($this->categories as $category): ?>
            <tr>
              <td><?php echo $category->title?></td>
              <td>
                <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-settings', 'action' => 'edit-category', 'id' =>$category->category_id),
                $this->translate('edit'),
                array('class' => 'smoothbox',
                )) ?>
                |
                <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-settings', 'action' => 'delete-category', 'id' =>$category->category_id),
                $this->translate('delete'),
                array('class' => 'smoothbox',
                )) ?>

              </td>
            </tr>

            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else:?>
        <br/>
        <div class="tip">
          <span><?php echo $this->translate("There are currently no categories.") ?></span>
        </div>
        <?php endif;?>
        <br/>

        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'add-category'), $this->translate('Add New Category'), array(
        'class' => 'smoothbox buttonlink',
        'style' => 'background-image: url(' . $this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/new_category.png);')) ?>

      </div>
    </form>
  </div>
</div>
