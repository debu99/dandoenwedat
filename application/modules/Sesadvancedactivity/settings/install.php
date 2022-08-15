<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Installer extends Engine_Package_Installer_Module {

//   public function onPreinstall() {
//
//     $db = $this->getDb();
//     $plugin_currentversion = '4.10.3p22';
//
//     //Check: Basic Required Plugin
//     $select = new Zend_Db_Select($db);
//     $select->from('engine4_core_modules')
//             ->where('name = ?', 'sesbasic');
//     $results = $select->query()->fetchObject();
//     if (empty($results)) {
//       return $this->_error('<div class="global_form"><div><div><p style="color:red;">The required SocialEngineSolutions Basic Required Plugin is not installed on your website. Please download the latest version of this FREE plugin from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>');
//     } else {
//       $error = include APPLICATION_PATH . "/application/modules/Sesbasic/controllers/checkPluginVersion.php";
//       if($error != '1') {
//         return $this->_error($error);
//       }
//     }
// // 		//Check latest version plugins
// // 		$getAllPluginVersion = $this->getAllPluginVersion();
// // 		if(!empty($getAllPluginVersion)) {
// //       return $this->_error($getAllPluginVersion);
// // 		}
//     parent::onPreinstall();
//   }

  public function onInstall() {

    $db = $this->getDb();

    //Upgrade Work
    $select = new Zend_Db_Select($db);
    $select
            ->from('engine4_core_modules')
            ->where('name = ?', 'sesadvancedactivity')
            ->where('version < ?', '4.10.3p8');
    $is_enabled = $select->query()->fetchObject();
    if (!empty($is_enabled)) {

        $table_exist_links = $db->query("SHOW TABLES LIKE 'engine4_sesadvancedactivity_links'")->fetch();
        if (empty($table_exist_links)) {
            $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_links` (
                `link_id` int(11) unsigned NOT NULL auto_increment,
                `core_link_id` int(11) NOT NULL,
                `ses_aaf_gif` TINYINT(1) NOT NULL DEFAULT "0",
                PRIMARY KEY  (`link_id`),
                UNIQUE( `core_link_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;');

            $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_links (`core_link_id`, `ses_aaf_gif`) SELECT `link_id`, `ses_aaf_gif` FROM engine4_core_links as t ON DUPLICATE KEY UPDATE core_link_id=t.link_id, ses_aaf_gif=t.ses_aaf_gif;');
        }

        $table_exist_details = $db->query("SHOW TABLES LIKE 'engine4_sesadvancedactivity_details'")->fetch();
        if (!empty($table_exist_details)) {

            $vote_up_count = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'vote_up_count'")->fetch();
            if (empty($vote_up_count)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `vote_up_count` INT(11) NOT NULL DEFAULT "0";');
            }

            $vote_down_count = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'vote_down_count'")->fetch();
            if (empty($vote_down_count)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `vote_down_count` INT(11) NOT NULL DEFAULT "0";');
            }

            $feedbg_id = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'feedbg_id'")->fetch();
            if (empty($feedbg_id)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `feedbg_id` INT(11) NOT NULL DEFAULT "0";');
            }

            $image_id = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'image_id'")->fetch();
            if (empty($image_id)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `image_id` INT(11) NOT NULL DEFAULT "0";');
            }

            $posting_type = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'posting_type'")->fetch();
            if (empty($posting_type)) {
              $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `posting_type` TINYINT(1) NOT NULL DEFAULT "0";');
              if($db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'posting_type'")->fetch()){
                $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_details (`action_id`, `posting_type`) SELECT `action_id`, `posting_type` FROM engine4_activity_actions as t ON DUPLICATE KEY UPDATE action_id=t.action_id, posting_type=t.posting_type;');
              }
            }

            $view_count = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'view_count'")->fetch();
            if (empty($view_count)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `view_count` INT UNSIGNED NOT NULL DEFAULT "0";');
            }

            $share_count = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_details LIKE 'share_count'")->fetch();
            if (empty($share_count)) {
                $db->query('ALTER TABLE `engine4_sesadvancedactivity_details` ADD `share_count` INT UNSIGNED NOT NULL DEFAULT "0";');
            }
        }

        //Advanced Comments Plugin
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules')
                ->where('name = ?', "sesadvancedcomment")
                ->where('enabled = ?', 1);
        $isModuleEnabled = $select->query()->fetchObject();
        if(!empty($isModuleEnabled)) {

            $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_actions'")->fetch();
            if (!empty($table_exist)) {
                $vote_up_count = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'vote_up_count'")->fetch();
                if (!empty($vote_up_count)) {
                    $db->query("ALTER TABLE `engine4_activity_actions` DROP `vote_up_count`;");
                }
                $vote_down_count = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'vote_down_count'")->fetch();
                if (!empty($vote_down_count)) {
                    $db->query("ALTER TABLE `engine4_activity_actions` DROP `vote_down_count`;");
                }
            }

            $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_activitylikes` (
                `activitylike_id` int(11) NOT NULL AUTO_INCREMENT,
                `activity_like_id` int(11) NOT NULL,
                `type` TINYINT(1) NOT NULL DEFAULT "1",
                PRIMARY KEY (`activitylike_id`),
                UNIQUE( `activity_like_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;');

            $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_likes'")->fetch();
            if (!empty($table_exist)) {
                $type_field = $db->query("SHOW COLUMNS FROM engine4_activity_likes LIKE 'type'")->fetch();
                if (!empty($type_field)) {
                    $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_activitylikes (`activity_like_id`, `type`) SELECT `like_id`, `type` FROM engine4_activity_likes as t ON DUPLICATE KEY UPDATE activity_like_id=t.like_id, type=t.type;");
                }
            }

            $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_corelikes` (
                `corelike_id` int(11) NOT NULL AUTO_INCREMENT,
                `core_like_id` int(11) NOT NULL,
                `type` TINYINT(1) NOT NULL DEFAULT "1",
                PRIMARY KEY (`corelike_id`),
                UNIQUE( `core_like_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;');

            $tablecorelike_exist = $db->query("SHOW TABLES LIKE 'engine4_core_likes'")->fetch();
            if (!empty($tablecorelike_exist)) {
                $typecore_field = $db->query("SHOW COLUMNS FROM engine4_core_likes LIKE 'type'")->fetch();
                if (!empty($typecore_field)) {
                    $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_corelikes (`core_like_id`, `type`) SELECT `like_id`, `type` FROM engine4_core_likes as t ON DUPLICATE KEY UPDATE core_like_id=t.like_id, type=t.type;");
                }
            }

            $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_activitycomments` (
            `activitycomment_id` int(11) NOT NULL AUTO_INCREMENT,
            `activity_comment_id` int(11) NOT NULL,
            `file_id` int(11) NOT NULL DEFAULT "0",
            `parent_id` int(11) NOT NULL DEFAULT "0",
            `gif_id` int(11) NOT NULL DEFAULT "0",
            `emoji_id` int(11) NOT NULL DEFAULT "0",
            `reply_count` int(11) NOT NULL DEFAULT "0",
            `preview` int(11) NOT NULL DEFAULT "0",
            `showpreview` tinyint(1) NOT NULL DEFAULT "0",
            `vote_up_count` int(11) NOT NULL DEFAULT "0",
            `vote_down_count` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`activitycomment_id`),
            UNIQUE( `activity_comment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

            $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_activitycomments (`activity_comment_id`, `file_id`, `parent_id`, `reply_count`, `preview`, `showpreview`, `vote_up_count`, `vote_down_count`) SELECT `comment_id`, `file_id`, `parent_id`, `reply_count`, `preview`, `showpreview`, `vote_up_count`, `vote_down_count` FROM engine4_activity_comments as t ON DUPLICATE KEY UPDATE activity_comment_id=t.comment_id, file_id=t.file_id, parent_id=t.parent_id, reply_count=t.reply_count, preview=t.preview, showpreview=t.showpreview, vote_up_count=t.vote_up_count, vote_down_count=t.vote_down_count;");

            $table_exist = $db->query("SHOW TABLES LIKE 'engine4_activity_comments'")->fetch();
            if (!empty($table_exist)) {
                $file_id = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'file_id'")->fetch();
                if (!empty($file_id)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `file_id`;");
                }
                $parent_id = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'parent_id'")->fetch();
                if (!empty($parent_id)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `parent_id`;");
                }
                $reply_count = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'reply_count'")->fetch();
                if (!empty($reply_count)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `reply_count`;");
                }
                $preview = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'preview'")->fetch();
                if (!empty($preview)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `preview`;");
                }
                $showpreview = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'showpreview'")->fetch();
                if (!empty($showpreview)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `showpreview`;");
                }
                $vote_up_count = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'vote_up_count'")->fetch();
                if (!empty($vote_up_count)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `vote_up_count`;");
                }
                $vote_down_count = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'vote_down_count'")->fetch();
                if (!empty($vote_down_count)) {
                    $db->query("ALTER TABLE `engine4_activity_comments` DROP `vote_down_count`;");
                }
            }

            $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesadvancedactivity_corecomments` (
            `corecomment_id` int(11) NOT NULL AUTO_INCREMENT,
            `core_comment_id` int(11) NOT NULL,
            `file_id` int(11) NOT NULL DEFAULT "0",
            `parent_id` int(11) NOT NULL DEFAULT "0",
            `emoji_id` int(11) NOT NULL DEFAULT "0",
            `reply_count` int(11) NOT NULL DEFAULT "0",
            `preview` int(11) NOT NULL DEFAULT "0",
            `showpreview` tinyint(1) NOT NULL DEFAULT "0",
            `gif_id` int(11) NOT NULL DEFAULT "0",
            `vote_up_count` int(11) NOT NULL DEFAULT "0",
            `vote_down_count` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`corecomment_id`),
            UNIQUE( `core_comment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

            $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_corecomments (`core_comment_id`, `file_id`, `parent_id`, `reply_count`, `preview`, `showpreview`, `vote_up_count`, `vote_down_count`) SELECT `comment_id`, `file_id`, `parent_id`, `reply_count`, `preview`, `showpreview`, `vote_up_count`, `vote_down_count` FROM engine4_core_comments as t ON DUPLICATE KEY UPDATE core_comment_id=t.comment_id, file_id=t.file_id, parent_id=t.parent_id, reply_count=t.reply_count, preview=t.preview, showpreview=t.showpreview, vote_up_count=t.vote_up_count, vote_down_count=t.vote_down_count;");

            $table_exist = $db->query("SHOW TABLES LIKE 'engine4_core_comments'")->fetch();
            if (!empty($table_exist)) {
                $file_id = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'file_id'")->fetch();
                if (!empty($file_id)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `file_id`;");
                }
                $parent_id = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'parent_id'")->fetch();
                if (!empty($parent_id)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `parent_id`;");
                }
                $reply_count = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'reply_count'")->fetch();
                if (!empty($reply_count)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `reply_count`;");
                }
                $preview = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'preview'")->fetch();
                if (!empty($preview)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `preview`;");
                }
                $showpreview = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'showpreview'")->fetch();
                if (!empty($showpreview)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `showpreview`;");
                }
                $vote_up_count = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'vote_up_count'")->fetch();
                if (!empty($vote_up_count)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `vote_up_count`;");
                }
                $vote_down_count = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'vote_down_count'")->fetch();
                if (!empty($vote_down_count)) {
                    $db->query("ALTER TABLE `engine4_core_comments` DROP `vote_down_count`;");
                }
            }
        }


        //Feed background plugin
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules')
                ->where('name = ?', "sesfeedbg")
                ->where('enabled = ?', 1);
        $isModuleEnabled = $select->query()->fetchObject();
        if(!empty($isModuleEnabled)) {
            $feedbg_id = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'feedbg_id'")->fetch();
            if (!empty($feedbg_id)) {
                $db->query("ALTER TABLE `engine4_activity_actions` DROP `feedbg_id`;");
            }
        }

        //Feed gif plugin
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules')
                ->where('name = ?', "sesfeedgif")
                ->where('enabled = ?', 1);
        $isModuleEnabled = $select->query()->fetchObject();
        if(!empty($isModuleEnabled)) {

            $image_id = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'image_id'")->fetch();
            if (!empty($image_id)) {

                $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_details (`action_id`, `image_id`) SELECT `action_id`, `image_id` FROM engine4_activity_actions as t ON DUPLICATE KEY UPDATE action_id=t.action_id, image_id=t.image_id;");

                $db->query("ALTER TABLE `engine4_activity_actions` DROP `image_id`;");
            }

            $gif_id = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'gif_id'")->fetch();
            if (!empty($gif_id)) {
              $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_activitycomments (`activity_comment_id`, `gif_id`) SELECT `comment_id`, `gif_id` FROM engine4_activity_comments as t ON DUPLICATE KEY UPDATE activity_comment_id=t.comment_id, gif_id=t.gif_id;");

              $db->query("ALTER TABLE `engine4_activity_comments` DROP `gif_id`;");
            }

            $gif_id = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'gif_id'")->fetch();
            if (!empty($gif_id)) {
              $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_corecomments (`core_comment_id`, `gif_id`) SELECT `comment_id`, `gif_id` FROM engine4_core_comments as t ON DUPLICATE KEY UPDATE core_comment_id=t.comment_id, gif_id=t.gif_id;");

              $db->query("ALTER TABLE `engine4_core_comments` DROP `gif_id`;");
            }
        }

        //Emoji plugin
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules')
                ->where('name = ?', "sesemoji")
                ->where('enabled = ?', 1);
        $isModuleEnabled = $select->query()->fetchObject();
        if(!empty($isModuleEnabled)) {

            $emoji_id = $db->query("SHOW COLUMNS FROM engine4_activity_comments LIKE 'emoji_id'")->fetch();
            if (!empty($emoji_id)) {
                $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_activitycomments (`activity_comment_id`, `emoji_id`) SELECT `comment_id`, `emoji_id` FROM engine4_activity_comments as t ON DUPLICATE KEY UPDATE activity_comment_id=t.comment_id, emoji_id=t.emoji_id;");

                $db->query("ALTER TABLE `engine4_activity_comments` DROP `emoji_id`;");
            }

            $emoji_id = $db->query("SHOW COLUMNS FROM engine4_core_comments LIKE 'emoji_id'")->fetch();
            if (!empty($emoji_id)) {
                $db->query("INSERT IGNORE INTO engine4_sesadvancedactivity_corecomments (`core_comment_id`, `emoji_id`) SELECT `comment_id`, `emoji_id` FROM engine4_core_comments as t ON DUPLICATE KEY UPDATE core_comment_id=t.comment_id, emoji_id=t.emoji_id;");

                $db->query("ALTER TABLE `engine4_core_comments` DROP `emoji_id`;");
            }
        }

        //Rest Api plugin
        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_modules')
                ->where('name = ?', "sesapi")
                ->where('enabled = ?', 1);
        $isModuleEnabled = $select->query()->fetchObject();
        if(!empty($isModuleEnabled)) {
            $posting_type = $db->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'posting_type'")->fetch();
            if (!empty($posting_type)) {
                $db->query("ALTER TABLE `engine4_activity_actions` DROP `posting_type`;");
            }
        }

        $db->query('UPDATE `engine4_core_menuitems` SET `label` = "SNS: Professional Activity & Nested Comments Plugin" WHERE `engine4_core_menuitems`.`name` = "core_admin_main_settings_sesadvancedactivity";');
    }

    $table_exist = $db->query("SHOW TABLES LIKE 'engine4_sesfeelingactivity_feelings'")->fetch();
    if (!empty($table_exist)) {

      $enabled_field = $db->query("SHOW COLUMNS FROM engine4_sesfeelingactivity_feelings LIKE 'enabled'")->fetch();
      if (empty($enabled_field)) {
        $db->query('ALTER TABLE `engine4_sesfeelingactivity_feelings` ADD `enabled` TINYINT(1) NOT NULL DEFAULT "1";');
      }
    }

    $tablepo_exist = $db->query("SHOW TABLES LIKE 'engine4_sesadvancedactivity_feelingposts'")->fetch();
    if (!empty($tablepo_exist)) {

      $feeling_custom_field = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_feelingposts LIKE 'feeling_custom'")->fetch();
      if (empty($feeling_custom_field)) {
        $db->query('ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_custom` TINYINT(1) NOT NULL DEFAULT "0";');
      }

      $feeling_customtext_field = $db->query("SHOW COLUMNS FROM engine4_sesadvancedactivity_feelingposts LIKE 'feeling_customtext'")->fetch();
      if (empty($feeling_customtext_field)) {
        $db->query('ALTER TABLE `engine4_sesadvancedactivity_feelingposts` ADD `feeling_customtext` VARCHAR(255) NULL;');
      }
    }

    parent::onInstall();
  }

  function onEnable() {

    $db = $this->getDb();

    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "1" WHERE `engine4_core_modules`.`name` = "sesadvancedcomment";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "1" WHERE `engine4_core_modules`.`name` = "sesfeedbg";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "1" WHERE `engine4_core_modules`.`name` = "sesfeedgif";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "1" WHERE `engine4_core_modules`.`name` = "sesfeelingactivity";');

    $db->query("UPDATE `engine4_core_content` SET `name` = 'sesadvancedactivity.feed' WHERE `engine4_core_content`.`name` = 'activity.feed';");

     $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_activitylikes (`activity_like_id`) SELECT `like_id` FROM engine4_activity_likes as t ON DUPLICATE KEY UPDATE activity_like_id=t.like_id;');
    $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_corelikes (`core_like_id`) SELECT `like_id` FROM engine4_core_likes as t ON DUPLICATE KEY UPDATE core_like_id=t.like_id');
    $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_activitycomments (`activity_comment_id`) SELECT `comment_id` FROM engine4_activity_comments as t ON DUPLICATE KEY UPDATE activity_comment_id=t.comment_id;');
    $db->query('INSERT IGNORE INTO engine4_sesadvancedactivity_corecomments (`core_comment_id`) SELECT `comment_id` FROM engine4_core_comments as t ON DUPLICATE KEY UPDATE core_comment_id=t.comment_id;');
    
    parent::onEnable();
  }

  public function onDisable() {

    $db = $this->getDb();

    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "0" WHERE `engine4_core_modules`.`name` = "sesadvancedcomment";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "0" WHERE `engine4_core_modules`.`name` = "sesfeedbg";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "0" WHERE `engine4_core_modules`.`name` = "sesfeedgif";');
    $db->query('UPDATE `engine4_core_modules` SET `enabled` = "0" WHERE `engine4_core_modules`.`name` = "sesfeelingactivity";');
    $db->query("UPDATE `engine4_core_content` SET `name` = 'activity.feed' WHERE `engine4_core_content`.`name` = 'sesadvancedactivity.feed';");

    parent::onDisable();
  }

  //Funcation for check versions
  private function checkpluginversion($pluginVersion, $plugin_currentversion) {

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
    return $finalVersion;
  }

  //Funcation for check depandencey plugin
  private function getAllPluginVersion() {

    $db = $this->getDb();
    $baseURL = Zend_Controller_Front::getInstance()->getBaseUrl();
    $pluginArrays = array(
      'sesvideo' => '4.8.13p1',
      'sesalbum' => '4.8.13',
    );
    $sespluginupgrademessage = '';
    foreach ($pluginArrays as $key=>$pluginArray) {
      $modulesExist = $db->query("SELECT * FROM  `engine4_core_modules` WHERE  `name` LIKE  '".$key."'")->fetch();
      if (!empty($modulesExist) && !empty($modulesExist['version'])) {
        $modulesExistSES = $this->checkpluginversion($modulesExist['version'], $pluginArray);
        if (empty($modulesExistSES)) {
          $sespluginupgrademessage .= '<div><span style="border-radius: 3px;border: 2px solid #cd4545;background-color: #da5252;padding: 10px;display: block;margin-bottom: 15px;"><p style="color:#fff;font-weight:bold;">Note: Your website does not have the latest version of "' . $modulesExist['title'] . '". Please upgrade "' . $modulesExist['title'] . '" on your website to the latest version available in your SocialEngineSolutions Client Area to enable its integration with "Professional Activity & Nested Comments Plugin". Please <a href="' . $baseURL . '/manage" style="color:#fff;text-decoration:underline;font-weight:bold;">Click here</a> to go Manage Packages.</p></span></div>';
        }
      }
    }
    return $sespluginupgrademessage;
  }
}
