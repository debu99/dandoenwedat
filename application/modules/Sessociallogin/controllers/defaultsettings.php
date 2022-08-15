<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

$db = Zend_Db_Table_Abstract::getDefaultAdapter();

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sessociallogin_admin_main_faq", "sessociallogin", "FAQ", "", \'{"route":"admin_default","module":"sessociallogin","controller":"settings","action":"faq"}\', "sessociallogin_admin_main", "", 568);');

$db->query('INSERT IGNORE INTO engine4_user_signup (`class`,`order`,enable) SELECT REPLACE(`class`,"User_","Sessociallogin_"),`order`,1 FROM engine4_user_signup WHERE class IN ("User_Plugin_Signup_Account","User_Plugin_Signup_Fields","User_Plugin_Signup_Photo");');

$db->query('UPDATE `engine4_user_signup` SET enable = 0 WHERE class IN ("User_Plugin_Signup_Account","User_Plugin_Signup_Fields","User_Plugin_Signup_Photo");');
