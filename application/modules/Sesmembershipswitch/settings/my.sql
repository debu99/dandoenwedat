/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: my.sql  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_plugins_sesmembershipswitch', 'sesmembershipswitch', 'SES - Auto Switching...', '', '{"route":"admin_default","module":"sesmembershipswitch","controller":"settings","action":"index"}', 'core_admin_main_plugins', '', 999),
('sesmembershipswitch_admin_main_settings', 'sesmembershipswitch', 'Global Settings', '', '{"route":"admin_default","module":"sesmembershipswitch","controller":"settings"}', 'sesmembershipswitch_admin_main', '', 1);
