<?php ?>

<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>

<h3 style="margin-bottom:6px;"><?php echo $this->translate("Integrate and Manage Other Plugins"); ?></h3>
<p>In this page, you can enable the creation of Browse Events Page in other plugins. Below you can add the integration with any plugin using “Add New Plugin” button.<br /><br />
This process is very easy, but still if you face any difficulty, then please contact our support team for Free integration of other plugins from here: <a href="http://www.socialenginesolutions.com/tickets" target="_blank">http://www.socialenginesolutions.com/tickets</a> .</p>
<br class="clear" />
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'integrateothermodule', 'action' => 'addmodule'), $this->translate("Add New Plugin"), array('class'=>'buttonlink sesbasic_icon_add'));
?>
<br /><br />

<?php if(count($this->paginator)): ?>
<form id='multidelete_form'>
  <table class='admin_table'>
    <thead>
      <tr>
        <th align="left">
          <?php echo $this->translate("Plugin Name"); ?>
        </th>
        <th align="left">
          <?php echo $this->translate("Item Type"); ?>
        </th>
        <th align="left">
          <?php echo $this->translate("Item Type URL"); ?>
        </th>
        <th class="left">
          <?php echo $this->translate("Enabled"); ?>
        </th>
        <th align="left">
          <?php echo $this->translate("Options"); ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
      <?php 
      $module_name = $item->module_name; 
      $enabledModules = $this->enabledModules;
      ?>
      <?php if(in_array($module_name, $enabledModules )): ?>
      <tr>
        <td><?php if( !empty($item->module_name) ){ echo $item->module_name; }else { echo '-'; } ?></td>
        <td><?php if( !empty($item->content_type) ){ echo $item->content_type; }else { echo '-'; } ?></td>
        <td><?php if( !empty($item->content_url) ){ echo $item->content_url; }else { echo '-'; } ?></td>
        <td><?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'integrateothermodule', 'action' => 'enabled', 'integrateothermodule_id' => $item->integrateothermodule_id ), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Disable'))), array())  : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'integrateothermodule', 'action' => 'enabled', 'integrateothermodule_id' => $item->integrateothermodule_id ), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Enable')))) ) ?></td >
        <td>
          <?php if(empty($item->default)):?>
          <a href="<?php echo $this->url(array('action' => 'delete', 'integrateothermodule_id' => $item->integrateothermodule_id)) ?>" class="smoothbox"><?php echo $this->translate("Delete") ?>
          </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>
      <?php  endforeach; ?>
    </tbody>
  </table>
</form>
<br />
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("No plugins are integrated yet.") ?>
  </span>
</div>
<?php endif; ?>
