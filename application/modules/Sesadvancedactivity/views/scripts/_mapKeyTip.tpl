<?php 

?>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.mapApiKey', '')) { ?>
  <div class="tip"><span><?php echo "The 'Check-in' feature of this plugin requires 'Google Map API Key' which you can configure in the Global Settings of <a href='admin/sesbasic/settings/global' target='_blank'>SocialNetworking Basic </a> Required Plugin. So, to enable the Check-in feature, please enter the 'Google Map API Key'. If the key is not entered or if you have disabled Google location, then this feature will not be available to the members of your website."; ?></span></div>
<?php } ?>
