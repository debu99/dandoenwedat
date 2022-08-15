
<h2><?php echo $this->translate('SocialEngineSolutions Basic Required Plugin'); ?></h2>
<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<h3><?php echo "Upgrade Plugins"; ?></h3>
<p>
	<?php echo $this->translate("") ?>	
</p>
<br class="clear" />
<?php if( count($this->plugns_array) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <table class='admin_table'>
    <thead>
      <tr>
        <th><?php echo $this->translate("Plugin Name") ?></th>
        <th align="center"><?php echo $this->translate("Version on Your Site") ?></th>
        <th><?php echo $this->translate("Current Version") ?></th>
        <th><?php echo $this->translate("Need Upgrade") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->plugns_array as $key => $item): //print_r($item['module_name']);die;  ?>
        <?php $plugin_installed = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($item['module_name']); if(!$plugin_installed) continue;
        $pluginVersionOnSite = Engine_Api::_()->sesbasic()->pluginVersion($item['module_name']);
        $checkPluginVersion = Engine_Api::_()->sesbasic()->checkPluginVersion($item['module_name'], $item['current_version']);
        ?>
        <tr>
          <td><a href="<?php echo $item['pluginpage_link'] ?>" target="_blank"><?php echo $item['title']; ?></a></td>
          <td class="admin_table_centered"><?php echo $pluginVersionOnSite; ?></td>
          <td><?php echo $item['current_version']; ?></td>
          <td><?php if(empty($checkPluginVersion)):  echo '<b>Yes</b>'; else: echo "No"; endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <br />
  </form>
  <br />
<?php else: ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no plugins") ?>
    </span>
  </div>
<?php endif; ?>