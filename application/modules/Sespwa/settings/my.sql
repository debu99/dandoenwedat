/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: my.sql  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sespwa', 'sespwa', 'SES - Progressive Web App', '', '{"route":"admin_default","module":"sespwa","controller":"settings"}', 'core_admin_main_plugins', '', 999),
('sespwa_admin_main_settings', 'sespwa', 'Global Settings', '', '{"route":"admin_default","module":"sespwa","controller":"settings"}', 'sespwa_admin_main', '', 1);
