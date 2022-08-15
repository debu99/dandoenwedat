
<h2><?php echo $this->translate('SocialEngineSolutions Basic Required Plugin'); ?></h2>
<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<h3><?php echo "Not Installed Plugins on Your Site"; ?></h3>
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
        <th align="center"><?php echo $this->translate("Explore") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->plugns_array as $key => $item): ?>
        <?php $plugin_installed = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($key); if($plugin_installed) continue; 
        $pluginVersionOnSite = Engine_Api::_()->sesbasic()->pluginVersion($key);
        ?>
        <tr>
          <td><?php echo $item['title']; ?></td>
          <td class="admin_table_centered"><a href="<?php echo $item['pluginpage_link'] ?>" target="_blank"><?php echo "Read More"; ?></a></td>
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