<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: settings.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/odering.js'); ?>
<h3>Module Name & Feed Display Settings</h3>
<p>Here, you can modify the names of modules which will be displayed in various widgets of this plugin.</p><br />
<p>You can also choose to display the activities of 1 module into another module, simply by chosing the parent module by editing it. This will be useful to show activities of an extension into its parent module.</p><br />
<div>
  <div id="error-message-category-delete"></div>
  <table class='admin_table' style="width:50%;">
    <tbody>
			<thead>
				<tr>
					<th style="display:none;"></th>
					<th style="width:40%;">Module</td>
                    <th style="width:40%;" align="center">Status</td>
					<th>Options</th>
				</tr>
            </thead>
            <tr style='display:none;'></tr>
      <?php foreach ($this->modules as $module): ?>
      
        <tr id="moduleid-<?php echo $module->name; ?>" data-article-id="<?php echo $module->name; ?>">
           <td style='display:none;'><input type="checkbox" class="checkbox check-column" name="delete_tag[]" value="<?php echo $module->name; ?>" /></td>
          <td><?php echo $module->name ?>
          <div class="hidden" style="display:none" id="inline_<?php echo $module->name; ?>">
            <div class="parent">0</div>
          </div>
          </td>
          <td class="admin_table_centered">
           <?php if($module->name != ''){ ?>
            <?php if($module->status == 1):?>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sescredit', 'controller' => 'credits', 'action' => 'enable-plugin', 'plugin' => $module->name, 'id' => $module->modulesetting_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disable')))) ?>
            <?php else: ?>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sescredit', 'controller' => 'credits', 'action' => 'enable-plugin', 'plugin' => $module->name,'id' => $module->modulesetting_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable')))) ?>
            <?php endif; ?>
          <?php } ?>
          </td>
          <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sescredit', 'controller' => 'credits', 'action' => 'show-parent-type', 'plugin' => $module->name), $this->translate('Edit'), array('class' => 'smoothbox')) ?> 
          </td>
        </tr>
        <?php $subModules = Engine_Api::_()->getDbTable('modulesettings', 'sescredit')->getModuleChild(array('parent_id' => $module->name));?>
        <?php if(count($subModules) > 0):?>
          <?php foreach ($subModules as $subModule):  ?>
            <tr id="moduleid-<?php echo $subModule->name; ?>" data-article-id="<?php echo $subModule->name; ?>">
              <td style='display:none;'><input type="checkbox"  class="checkbox check-column" name="delete_tag[]" value="<?php echo $subModule->name; ?>" /></td>
              <td>-&nbsp;<?php echo $subModule->name ?>
                <div class="hidden" style="display:none" id="inline_<?php echo $subModule->name; ?>">
                  <div class="parent"><?php echo $subModule->parent_id; ?></div>
                </div>
              </td>
               <td class="admin_table_centered"> 
            <?php if($subModule->status == 1):?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-credits', 'action' => 'enable-plugin', 'id' => $subModule->modulesetting_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disabled')))) ?>
            <?php else: ?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-credits', 'action' => 'enable-plugin', 'id' => $subModule->modulesetting_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enabled')))) ?>
            <?php endif; ?>
              </td>
              <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sescredit', 'controller' => 'credits', 'action' => 'show-parent-type', 'plugin' => $subModule->name), $this->translate('Edit'), array('class' => 'smoothbox')) ?>  
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif;?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script type='text/javascript'>
  ajaxurl = en4.core.baseUrl+"admin/sescredit/credits/change-order";
</script>