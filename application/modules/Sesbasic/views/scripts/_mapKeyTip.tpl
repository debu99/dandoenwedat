<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesvideo
 * @package    Sesvideo
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: share.tpl 2015-10-11 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.mapApiKey', '') && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
  <div class="tip"><span><?php echo "You have enabled Google APIs for Location from <a href='admin/sesbasic/settings/global' target='_blank'>here</a>, but you have not entered the \"Google Map API Key\". So, please enter the Google Maps API Key to enable your users to select locations via Google's Autocomplete for Addresses feature."; ?></span></div>
<?php } ?>
