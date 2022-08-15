/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.10.3p9-4.10.3p10.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

UPDATE `engine4_core_menuitems` SET `plugin` = 'Sesapi_Plugin_Menus::enableAndroidModule'
WHERE `name` = 'sesapi_admin_main_androidapp';