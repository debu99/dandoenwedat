<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _date.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php if($transaction->creation_date):?>
	<?php if(empty($localeLanguage)):?>
	  <?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/_language.tpl';?>
	  <?php $localeLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');?>
	  <?php if( 1 !== count($languageNameList)):?>
		<?php $localeLanguage = $_COOKIE['en4_language'];?>
	  <?php endif;?>
	<?php endif;?>
	<?php  $locale = new Zend_Locale($localeLanguage);?>
	<?php Zend_Date::setOptions(array('format_type' => 'php'));?>
	<?php $date = new Zend_Date(strtotime($transaction->creation_date), false, $locale);?>
	<div class="_date" title=""><?php echo $date->toString('jS M');?>,&nbsp;<?php echo date('Y', strtotime($transaction->creation_date));?></div>
<?php endif;?>