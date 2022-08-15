/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.9.4p2-4.9.4p3.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `device`, `is_delete`, `visibility`, `module_name`) VALUES ("Quotes", 'Quotes', 1, 1, 14, 0, '', 'core_main_sesquote', 1, 0, 0, 'sesquote'), ("Quotes", 'Quotes', 1, 1, 14, 0, '', 'core_main_sesquote', 2, 0, 0, 'sesquote');