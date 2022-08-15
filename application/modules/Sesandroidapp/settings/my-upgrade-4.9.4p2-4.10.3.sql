 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: my-upgrade-4.9.4p2-4.10.3.sql 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

ALTER TABLE `engine4_sesandroidapp_slides` ADD `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0=>photo/1=>video';
ALTER TABLE `engine4_sesandroidapp_slides` ADD `video_id` INT(11) NOT NULL DEFAULT '0';