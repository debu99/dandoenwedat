 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.10.3p5-4.10.3p6.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

DELETE FROM engine4_core_menuitems WHERE name='sesandroidapp_admin_main_appsetup';
CREATE TABLE IF NOT EXISTS `engine4_sesandroapp_graphics` (
  `graphic_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `title_color` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FFFFFF',
  `description_color` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FFFFFF',
  `background_color` VARCHAR(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `file_id` int(11) DEFAULT '0',
  `order` int(11) DEFAULT '0',
  `creation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`graphic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
