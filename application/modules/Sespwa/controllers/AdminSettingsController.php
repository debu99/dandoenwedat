<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sespwa_AdminSettingsController extends Core_Controller_Action_Admin {

    public function indexAction() {
        $db = Engine_Db_Table::getDefaultAdapter();

        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_settings');

        $this->view->form = $form = new Sespwa_Form_Admin_Settings_Global();

        if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.movemanifestfiles',0)) {
            $this->moveManifestFileToNewFolder();
            Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwa.movemanifestfiles',1);
        }

        if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
            $values = $form->getValues();
            include_once APPLICATION_PATH . "/application/modules/Sespwa/controllers/License.php";
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.pluginactivated')) {
                foreach ($values as $key => $value) {
                if($value != '')
                Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
                }
                $form->addNotice('Your changes have been saved.');
                if($error)
                $this->_helper->redirector->gotoRoute(array());
            }
        }
    }
    public function moveManifestFileToNewFolder(){
        // Get array of all source files
        $source = APPLICATION_PATH.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'Sespwa'.DIRECTORY_SEPARATOR;
        if(!is_dir($source))
            return;
        $files = scandir($source);
        // Identify directories
        $destination = APPLICATION_PATH.DIRECTORY_SEPARATOR.'sespwa'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;;
        // Cycle through all source files
        foreach ($files as $file) {
            if (in_array($file, array(".",".."))) continue;
            // If we copied this successfully, mark it for deletion
            if (copy($source.$file, $destination.$file)) {
                $delete[] = $source.$file;
            }
        }
    }
    public function mainMenuIconsAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_mainmenuicon');

        $this->view->storage = Engine_Api::_()->storage();
        $select = Engine_Api::_()->getDbTable('menuitems', 'core')->select()
                ->where('menu = ?', 'core_main')
                ->where('enabled = ?', 1)
                ->order('order ASC');
        $this->view->paginator = Engine_Api::_()->getDbTable('menuitems', 'core')->fetchAll($select);
    }

    public function uploadIconAction() {

        $this->_helper->layout->setLayout('admin-simple');
        $id = $this->_getParam('id', null);
        $menuType = $this->_getParam('type', null);

        $db = Engine_Db_Table::getDefaultAdapter();
        $select = new Zend_Db_Select($db);
        $menu = $select->from('engine4_core_menuitems')->where('id = ?', $id)->query()->fetchObject();

        $this->view->form = new Sespwa_Form_Admin_Icon();

        if ($this->getRequest()->isPost()) {
            if (isset($_FILES['photo']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {

                $photoFile = Engine_Api::_()->sespwa()->setPhoto($_FILES['photo'], $id);
                if (!empty($photoFile->file_id)) {
                $previousFile = Engine_Api::_()->getDbTable('menusicons','sesbasic')->getRow($menu->id);
                $previous_file_id = !empty($previousFile->sespwa_icon_id) ? $previousFile->sespwa_icon_id : 0;
                Engine_Api::_()->getDbTable('menusicons','sesbasic')->addSave($menu->id,$photoFile->file_id, '', 0, 'sespwa');

                $file = Engine_Api::_()->getItem('storage_file', $previous_file_id);
                if (!empty($file))
                    $file->delete();
                }
            }

            if ($menuType == 'main')
                $redirectUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sespwa', 'controller' => 'admin-settings', 'action' => 'main-menu-icons'), 'default', true);
            else
                $redirectUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sespwa', 'controller' => 'admin-settings', 'action' => 'footer-menu'), 'default', true);

            return $this->_forward('success', 'utility', 'core', array(
                        'parentRedirect' => $redirectUrl,
                        'messages' => 'Icon has been upoaded successfully.',
            ));
        }
    }

    public function deleteMenuIconAction() {

        $this->_helper->layout->setLayout('admin-simple');
        $this->view->id = $id = $this->_getParam('id', 0);
        $this->view->file_id = $file_id = $this->_getParam('file_id', 0);

        if ($this->getRequest()->isPost()) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $mainMenuIcon = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id);
            if($mainMenuIcon)
                $mainMenuIcon->delete();
            Engine_Api::_()->getDbTable('menusicons','sesbasic')->deleteNotification($id,'sespwa');;
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => 10,
            'parentRefresh' => 10,
            'messages' => array('')
        ));
        }
        // Output
        $this->renderScript('admin-settings/delete-menu-icon.tpl');
    }



  public function widgetCheck($params = array()) {

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    return $db->select()
                    ->from('engine4_sespwa_content', 'content_id')
                    ->where('type = ?', 'widget')
                    ->where('page_id = ?', $params['page_id'])
                    ->where('name = ?', $params['widget_name'])
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

  public function miniMenuIconsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_minimenuicon');

    $this->view->form = $form = new Sespwa_Form_Admin_MiniMenuIcons();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      $db = Engine_Db_Table::getDefaultAdapter();
      unset($values['sespwaminimenu_icons']);
      unset($values['sespwaminimenu_message_icons']);
      unset($values['sespwaminimenu_frrequest_icons']);
      foreach ($values as $key => $value) {
        //Notification Icon
        if($key == 'sespwaminimenu_notification_normal') {
          if (isset($_FILES['sespwaminimenu_notification_normal']) && isset($_FILES['sespwaminimenu_notification_normal']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_notification_normal']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_notification_normalremove']) && !empty($values['sespwaminimenu_notification_normalremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.notification.normal', '0');
            }
          } else {
            $value = 0;
          }

        }
        if($key == 'sespwaminimenu_notification_mouseover') {
          if (isset($_FILES['sespwaminimenu_notification_mouseover']) && isset($_FILES['sespwaminimenu_notification_mouseover']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_notification_mouseover']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_notification_mouseoverremove']) && !empty($values['sespwaminimenu_notification_mouseoverremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.notification.mouseover', '0');
            }
          } else {
            $value = 0;
          }
        }

        //Message Icon upload
        if($key == 'sespwaminimenu_message_normal') {
          if (isset($_FILES['sespwaminimenu_message_normal']) && isset($_FILES['sespwaminimenu_message_normal']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_message_normal']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_message_normalremove']) && !empty($values['sespwaminimenu_message_normalremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.message.normal', '0');
            }
          } else {
            $value = 0;
          }
        }
        if($key == 'sespwaminimenu_message_mouseover') {
          if (isset($_FILES['sespwaminimenu_message_mouseover']) && isset($_FILES['sespwaminimenu_message_mouseover']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_message_mouseover']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_message_mouseoverremove']) && !empty($values['sespwaminimenu_message_mouseoverremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.message.mouseover', '0');
            }
          } else {
            $value = 0;
          }
        }

        //Friend Requests
        if($key == 'sespwaminimenu_frrequest_normal') {
          if (isset($_FILES['sespwaminimenu_frrequest_normal']) && isset($_FILES['sespwaminimenu_frrequest_normal']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_frrequest_normal']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_frrequest_normalremove']) && !empty($values['sespwaminimenu_frrequest_normalremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.frrequest.normal', '0');
            }
          } else {
            $value = 0;
          }
        }
        if($key == 'sespwaminimenu_frrequest_mouseover') {
          if (isset($_FILES['sespwaminimenu_frrequest_mouseover']) && isset($_FILES['sespwaminimenu_frrequest_mouseover']['tmp_name'])) {
            $value = $this->setPhoto($_FILES['sespwaminimenu_frrequest_mouseover']);
            if($value)
              $value = $value->file_id;
            //Remove icon
            if (isset($values['sespwaminimenu_frrequest_mouseoverremove']) && !empty($values['sespwaminimenu_frrequest_mouseoverremove'])) {
              Engine_Api::_()->getApi('settings', 'core')->setSetting('sespwaminimenu.frrequest.mouseover', '0');
            }
          } else {
            $value = 0;
          }
        }
        if($value)
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }

    public function setPhoto($photo) {

        //GET PHOTO DETAILS
        $mainName = dirname($photo['tmp_name']) . '/' . $photo['name'];

        //GET VIEWER ID
        $photo_params = array(
            'parent_id' => 1,
            'parent_type' => "sespwa_menuicons",
        );
        copy($photo['tmp_name'], $mainName);
        try {
        $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
        } catch (Exception $e) {
        if ($e->getCode() == Storage_Api_Storage::SPACE_LIMIT_REACHED_CODE) {
            echo $e->getMessage();
            exit();
        }
        }

        return $photoFile;
    }


    public function menuSettingsAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_menusettings');

        $this->view->form = $form = new Sespwa_Form_Admin_MenuSettings();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();
            $db = Engine_Db_Table::getDefaultAdapter();
            foreach ($values as $key => $value) {
                if($key == 'sespwa_menuinformation_img') {
                    if(empty($value)) {
                        $value = 'public/admin/blank.png';
                    }
                }
                if($key == 'sespwa_menu_img') {
                    if(empty($value))
                        $value = 'public/admin/blank.png';
                }
                if (Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
                    Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
                if (!$value && strlen($value) == 0)
                    continue;
                Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
            }
            $form->addNotice('Your changes have been saved.');
        }

    }

    public function supportAction() {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_support');
    }
    function manifestAction(){
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_manifest');
        $this->view->form = $form = new Sespwa_Form_Admin_Manifest();

        $table = Engine_Api::_()->getDbTable('manifests','sespwa');
        $manifest = $table->fetchRow($table->select()->limit(1));
        if($manifest){
            $form->populate($manifest->toArray());
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();

            $table = Engine_Api::_()->getDbTable('manifests','sespwa');
            $db = $table->getAdapter();
            $db->beginTransaction();
            try {
                if(empty($manifest))
                    $manifest = $table->createRow();

                $manifest->setFromArray($values);
                if(!empty($_FILES["photo"]['name']) && $_FILES["photo"]['size'] > 0){
                    //give permission 0777
                    //remove previous images

                    $it = new DirectoryIterator(APPLICATION_PATH.DIRECTORY_SEPARATOR.'sespwa'.DIRECTORY_SEPARATOR.'images');
                    foreach( $it as $imagefile ) {
                        if( !$imagefile->isFile() ) continue;
                        @unlink($imagefile->getPathname());
                    }

                    $file = $form->photo->getFileName();
                    $name = str_replace(' ','',basename($_FILES["photo"]['name']));
                    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
                    $params = array(
                        'parent_type' => 'sespwa',
                        'parent_id' => $manifest->manifest_id,
                    );
                    $movePath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'sespwa'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
                    $baseFileName = pathinfo($name);
                    // Save
                    $storage = Engine_Api::_()->storage();
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(2000, 2000)
                        ->write($path . '/m_' . $name)
                        ->destroy();



                    $movePathName = $movePath.$name;
                    $manifest->app_icon = $name;
                    copy($path.'/m_'.$name,$movePathName);
                    @unlink($path . '/m_' . $name);

                    $name = str_replace(' ','',$baseFileName["filename"]).'-72X72.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(72, 72)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_72_72 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);

                    $name = str_replace(' ','',$baseFileName["filename"]).'-96X96.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(96, 96)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_96_96 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);

                    $name = str_replace(' ','',$baseFileName["filename"]).'-128X128.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(128, 128)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_128_128 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);


                    $name = str_replace(' ','',$baseFileName["filename"]).'-144X144.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(144, 144)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_144_144 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);


                    $name = str_replace(' ','',$baseFileName["filename"]).'-152X152.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(152, 152)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_152_152 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);


                    $name = str_replace(' ','',$baseFileName["filename"]).'-192X192.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(192, 192)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_192_192 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);


                    $name = str_replace(' ','',$baseFileName["filename"]).'-384X384.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(384, 384)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;
                    $manifest->icon_384_384 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);


                    $name = str_replace(' ','',$baseFileName["filename"]).'-512X512.'.$baseFileName["extension"];
                    $image = Engine_Image::factory();
                    $image->open($file)
                        ->resize(512, 512)
                        ->write($path . '/in_' . $name)
                        ->destroy();
                    $movePathName = $movePath.$name;

                    $manifest->icon_512_512 = $name;
                    copy($path.'/in_'.$name,$movePathName);
                    @unlink($path . '/in_' . $name);
                    $manifest->save();
                }
                $manifest->save();
                $form->addNotice("Manifest file updated successfully.");
                $db->commit();
            }catch(Exception $e){
                $db->rollBack();
                throw $e;
            }
            //update manifest file
            $table = Engine_Api::_()->getDbTable('manifests','sespwa');
            $manifest = $table->fetchRow($table->select()->limit(1));

            $manifestName["name"] = $manifest["appname"];
            $manifestName["short_name"] = $manifest["shotname"];
            $manifestName["desciption"] = $manifest["description"];
            $manifestName["theme_color"] = '#'.$manifest["themecolor"];
            $manifestName["background_color"] = '#'.$manifest["backgroundcolor"];
            $manifestName["display"] = 'standalone';
            $manifestName["orientation"] = 'any';
            $manifestName["app_icon"] = $this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["app_icon"]);
            $manifestName["start_url"] = $this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'?pwa=1');
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_72_72"]),
                'sizes'=>'72x72',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_96_96"]),
                'sizes'=>'96x96',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_128_128"]),
                'sizes'=>'128x128',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_144_144"]),
                'sizes'=>'144x144',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_152_152"]),
                'sizes'=>'152x152',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_192_192"]),
                'sizes'=>'192x192',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_384_384"]),
                'sizes'=>'384x384',
                'type'=>'image/png',
            );
            $manifestName["icons"][] = array(
                'src'=>$this->view->absoluteUrl($this->view->layout()->staticBaseUrl.'sespwa/images/'.$manifest["icon_512_512"]),
                'sizes'=>'512x512',
                'type'=>'image/png',
            );
            //echo "<pre>";var_dump($manifestName);die;
            $manifestFile = fopen(APPLICATION_PATH."/public/manifest.json", "w");
            fwrite($manifestFile, json_encode($manifestName));

        }
    }

    public function sinkPagesAction() {

      $db = Engine_Db_Table::getDefaultAdapter();

      $pagesTable = Engine_Api::_()->getDbTable('pages', 'sespwa');
      $pagesTableName = $pagesTable->info('name');

      $contentTable = Engine_Api::_()->getDbTable('content', 'sespwa');
      $contentTableName = $contentTable->info('name');

      $corepagesTable = Engine_Api::_()->getDbTable('pages', 'core');
      $corepagesTableName = $corepagesTable->info('name');

      $pageId = $pagesTable->select()
                  ->from($pagesTableName, 'page_id')
                  ->limit(1)
                  ->order('page_id DESC')
                  ->query()
                  ->fetchColumn();

      $contentId = $contentTable->select()
                  ->from($contentTableName, 'content_id')
                  ->limit(1)
                  ->order('content_id DESC')
                  ->query()
                  ->fetchColumn();

      $select = $corepagesTable->select()
                  ->from($corepagesTableName)
                  ->where('page_id > ?', $pageId)
                  ->order('page_id ASC');
      $results = $corepagesTable->fetchAll($select);
      
      
      // Get page param
      $pagePwaTable = Engine_Api::_()->getDbtable('pages', 'sespwa');
      $contentPwaTable = Engine_Api::_()->getDbtable('content', 'sespwa');
      
      foreach($results as $result) {
        // Make new page
        $pageObject = $pagePwaTable->createRow();
		$pageObject->name = $result->name;
        $pageObject->displayname = $result->displayname;
		$pageObject->url = $result->url;
		$pageObject->title = $result->title;
		$pageObject->description = $result->description;
		$pageObject->keywords = $result->keywords;
		$pageObject->custom = $result->custom;
		$pageObject->fragment = $result->fragment;
		$pageObject->layout = $result->layout;
		$pageObject->levels = $result->levels;
        $pageObject->provides = $result->provides;
		$pageObject->search = $result->search;
        $pageObject->save();
        $new_page_id = $pageObject->page_id;
         
        $old_page_content = $db->select()
            ->from('engine4_core_content')
            ->where('`page_id` = ?', $result->page_id)
            ->order(array('type', 'content_id'))
            ->query()
            ->fetchAll();
        
        $content_count = count($old_page_content);
        for($i = 0; $i < $content_count; $i++){
          $contentRow = $contentPwaTable->createRow();
          $contentRow->page_id = $new_page_id;
          $contentRow->type = $old_page_content[$i]['type'];
          $contentRow->name = $old_page_content[$i]['name'];
          if( $old_page_content[$i]['parent_content_id'] != null ) {
            $contentRow->parent_content_id = $content_id_array[$old_page_content[$i]['parent_content_id']];            
          }
          else{
            $contentRow->parent_content_id = $old_page_content[$i]['parent_content_id'];
          }
          $contentRow->order = $old_page_content[$i]['order'];
          $contentRow->params = $old_page_content[$i]['params'];
          $contentRow->attribs = $old_page_content[$i]['attribs'];
          $contentRow->save();
          $content_id_array[$old_page_content[$i]['content_id']] = $contentRow->content_id;
        }
      }
      $this->_redirect('admin');
    }
}
