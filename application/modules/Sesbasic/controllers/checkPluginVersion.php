<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: checkPluginVersion.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
	//$db = Engine_Db_Table::getDefaultAdapter();

	$select = new Zend_Db_Select($db);
	$select->from('engine4_core_modules')
					->where('name = ?', 'sesbasic');
	$results = $select->query()->fetchObject();
	$pluginVersion = $results->version;

	$sesbasicSiteversion = @explode('p', $plugin_currentversion);
	$sesbasiCurrentversionE = @explode('p', $pluginVersion);
	
	if(isset($sesbasiCurrentversionE[0]))
		$sesbasiCurrentVersion = @explode('.', $sesbasiCurrentversionE[0]);
		
	if(isset($sesbasiCurrentversionE[1]))
		$sesbasiCurrentVersionP = $sesbasiCurrentversionE[1];
		
	$finalVersion = 1;
	$versionB  = false;
	
	foreach($sesbasicSiteversion as $versionSite) {
		$sesVersion = explode('.', $versionSite);
		if(count($sesVersion) > 1){
		$counterV = 0;
		foreach($sesVersion as $key => $version) {
			if(isset($sesbasiCurrentVersion[$key]) && $version < $sesbasiCurrentVersion[$key]){
				$versionB = true;
				$finalVersion = 1;
				break;
			}
			if(isset($sesbasiCurrentVersion[$key]) && $version > $sesbasiCurrentVersion[$key] && 	$version != $sesbasiCurrentVersion[$key]) {
				$finalVersion = 0;
				break;
			}
			$counterV++;
		}
		} else {
			//string after p
			if(isset($sesbasiCurrentVersionP)){
				if( $versionSite > $sesbasiCurrentVersionP && $versionSite != $sesbasiCurrentVersionP) {
					$finalVersion = 0;
					break;
				}
			} else {
				$finalVersion = 0;
				break;
			}
		}
		//check if final result is false exit
		if(!$finalVersion || $versionB)
			break;
	}

	if (empty($results)) {
		return '<div class="global_form"><div><div><p style="color:red;">The required SocialEngineSolutions Basic Required Plugin is not installed on your website. Please download the latest version of this FREE plugin from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>';
	} else {
		if (isset($results->enabled) && !empty($results->enabled)) {
			if (empty($finalVersion)) {
				return '<div class="global_form"><div><div><p style="color:red;">The latest version of the SocialEngineSolutions Basic Required Plugin installed on your website is less than the minimum required version: ' . $plugin_currentversion . '. Please upgrade this Free plugin to its latest version after downloading the latest version of this plugin from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>';
			} else {
				return '1';
			}
		} else {
			return '<div class="global_form"><div><div><p style="color:red;">The SocialEngineSolutions Basic Required Plugin is installed but not enabled on your website. So, please first enable it from the "Manage" >> "Packages & Plugins" section.</p></div></div></div>';
		}
	}
    
?>