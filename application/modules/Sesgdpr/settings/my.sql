/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: my.sql 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_sesgdpr', 'sesgdpr', 'SES - Professional GDPR', '', '{"route":"admin_default","module":"sesgdpr","controller":"settings"}', 'core_admin_main_plugins', '', 999),
('sesgdpr_admin_main_settings', 'sesgdpr', 'Global Settings', '', '{"route":"admin_default","module":"sesgdpr","controller":"settings"}', 'sesgdpr_admin_main', '', 1);
