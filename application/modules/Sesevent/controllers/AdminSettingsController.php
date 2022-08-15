<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_settings');
    
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_settings', array(), 'sesevent_admin_main_subgloablsetting');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->form = $form = new Sesevent_Form_Admin_Global();
    
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) {
	    if ($form->sesevent_defaultcurrency) {
	      $fullySupportedCurrencies = Engine_Api::_()->sesevent()->getSupportedCurrency();
	      $form->sesevent_defaultcurrency->setMultiOptions($fullySupportedCurrencies);
	      $form->sesevent_defaultcurrency->setValue(Engine_Api::_()->sesevent()->defaultCurrency());
	    }
			
			if (isset($_POST['sesevent_changelanding']) && $_POST['sesevent_changelanding'] == 1) {
				$db = Engine_Db_Table::getDefaultAdapter();
				$db->query("DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`page_id` = 3");
				$page_id = 3;
				// Insert main
				$db->insert('engine4_core_content', array(
					'type' => 'container',
					'name' => 'main',
					'page_id' => $page_id,
					'order' => 2,
				));
				$main_id = $db->lastInsertId();
				
				
				// Insert main-middle
				$db->insert('engine4_core_content', array(
					'type' => 'container',
					'name' => 'middle',
					'page_id' => $page_id,
					'parent_content_id' => $main_id,
					'order' => 2,
				));
				$main_middle_id = $db->lastInsertId();
				
			  // Insert content
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.main-slideshows',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 3,
					'params' => '{"infoshow":["searchForVenue","findVenue","getStarted"],"sfvtextcolor":"#FFFFFF","sfvbtncolor":"#FF4C4C","fvbtextcolor":"#FFFFFF","fvbbtncolor":"#FF4C4C","gsttextcolor":"#FFFFFF","gstbgcolor":"#48B3B6","getStartedLink":"1","percentageWidth":"90","titleS":"Create, promote, manage, and host","titlecolor":"#FFFFFF","descriptionS":"Your meetings, conferences & special events, etc.","descriptioncolor":"#FFFFFF","margin_top":"-21px","height":"420","animationSpeed":"3000","navigation":"1","isfullwidth":"1","title":"","nomobile":"0","name":"sesevent.main-slideshows"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';

				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<h2 style="font-size: 34px;margin: 30px 0 10px;text-align: center;">Featured Upcoming Events</h2>';
				}
 
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 4,
					'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.featured-sponsored',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 5,
					'params' => '{"viewType":"gridInside","order":"ongoingSPupcomming","criteria":"1","info":"most_liked","show_criteria":["title","location","socialSharing","likeButton","favouriteButton","listButton","buy"],"grid_title_truncation":"45","list_title_truncation":"45","height":"190","width":"284","limit_data":"4","title":"","nomobile":"0","name":"sesevent.featured-sponsored"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';
 
				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;margin-top:10px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'upcoming\');"  class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">See All Upcoming Events</a></div> 
					
					<h2 style="font-size: 34px;margin-bottom: 0;text-align: center;padding-top: 30px;border-top: 1px solid #cdcdcd;">Browse Events by Top Categories</h2>"';
				}
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 6,
					'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.event-category-icons',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 7,
					'params' => '{"titleC":"Browse by Top Categories","height":"200","width":"175","alignContent":"center","criteria":"most_event","show_criteria":["title","countEvents"],"limit_data":"12","title":"","nomobile":"0","name":"sesevent.event-category-icons"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';
  
				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;margin-top:-50px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'categories\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Categories</a></div> 
					
					<h2 style="font-size: 34px;margin-bottom: -10px;margin-top: 10px;border-top-width: 1px;text-align: center;padding-top: 30px;border-color: #cdcdcd;">Popular Events</h2>';
				}  
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 8,
				 'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.tabbed-events',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 9,
					'params' => '{"enableTabs":["grid"],"openViewType":"grid","tabOption":"vertical","show_item_count":"0","show_criteria":["favouriteButton","listButton","likeButton","socialSharing","like","location","comment","favourite","buy","rating","view","title","startenddate","category"],"limit_data":"3","show_limited_data":"no","pagging":"pagging","grid_title_truncation":"35","advgrid_title_truncation":"45","list_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"45","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"160","width":"140","photo_height":"225","photo_width":"312","info_height":"130","advgrid_width":"344","advgrid_height":"222","pinboard_width":"250","masonry_height":"250","search_type":["ongoingSPupcomming","week","weekend","month","mostSPjoined","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","featured","sponsored","verified"],"ongoingSPupcomming_order":"1","ongoingSPupcomming_label":"Upcoming & Ongoing","upcoming_order":"2","upcoming_label":"Upcoming","ongoing_order":"3","ongoing_label":"Ongoing","past_order":"4","past_label":"Past","week_order":"5","week_label":"This Week","weekend_order":"6","weekend_label":"This Weekend","month_order":"7","month_label":"This Month","mostSPjoined_order":"8","mostSPjoined_label":"Most Joined Events","recentlySPcreated_order":"9","recentlySPcreated_label":"Recently Created","mostSPviewed_order":"10","mostSPviewed_label":"Most Viewed","mostSPliked_order":"11","mostSPliked_label":"Most Liked","mostSPcommented_order":"12","mostSPcommented_label":"Most Commented","mostSPrated_order":"13","mostSPrated_label":"Most Rated","mostSPfavourite_order":"14","mostSPfavourite_label":"Most Favourite","featured_order":"15","featured_label":"Featured","sponsored_order":"16","sponsored_label":"Sponsored","verified_order":"17","verified_label":"Verified","title":"","nomobile":"0","name":"sesevent.tabbed-events"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';
  
				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<div style="text-align: center;margin-bottom: 30px;margin-top:10px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'browse\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Events</a></div>';
				}
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 10,
					'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'seshtmlbackground.paralex-video',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 11,
					'params' => '{"bannervideo":"public\/admin\/GLASS_BACKGROUND_CLUB_CLIPCHAMP_720p.mp4","paralextitle":"<p style=\"text-align: center; margin-top: -25px; margin-bottom: 20px;\"><span style=\"font-size: 20px; font-weight: bold; letter-spacing: 5px;\"><span class=\"h2\" style=\"color: #ff4c4c;\">Largest Event Listing Platform<\/span><br><\/span><\/p>\r\n<h2 style=\"font-size: 35px; font-weight: normal; margin-bottom: 10px;\">Discover events near you and around your city and much more...<\/h2>\r\n<div style=\"width: 10%; display: inline-block; float: none; margin-bottom: 30px; border-bottom: 4px solid #FF4C4C;\">&nbsp;<\/div>\r\n<ul style=\"vertical-align: top;\">\r\n<li style=\"display: inline-block; width: 30%; padding: 0px 30px; vertical-align: top;\"><img src=\"http:\/\/demo.socialenginesolutions.com\/public\/sesWysiwygPhotos\/joinevent.png\" alt=\"\"><br><span style=\"display: block; margin: 10px 0px; font-size: 25px; font-weight: bold;\">JOIN EVENTS<\/span>\r\n<p style=\"font-size: 15px;\">Plan your weekends and involve your friends with our Social Integrations.<\/p>\r\n<\/li>\r\n<li style=\"display: inline-block; width: 30%; padding: 0px 30px; vertical-align: top;\"><img src=\"http:\/\/demo.socialenginesolutions.com\/public\/sesWysiwygPhotos\/ticketicon.png\" alt=\"\"><br><span style=\"display: block; margin: 10px 0px; font-size: 25px; font-weight: bold;\">BUY TICKETS<\/span>\r\n<p style=\"font-size: 15px;\">Purchase tickets of your favorite Online Events without waiting in queue.<\/p>\r\n<\/li>\r\n<\/ul>","height":"500","title":"","nomobile":"0","name":"seshtmlbackground.paralex-video"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';

				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<h2 style="font-size: 34px;margin-bottom: -10px;text-align: center;margin-top:30px;">Browse Events by Countries</h2>"';
				}
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 12,
					'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.country-tabbed-events',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 13,
					'params' => '{"enableTabs":["advgrid"],"openViewType":"advgrid","tabOption":"default","show_criteria":["favouriteButton","likeButton","joinedcount","socialSharing","listButton","like","location","comment","favourite","buy","rating","view","title","startenddate","category"],"limit_data":"4","show_item_count":"0","show_limited_data":"yes","pagging":"button","advgrid_title_truncation":"20","grid_title_truncation":"45","list_title_truncation":"45","pinboard_title_truncation":"45","masonry_title_truncation":"45","list_description_truncation":"45","masonry_description_truncation":"45","grid_description_truncation":"45","pinboard_description_truncation":"45","height":"160","width":"140","photo_height":"160","photo_width":"250","advgrid_width":"284","advgrid_height":"355","info_height":"160","pinboard_width":"250","masonry_height":"250","country":["United Kingdom","United States"],"criteria":"ongoingSPupcomming","title":"","nomobile":"0","name":"sesevent.country-tabbed-events"}',
				));
				
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';

				foreach ($languageList as $key => $language) {
					if ($language == 'en')
						$coulmnName = 'bodysimple';
					else
						$coulmnName = $language . '_bodysimple';
						$array[$coulmnName] = '<div style="text-align: center;margin-bottom:50px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'locations\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Events in All Locations</a></div> 
					 
					 <h2 style="font-size: 34px;margin-bottom: -10px;margin-top: 10px;border-top-width: 1px;text-align: center;padding-top: 30px;border-color: #cdcdcd;">Popular Hosts</h2>';
				}
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 14,
					'params' => json_encode($array),
				));
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesevent.featured-sponsored-host',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 15,
					'params' => '{"viewType":"grid","criteria":"5","info":"most_viewed","show_criteria":["view","favourite","hostEventCount","host","socialSharing","favouriteButton"],"grid_title_truncation":"45","list_title_truncation":"45","height":"150","width":"158","limit_data":"7","contentInsideOutside":"in","mouseOver":"1","title":"","nomobile":"0","name":"sesevent.featured-sponsored-host"}',
				));
  
				$array['show_content'] = 1;
				$array['title'] = '';
				$array['nomobile'] = 0;
				$array['name'] = 'sesbasic.simple-html-block';
  
				foreach ($languageList as $key => $language) {
					if ($language == 'en')
					$coulmnName = 'bodysimple';
					else
					$coulmnName = $language . '_bodysimple';
					$array[$coulmnName] = '<div style="text-align: center;margin-bottom:20px;"><a href="javascript:void(0);" onclick="chnageManifestUrl(\'browse-host\');" class="sesbasic_animation" onmouseover="this.style.backgroundColor=\'#29A6AA\'" onmouseout="this.style.backgroundColor=\'#FF4C4C\'" style="padding: 10px 20px; font-size: 17px; color: rgb(255, 255, 255); font-weight: bold; text-decoration: none; border-radius: 3px; background-color: rgb(255, 76, 76);">Browse All Hosts</a></div>';
				}
  
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'sesbasic.simple-html-block',
					'page_id' => $page_id,
					'parent_content_id' => $main_middle_id,
					'order' => 16,
				 'params' => json_encode($array),
				));
			}
			//Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent_changelanding', 1);
    }
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
	      include_once APPLICATION_PATH . "/application/modules/Sesevent/controllers/License.php";
	      $db = Engine_Db_Table::getDefaultAdapter();
	      $values = $form->getValues();
	      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) {
		      foreach ($values as $key => $value) {
				if(Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
				if(!$value && strlen($value) == 0)
				continue;
		        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
		      }
			    $form->addNotice('Your changes have been saved.');
			    if($error)
		      $this->_helper->redirector->gotoRoute(array());
		    }
    }
  }
  // for default installation
  function setCategoryPhoto($file, $cat_id, $resize = false) {
    $fileName = $file;
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sesevent_category',
        'parent_id' => $cat_id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $name,
    );

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    if ($resize) {
      // Resize image (main)
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_poster.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(800, 800)
              ->write($mainPath)
              ->destroy();

      // Resize image (normal) make same image for activity feed so it open in pop up with out jump effect.
      $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_thumb.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(500, 500)
              ->write($normalPath)
              ->destroy();
    } else {
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_poster.' . $extension;
      copy($file, $mainPath);
    }
    if ($resize) {
      // normal main  image resize
      $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_icon.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(100, 100)
              ->write($normalMainPath)
              ->destroy();
    } else {
      $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_icon.' . $extension;
      copy($file, $normalMainPath);
    }
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      if ($resize) {
        $iIconNormal = $filesTable->createFile($normalPath, $params);
        $iMain->bridge($iIconNormal, 'thumb.thumb');
      }
      $iNormalMain = $filesTable->createFile($normalMainPath, $params);
      $iMain->bridge($iNormalMain, 'thumb.icon');
    } catch (Exception $e) {
      die;
      // Remove temp files
      @unlink($mainPath);
      if ($resize) {
        @unlink($normalPath);
      }
      @unlink($normalMainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Sesevent_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    if ($resize) {
      @unlink($normalPath);
    }
    @unlink($normalMainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
  public function eventcreateAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_settings');
    
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_settings', array(), 'sesevent_admin_main_subeventcreate');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->form = $form = new Sesevent_Form_Admin_EventCreatePageSettings();
 
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      $db = Engine_Db_Table::getDefaultAdapter();
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
	    $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }

  public function currencyAction() {
    $this->_redirect('admin/sesbasic/settings/currency');
  }

  public function editCurrencyAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->currency_symbol = $currency_symbol = $id;
    $this->view->form = $form = new Sesevent_Form_Admin_Settings_EditCurrency();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $getSetting = $settings->getSetting('sesevent.' . $currency_symbol);
    $form->getElement('currency_rate')->setValue($getSetting);
    $form->getElement('currency_symbol')->setValue($id);
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $settings->setSetting('sesevent.' . $_POST['currency_symbol'], $_POST['currency_rate']);
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully edit currency.'))
      ));
    }
  }
  public function levelAction(){
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_level');
    // Get level id
    if (null !== ($id = $this->_getParam('id'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }
    if (!$level instanceof Authorization_Model_Level) {
      throw new Engine_Exception('missing level');
    }
    $level_id = $id = $level->level_id;
    // Make form
    $this->view->form = $form = new Sesevent_Form_Admin_Settings_Level(array(
        'public' => ( in_array($level->type, array('public')) ),
        'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($level_id);
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
		$valuesForm = $permissionsTable->getAllowed('sesevent_event', $level_id, array_keys($form->getValues()));
		/*if(is_array($valuesForm['sponcommi_value'])){
			$valuesForm['sponcommi_value']	 = $valuesForm['sponcommi_value'][0];
		}
		if(is_array($valuesForm['event_sponsothre'])){
			$valuesForm['sesevent_sponsorship_threshold_amount']	 = $valuesForm['sesevent_sponsorship_threshold_amount'][0];
		}
		if(is_array($valuesForm['event_threshold'])){
			$valuesForm['event_threshold']	 = $valuesForm['event_threshold'][0];
		}
		if(is_array($valuesForm['event_commission'])){
			$valuesForm['event_commission']	 = $valuesForm['event_commission'][0];
		}
		if(is_array($valuesForm['addlist_maxevent'])){
			$valuesForm['addlist_maxevent']	 = $valuesForm['addlist_maxevent'][0];
		}*/
    $form->populate($valuesForm);
    if (!$this->getRequest()->isPost()) {
      return;
    }
    // Check validitiy
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $values = $form->getValues();
    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();
    try {
      if ($level->type != 'public') {
        // Set permissions
        $values['auth_comment'] = (array) $values['auth_comment'];
        $values['auth_photo'] = (array) $values['auth_photo'];
        $values['auth_view'] = (array) $values['auth_view'];
      }
			/*if(isset($values['sponcommi_value'])){
				$valueSponsorComm = $values['sponcommi_value'];
				$values['sponcommi_value'] = array($valueSponsorComm);
			}
			if(isset($values['event_sponsothre'])){
				$valueSponsorComm = $values['event_sponsothre'];
				$values['event_sponsothre'] = array($valueSponsorComm);
			}
			if(isset($values['event_threshold'])){
				$valueSponsorComm = $values['event_threshold'];
				$values['event_threshold'] = array($valueSponsorComm);
			}
			if(isset($values['event_commission'])){
				$valueSponsorComm = $values['event_commission'];
				$values['event_commission'] = array($valueSponsorComm);
			}
			if(isset($values['addlist_maxevent'])){
				$valueSponsorComm = $values['addlist_maxevent'];
				$values['addlist_maxevent'] = array($valueSponsorComm);
			}*/
      $permissionsTable->setAllowed('sesevent_event', $level_id, $values);
      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }
  public function manageDashboardsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managedashboards');

    $this->view->storage = Engine_Api::_()->storage();
    $this->view->paginator = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems();
  }
  
  
  //Enable Action
  public function enabledAction() {

    $id = $this->_getParam('dashboard_id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesevent_dashboards', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect('admin/sesevent/settings/manage-dashboards');
  }
  
  public function editDashboardsSettingsAction() {

    $dashboards = Engine_Api::_()->getItem('sesevent_dashboards', $this->_getParam('dashboard_id'));
    $this->_helper->layout->setLayout('admin-simple');
    $form = $this->view->form = new Sesevent_Form_Admin_EditDashboard();
    $form->setTitle('Edit This Item');
    $form->button->setLabel('Save Changes');

    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    if (!($id = $this->_getParam('dashboard_id')))
      throw new Zend_Exception('No identifier specified');

    $form->populate($dashboards->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $dashboards->title = $values["title"];
        $dashboards->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array('You have successfully edit entry.')
      ));
      $this->_redirect('admin/sesevent/settings/manage-dashboards');
    }
  }
  
  public function extensionAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_seseventtickets');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventticket_admin_main', array(), 'seseventticket_admin_main_settings');
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')){
    $this->view->form = $form = new Seseventticket_Form_Admin_Global();
	    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
	      $values = $form->getValues();
	      include_once APPLICATION_PATH . "/application/modules/Seseventticket/controllers/License.php";
	      $db = Engine_Db_Table::getDefaultAdapter();
	      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
		      foreach ($values as $key => $value) {
						if($key == 'sesevent_ticket_service_tax' || $key == 'sesevent_ticket_entertainment_tax') {
                            if(Engine_Api::_()->getApi('settings', 'core')->hasSetting($key))
						    Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
                        }
						if(!$value) {
                            continue;
                        }
		        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
		      }
			    $form->addNotice('Your changes have been saved.');
			    if($error)
		      $this->_helper->redirector->gotoRoute(array());
		    }
	    }
    }
  }
  
  public function eventsponsorshipAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_extension');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_extension', array(), 'sesevent_admin_main_subeventsponsorship');
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship')){
    $this->view->form = $form = new Seseventsponsorship_Form_Admin_Global();
	    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
	      $values = $form->getValues();
	      include_once APPLICATION_PATH . "/application/modules/Seseventsponsorship/controllers/License.php";
	      $db = Engine_Db_Table::getDefaultAdapter();
	      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')) {
		      foreach ($values as $key => $value) {
		        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
		      }
			    $form->addNotice('Your changes have been saved.');
		      $this->_helper->redirector->gotoRoute(array());
		    }
	    }
    }
  }
  
  public function eventvideoAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_extension');
    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main_extension', array(), 'sesevent_admin_main_subeventvideo');
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')){
	    $this->view->form = $form = new Seseventvideo_Form_Admin_Settings_GlobalLicense();
	    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
	      $values = $form->getValues();
	      include_once APPLICATION_PATH . "/application/modules/Seseventvideo/controllers/License.php";
	      $db = Engine_Db_Table::getDefaultAdapter();
	      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventvideo.pluginactivated')) {
		      foreach ($values as $key => $value) {
		        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
		      }
			    $form->addNotice('Your changes have been saved.');
		      $this->_helper->redirector->gotoRoute(array());
		    }
	    }
    }
  }

  public function manageWidgetizePageAction() {

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_managewidgetizepage');

    $pagesArray = array('sesevent_index_welcome', 'sesevent_index_home', 'sesevent_index_browse', 'sesevent_index_upcoming', 'sesevent_index_past', 'sesevent_list_browse', 'sesevent_index_tags', 'sesevent_index_manage', 'sesevent_category_browse', 'sesevent_index_locations', 'sesevent_index_calender', 'sesevent_index_browse-host', 'seseventreview_index_view', 'sesevent_photo_view', 'sesevent_list_view', 'sesevent_album_view', 'sesevent_index_viewhost', 'sesevent_category_index', 'sesevent_index_create');

    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')) {
	    $pagesArray = array_merge(array('sesevent_ticket_my-tickets', 'sesevent_ticket_buy'), $pagesArray);
    }
    
//     if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship')) {
// 	    $pagesArray = array_merge(array('sesevent_sponsorship_view-sponsorship'), $pagesArray);
//     }
    
    $this->view->pagesArray = $pagesArray;
  }
	
	//site statis for sesevent plugin 
  public function statisticAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_statistics');

    $eventTable = Engine_Api::_()->getDbtable('events', 'sesevent');
    $eventTableName = $eventTable->info('name');
    
    $eventPhotoTable = Engine_Api::_()->getDbtable('photos', 'sesevent');
    $eventPhotoTableName = $eventPhotoTable->info('name');
    
    $eventAlbumTable = Engine_Api::_()->getDbtable('albums', 'sesevent');
    $eventAlbumTableName = $eventAlbumTable->info('name');

    //Total events
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalevent');
    $this->view->totalevent = $select->query()->fetchColumn();
    
    //Total approved event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalapprovedevent')->where('is_approved =?', 1);
    $this->view->totalapprovedevent = $select->query()->fetchColumn();
    
    //Total verified event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalverified')->where('verified =?', 1);
    $this->view->totaleventverified = $select->query()->fetchColumn();

    //Total featured event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalfeatured')->where('featured =?', 1);
    $this->view->totaleventfeatured = $select->query()->fetchColumn();

    //Total sponsored event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalsponsored')->where('sponsored =?', 1);
    $this->view->totaleventsponsored = $select->query()->fetchColumn();
    
    //Total albums of event
    $select = $eventAlbumTable->select()->from($eventAlbumTableName, 'count(*) AS totalalbums');
    $this->view->totalalbums = $select->query()->fetchColumn();
    
    //Total photos of event
    $select = $eventPhotoTable->select()->from($eventPhotoTableName, 'count(*) AS totalphotos');
    $this->view->totalphotos = $select->query()->fetchColumn();
    
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')) {
			$videoTable = Engine_Api::_()->getDbtable('videos', 'seseventvideo');
			$videoTableName = $videoTable->info('name');
			//Total videos of event
			$select = $videoTable->select()->from($videoTableName, 'count(*) AS totalvideos');
			$this->view->totalvideos = $select->query()->fetchColumn();
    }

    //Total favourite event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalfavourite')->where('favourite_count <>?', 0);
    $this->view->totaleventfavourite = $select->query()->fetchColumn();
    
    //Total comments event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalcomment')->where('comment_count <>?', 0);
    $this->view->totaleventcomments = $select->query()->fetchColumn();
    
     //Total view event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalview')->where('view_count <>?', 0);
    $this->view->totaleventviews = $select->query()->fetchColumn();
    
     //Total like event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totallike')->where('like_count <>?', 0);
    $this->view->totaleventlikes = $select->query()->fetchColumn();

    //Total rated event
    $select = $eventTable->select()->from($eventTableName, 'count(*) AS totalrated')->where('rating <>?', 0);
    $this->view->totaleventrated = $select->query()->fetchColumn();
  }
	public function serviceTaxAction(){
		 // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
		$data=$this->_getParam('data',false);		
		$action=$this->_getParam('actionA',false);
		$getServiceTaxValues = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.service.tax.values','');
		if($getServiceTaxValues)
			$getServiceTaxValues = explode(',',$getServiceTaxValues);
		else
			$getServiceTaxValues = array();
      
		if($action == 'create'){
			 $this->view->form = $form = new Sesevent_Form_Admin_Addservicetax();
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      return;
			if(in_array($_POST['service_tax'],$getServiceTaxValues)){
				$form->addError("Service tax already exists");
				return;
			}
			$getServiceTaxValues = array_merge($getServiceTaxValues,array($_POST['service_tax']));
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.service.tax.values',implode(',',$getServiceTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.service.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.service.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.service.tax',$getServiceTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Service Tax Added Sucessfully.');
		}else if($action == 'edit'){		
	    $this->view->form = $form = new Sesevent_Form_Admin_Addservicetax();
			$form->button->setLabel('Edit');
			$form->setTitle('Edit New Service Tax');
			$form->populate(array(
        'service_tax' => $data,
   	 	));
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      	return;
			if(!in_array($data,$getServiceTaxValues)){
				$form->addError("Service tax value not exists");
				return;
			}
			//$getServiceTaxValues = array_merge($getServiceTaxValues,array($_POST['service_tax']));
			//search Array
			$index = 0;
			foreach($getServiceTaxValues as $key=>$exa){
				if($exa == $data)	{
					$index = $key;	
					break;
				}
			}
			$getServiceTaxValues[$index] = $_POST['service_tax'];
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.service.tax.values',implode(',',$getServiceTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.service.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.service.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.service.tax',$getServiceTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Service Tax Edited Sucessfully.');			
		}else if($action == 'delete'){
			$this->view->form = $form = new Sesbasic_Form_Delete();
			$form->setTitle('Delete Service Tax?');
			$form->setDescription('Are you sure that you want to delete this service tax? It will not be recoverable after being deleted.');
			$form->submit->setLabel('Delete');
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      	return;
			$index = 0;
			foreach($getServiceTaxValues as $key=>$exa){
				if($exa == $data)	{
					unset($getServiceTaxValues[$key]);
					break;
				}
			}
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.service.tax.values',implode(',',$getServiceTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.service.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.service.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.service.tax',$getServiceTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Service Tax Deleted Sucessfully.');			
		}
			 return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array($this->view->message)
      ));
	}	
	public function entertainmentTaxAction(){
		 // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
		$data=$this->_getParam('data',false);		
		$action=$this->_getParam('actionA',false);
		$getEntertainmentTaxValues = Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.entertainment.tax.values','');
		if($getEntertainmentTaxValues)
			$getEntertainmentTaxValues = explode(',',$getEntertainmentTaxValues);
		else
			$getEntertainmentTaxValues = array();
		if($action == 'create'){
			 $this->view->form = $form = new Sesevent_Form_Admin_Addentertainmenttax();
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      return;
			if(in_array($_POST['entertainment_tax'],$getEntertainmentTaxValues)){
				$form->addError("Entertainment tax already exists");
				return;
			}
			$getEntertainmentTaxValues = array_merge($getEntertainmentTaxValues,array($_POST['entertainment_tax']));
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.entertainment.tax.values',implode(',',$getEntertainmentTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.entertainment.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.entertainment.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.entertainment.tax',$getEntertainmentTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Entertainment Tax Added Sucessfully.');
		}else if($action == 'edit'){		
	    $this->view->form = $form = new Sesevent_Form_Admin_Addentertainmenttax();
			$form->button->setLabel('Edit');
			$form->setTitle('Edit New Entertainment Tax');
			$form->populate(array(
        'entertainment_tax' => $data,
   	 	));
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      	return;
			if(!in_array($data,$getEntertainmentTaxValues)){
				$form->addError("Entertainment tax value not exists");
				return;
			}
			//$getEntertainmentTaxValues = array_merge($getEntertainmentTaxValues,array($_POST['service_tax']));
			//search Array
			$index = 0;
			foreach($getEntertainmentTaxValues as $key=>$exa){
				if($exa == $data)	{
					$index = $key;	
					break;
				}
			}
			$getEntertainmentTaxValues[$index] = $_POST['entertainment_tax'];
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.entertainment.tax.values',implode(',',$getEntertainmentTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.entertainment.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.entertainment.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.entertainment.tax',$getEntertainmentTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Entertainment Tax Edited Sucessfully.');			
		}else if($action == 'delete'){
			$this->view->form = $form = new Sesbasic_Form_Delete();
			$form->setTitle('Delete Entertainment Tax?');
			$form->setDescription('Are you sure that you want to delete this entertainment tax? It will not be recoverable after being deleted.');
			$form->submit->setLabel('Delete');
			if (!$this->getRequest()->isPost()) 
				return;
			if (!$form->isValid($this->getRequest()->getPost()))
      	return;
			$index = 0;
			foreach($getEntertainmentTaxValues as $key=>$exa){
				if($exa == $data)	{
					unset($getEntertainmentTaxValues[$key]);
					break;
				}
			}
			Engine_Api::_()->getApi('settings', 'core')->setSetting('seseventticket.entertainment.tax.values',implode(',',$getEntertainmentTaxValues));
			if(Engine_Api::_()->getApi('settings', 'core')->hasSetting('sesevent.ticket.entertainment.tax'))
				Engine_Api::_()->getApi('settings', 'core')->removeSetting('sesevent.ticket.entertainment.tax');
			Engine_Api::_()->getApi('settings', 'core')->setSetting('sesevent.ticket.entertainment.tax',$getEntertainmentTaxValues);
			$this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Entertainment Tax Deleted Sucessfully.');			
		}
			 return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => 10,
                  'parentRefresh' => 10,
                  'messages' => array($this->view->message)
      ));
	}	
}
