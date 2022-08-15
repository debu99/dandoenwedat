<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_IndexController extends Sesapi_Controller_Action_Standard
{

  public function suggestAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');


        if($this->_getParam('allUser')){
            $select = $table->select()->where('enabled = ?','1');
        }else {
            $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
        }


      if( $this->_getParam('includeSelf', true) ) {
        $data[] = array(
          'type' => 'user',
          'id' => $viewer->getIdentity(),
          'guid' => $viewer->getGuid(),
          'label' => $viewer->getTitle() . ' (you)',
          'photo' => $this->view->itemPhoto($viewer, 'thumb.icon'),
          'url' => $viewer->getHref(),
        );
      }

      if( 0 < ($limit = (int) $this->_getParam('limit', 10)) ) {
        $select->limit($limit);
      }

      if( null !== ($text = $this->_getParam('search', $this->_getParam('value'))) ) {
        $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
      }
      $ids = array();
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
        $data[] = array(
          'type'  => 'user',
          'id'    => $friend->getIdentity(),
          'guid'  => $friend->getGuid(),
          'label' => $friend->getTitle(),
          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
          'url'   => $friend->getHref(),
        );
        $ids[] = $friend->getIdentity();
        $friend_data[$friend->getIdentity()] = $friend->getTitle();
      }
    }

    if( $this->_getParam('sendNow', true) ) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
  
  public  function updatePushTokenAction(){
      $device_id = $this->_getParam('device_id');
      $resource_id = $this->_getParam('user_id',$this->view->viewer()->getIdentity());
      Engine_Api::_()->getDbTable('users','sesapi')->register(array('user_id'=>$resource_id,'device_uuid'=>$device_id));
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => 1));
  }
  public function indexAction()
  {
    $this->view->someVar = 'someVal';
  }
  function privacyAction(){
    $str = $this->view->translate('_CORE_PRIVACY_STATEMENT');
    if ($str == strip_tags($str)) {
      // there is no HTML tags in the text
      $message = nl2br($str);
    } else {
      $message = $str;
    }
    $title =  $this->view->translate('Privacy Statement');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('privacy'=>array('description'=>$message,'title'=>$title))));
  }
  function termsAction(){
    $title =  $this->view->translate('Terms of Service');
    $str = $this->view->translate('_CORE_TERMS_OF_SERVICE');
    if ($str == strip_tags($str)) {
      // there is no HTML tags in the text
      $message = nl2br($str);
    } else {
      $message = $str;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('terms'=>array('description'=>$message,'title'=>$title))));
  }
  function appDefaultDataAction(){
      //update session
      $token = !empty($_REQUEST['auth_token']) ? $_REQUEST['auth_token'] : "";
      $table = Engine_Api::_()->getDbtable('aouthtokens', 'sesapi');
      if($token){
          $token = $table->check($token);
          if($token){
            $token->sessions++;
            $token->save();
          }
      }
      $result = array();
      $settings = Engine_Api::_()->getApi('settings', 'core');
      if(_SESAPI_PLATFORM_SERVICE == 1){
          $result['isEnableSkipLogin'] = $settings->getSetting('sesiosapp.guest.enable', 1) ? true : false;
      }else{
          $result['isEnableSkipLogin'] = $settings->getSetting('sesandroidapp.guest.enable', 1) ? true : false;
      }
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $result['is_core_activity'] = false;
      } else {
        $result['is_core_activity'] = true;
      }
     if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
        $recArray = array();
        $reactions = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->getPaginator();
        $counterReaction = 0;
        foreach($reactions as $reac){
          if(!$reac->enabled)
            continue;
          $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
          $result['reaction'][$counterReaction]  = $icon['main'];
          $counterReaction++;
        }
      }
      if(_SESAPI_PLATFORM_SERVICE == 1){
        $result['loginBackgroundImage'] = $this->getBaseUrl(true,$settings->getSetting('sesiosapp_login_background_image', $this->getBaseUrl(true,'application/modules/Sesiosapp/externals/images/login.jpeg')));
        $result['forgotPasswordBackgroundImage'] =  $this->getBaseUrl(true,$settings->getSetting('sesiosapp_forgot_background_image', $this->getBaseUrl(true,'application/modules/Sesiosapp/externals/images/forgot.jpeg')));
        $result['rateusBackgroundImage'] =  $this->getBaseUrl(true,$settings->getSetting('sesiosapp_rateus_background_image', $this->getBaseUrl(true,'application/modules/Sesiosapp/externals/images/rateus.jpg')));
        $result['dahsboardmenuBackgroundImage'] =  $this->getBaseUrl(true,$settings->getSetting('sesiosapp_dashboardmenu_background_image', $this->getBaseUrl(true,'application/modules/Sesiosapp/externals/images/dashboardmenu.jpg')));

        $result['loadingImage'] = $settings->getSetting('sesiosapp_loadingimage', '32');
        $result['titleHeaderType'] = $settings->getSetting('sesiosapp_show_titleheader', '');
        /* Admin - Setting is not working. #756 */
        $result['memberImageShapeIsRound'] = $settings->getSetting('sesiosapp_memberImageShapeIsRound', '0') ? true : false;
        $result['isNavigationTransparent'] = $settings->getSetting('sesiosapp_isNavigationTransparent', '0') ? true : false;
        $result['siteTitle'] = $settings->getSetting('sesiosapp_sitetitle', '') ;
        $result['enableLoggedinUserphoto'] = $settings->getSetting('sesiosapp_display_loggedinuserphoto', 1) ? true : false;
        $result['enableTabbedMenu'] = $settings->getSetting('sesiosapp_enable_tabbedmenu', 1) ? true : false;
        $result['limitForIphone'] = (string)$settings->getSetting('sesiosapp_limitForIphone', 10);
        $result['limitForIpad'] = (string)$settings->getSetting('sesiosapp_limitForIpad', 10);
        $result['descriptionTrucationLimitFeed'] = (int)$settings->getSetting('sesiosapp_feedtruncationlimit', 200);
        $result['enableTabbarTitle'] = $settings->getSetting('sesiosapp_showtabbartitle', 1) ? true : false;
        $result['enableHeaderFixedFeed'] = $settings->getSetting('sesiosapp.headerfixed', 1) ? true : false;
        $result['shareTextForFeed'] = $settings->getSetting('sesiosapp_shareontext', 'SocialEngine');
        $result['appstoreUrl'] = str_replace(array('https://','http://'),'',$settings->getSetting('sesiosapp_appurl', ''));
        $result['googleapikey'] = $settings->getSetting('sesiosapp_googleapikey', '');

        //default app styling
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $getActivatedTheme = $settings->getSetting('sesiosapptheme.color',1);
        $customActivatedTheme = $settings->getSetting('sesiosappcustom.theme.color',1);
        $isCustom = 0;
        $theme_id = $getActivatedTheme;
        if($getActivatedTheme == 5){
          $isCustom = 1;
          $theme_id = $customActivatedTheme;
        }
        $sesiosapptheme = Engine_Api::_()->getDbTable('customthemes','sesiosapp')->getThemeKey(array('theme_id'=>$theme_id,'is_custom'=>$isCustom));
        $themeStyling = array();
        $counterTheme = 0;
        foreach($sesiosapptheme as $res){
          if(!$res['value'])
            continue;
          $themeStyling[$counterTheme]['key'] = str_replace('sesiosapp_','',$res['column_key']);
          if(strpos($res['column_key'],'fontSize') !== false || strpos($res['column_key'],'buttonRadius') !== false || strpos($res['column_key'],'buttonBorderWidth') !== false){
            $themeStyling[$counterTheme]['value'] = (int)$res['value'];
          }else{
            $themeStyling[$counterTheme]['value'] = $res['value'];
          }
          $counterTheme++;
        }
        $result['theme_styling'] = $themeStyling;
        $moduleName = "sesiosapp";
      }else if(_SESAPI_PLATFORM_SERVICE == 2){
        $result['loginBackgroundImage'] = $this->getBaseUrl(true,$settings->getSetting('sesandroidapp_login_background_image', $this->getBaseUrl(true,'application/modules/Sesandroidapp/externals/images/login.jpeg')));
        $result['forgotPasswordBackgroundImage'] =  $this->getBaseUrl(true,$settings->getSetting('sesandroidapp_forgot_background_image', $this->getBaseUrl(true,'application/modules/Sesandroidapp/externals/images/forgot.jpeg')));
        $result['rateusBackgroundImage'] =  $this->getBaseUrl(true,$settings->getSetting('sesandroidapp_rateus_background_image', $this->getBaseUrl(true,'application/modules/Sesandroidapp/externals/images/rateus.jpg')));

        $result['titleHeaderType'] = $settings->getSetting('sesandroidapp_show_titleheader', '');
        /* Admin - Setting is not working. #756 */
        $result['memberImageShapeIsRound'] = $settings->getSetting('sesandroidapp_memberImageShapeIsRound', '0') ? true : false;
        $result['isNavigationTransparent'] = $settings->getSetting('sesandroidapp_isNavigationTransparent', '0') ? true : false;
        $result['siteTitle'] = $settings->getSetting('sesandroidapp_sitetitle', '') ;
        $result['enableLoggedinUserphoto'] = $settings->getSetting('sesandroidapp_display_loggedinuserphoto', 1) ? true : false;
        $result['limitForPhone'] = (string)$settings->getSetting('sesandroidapp_limitForphone', 10);
        $result['limitForTablet'] = (string)$settings->getSetting('sesandroidapp_limitForTablet', 10);
        $result['descriptionTrucationLimitFeed'] = (int)$settings->getSetting('sesandroidapp_feedtruncationlimit', 200);
        $result['enableTabbarTitle'] = $settings->getSetting('sesandroidapp_showtabbartitle', 1) ? true : false;
        $result['enableHeaderFixedFeed'] = $settings->getSetting('sesandroidapp.headerfixed', 1) ? true : false;
        $result['shareTextForFeed'] = $settings->getSetting('sesandroidapp_shareontext', 'SocialEngine');

        //default app styling
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $getActivatedTheme = $settings->getSetting('sesandroidapptheme.color',1);
        $customActivatedTheme = $settings->getSetting('sesandroidappcustom.theme.color',1);
        $isCustom = 0;
        $theme_id = $getActivatedTheme;
        if($getActivatedTheme == 5){
          $isCustom = 1;
          $theme_id = $customActivatedTheme;
        }
        $sesandroidapptheme = Engine_Api::_()->getDbTable('customthemes','sesandroidapp')->getThemeKey(array('theme_id'=>$theme_id,'is_custom'=>$isCustom));
        $themeStyling = array();
        $counterTheme = 0;
        foreach($sesandroidapptheme as $res){
          if(!$res['value'])
            continue;
          $themeStyling[$counterTheme]['key'] = str_replace('sesandroidapp_','',$res['column_key']);
          if(strpos($res['column_key'],'fontSize') !== false || strpos($res['column_key'],'buttonRadius') !== false || strpos($res['column_key'],'buttonBorderWidth') !== false){
            $themeStyling[$counterTheme]['value'] = (int)$res['value'];
          }else{
            $themeStyling[$counterTheme]['value'] = $res['value'];
          }
          $counterTheme++;
        }
        $result['theme_styling'] = $themeStyling;
        $moduleName = "sesandroidapp";
      }
      $user = Engine_Api::_()->user()->getViewer();
      if($user->getIdentity()){
          $result['user']["user_id"] = $user->user_id;
          $result['user']["email"] = $user->email;
          $result['user']["username"] = $user->username;
          $result['user']["displayname"] = $user->displayname;
          $result['user']["photo_id"] = $user->photo_id;
          $result['user']["status"] = $user->status;
          $result['user']["password"] = $user->password;
          $result['user']["status_date"] = $user->status_date;
          $result['user']["salt"] = $user->salt;
          $result['user']["locale"] = $user->locale;
          $result['user']["language"] = $user->language;
          $result['user']["timezone"] = $user->timezone;
          $result['user']["search"] = $user->search;
          $result['user']["level_id"] = $user->level_id;
          $result['user']['photo_url']= $this->userImage($this->view->viewer(),'thumb.profile');

      }
      //default slideshow
      $enableVideo = 0;
      if(_SESAPI_VERSION_ANDROID >= 1.2){
          $enableVideo = 1;
      }
      if(_SESAPI_VERSION_IOS >= 1.2 && _SESAPI_VERSION_IOS < 1.5){
          $enableVideo = 1;
      }
      $result['disable_welcome_screen'] = $settings->getSetting($moduleName.'.disable.welcome',0);
      $paginator = Engine_Api::_()->getDbtable('slides', $moduleName)->getSlides(true,array('fetchAll'=>true,'enableVideo'=>$enableVideo));

      if(_SESAPI_VERSION_IOS >= 1.5 || _SESAPI_VERSION_ANDROID >= 2.4){
        $isVideo = false;
        if($settings->getSetting($moduleName.'.video.slide',0)){
          $result['video_url'] = $this->getBaseUrl(true,$settings->getSetting($moduleName.'.video.slide',0));
          $result['video_slideshow'] = true;
        }
        if(count($paginator)){
          $slideshows = array();
          $counter = 0;
          foreach($paginator as $item){
            $photoUrl = $item->getFilePath();
            if(!$photoUrl && !$isVideo)
              continue;
            if($photoUrl)
              $slideshows[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            $slideshows[$counter]['title'] = $item->title;
            $slideshows[$counter]['description'] = $item->description;
            $slideshows[$counter]['title_color'] = '#'.$item->title_color;
            $slideshows[$counter]['description_color'] = '#'.$item->description_color;
            $counter++;
          }
          if(count($slideshows)){
            $result['slideshow'] = $slideshows;
          }
        }

          //graphic
          $graphics = array();
          $counter = 0;
          $paginator = Engine_Api::_()->getDbtable('graphics', $moduleName)->getGraphics(true,array('fetchAll'=>true));
          foreach($paginator as $item){
            if($item->file_id){
              $photoUrl = $item->getFilePath();
              if($photoUrl)
                $graphics[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            }
            $graphics[$counter]['title'] = $item->title;
            $graphics[$counter]['description'] = $item->description;
            $graphics[$counter]['title_color'] = '#'.$item->title_color;
            $graphics[$counter]['description_color'] = '#'.$item->description_color;
            $graphics[$counter]['background_color'] = '#'.$item->background_color;
            $counter++;
          }
          if(count($graphics)){
            $result['graphics'] = $graphics;
          }
      }else{
        if(count($paginator)){
          $slideshows = array();
          $counter = 0;
          $isVideo = false;
          foreach($paginator as $item){
            if($item->video_id){
              $videoUrl = $item->getFilePath('video_id');
              if(!$videoUrl)
                continue;
              $slideshows[$counter]['videourl'] = $this->getBaseUrl(false,$videoUrl);
              $isVideo = true;
            }
            $photoUrl = $item->getFilePath();
            if(!$photoUrl && !$isVideo)
              continue;
            if($photoUrl)
              $slideshows[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            $slideshows[$counter]['title'] = $item->title;
            $slideshows[$counter]['description'] = $item->description;
            $slideshows[$counter]['title_color'] = '#'.$item->title_color;
            $slideshows[$counter]['description_color'] = '#'.$item->description_color;
            $counter++;
          }
          if(count($slideshows)){
            $result['slideshow'] = $slideshows;
            if($isVideo){
              $result['video_slideshow'] = true;
            }
          }
        }
      }
    
    if(_SESAPI_PLATFORM_SERVICE == 1) {
      $result['is_story_enabled'] = $isStoryEnabled =  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('eandroidstories') ? true : false;
      if ($isStoryEnabled) {
        $result['story_video_limit'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesstories.videouplimit', 10);
        $result['sesstories_storyviewtime'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesstories.storyviewtime', 5);
      }
    } else if(_SESAPI_PLATFORM_SERVICE == 2) {
      $result['is_story_enabled'] = $isStoryEnabled =  Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('eiosstories') ? true : false;
      if ($isStoryEnabled) {
        $result['story_video_limit'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('eiosstories.videouplimit', 10);
        $result['sesstories_storyviewtime'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('eiosstories.storyviewtime', 5);
      }
    }

    // for live stream enable.
    if ((_SESAPI_VERSION_IOS >= 3.2 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID >= 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
      $result['is_livestream_enabled'] = (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('elivestreaming')) ? true : false;
      $result['linux_base_url'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('elivestreaming.linux.base.url',"");
    }

      //ses demo user
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesdemouser')){
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $headingText = $this->view->translate($settings->getSetting('sesdemouser.headingText', "Site Tour with Test Users"));
        $innerText = $this->view->translate($settings->getSetting('sesdemouser.innerText', 'Choose a test user to login and take a site tour.'));
        $limit = $settings->getSetting('sesdemouser.limit',6);
        $defaultimage = $settings->getSetting('sesdemouser.defaultimage', '');
        $results = Engine_Api::_()->getDbtable('demousers', 'sesdemouser')->getDemoUsers(array('widgettype' => 'widget', 'limit' => $limit));
        if($defaultimage){
          $defaultimage = $this->view->baseUrl() . '/' . $this->defaultimage;;
        }else{
          $defaultimage = $this->getBaseUrl(true,$this->view->layout()->staticBaseUrl.'application/modules/Sesdemouser/externals/images/nophoto_user_thumb_icon.png');
        }
        if (count($results) > 0){
          $demoUsers = array();
          $counterDemo = 0;
          foreach($results as $res){
             $user = Engine_Api::_()->getItem('user', (int) $res->user_id);
             $demoUsers[$counterDemo]['image_url'] = $this->userImage($user->getIdentity());
             $demoUsers[$counterDemo]['user_id'] = $user->getIdentity();
             $counterDemo++;
          }
          $result['demoUser']['users'] = $demoUsers;
          $result['demoUser']['defaultimage'] = $defaultimage;
          $result['demoUser']['headingText'] = $headingText;
          $result['demoUser']['innerText'] = $innerText;
        }
      }

      //if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessociallogin')){
          //$result['socialLogin'] = $this->socialLogin();
      //}
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }

  public function socialLogin(){
       $settings = Engine_Api::_()->getApi('settings', 'core');
       $returnUrl = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) .Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
       $facebookHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'facebook'), 'default', true);
       $twitterHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'twitter'), 'default', true);
       $linkdinHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'linkedin'), 'default', true);
       $likedinTable = Engine_Api::_()->getDbtable('linkedin', 'sessociallogin');
       $linkedinApi = $likedinTable->getApi();
       $instagramHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'instagram'), 'default', true);
       $instagramTable = Engine_Api::_()->getDbtable('instagram', 'sessociallogin');
      $instagram = $instagramTable->getApi('auth');
       $googleHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'google'), 'default', true);
       $googleTable = Engine_Api::_()->getDbtable('google', 'sessociallogin');
      $google = $googleTable->getApi();
       $pinterestHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'pinterest'), 'default', true);
       $pinterestTable = Engine_Api::_()->getDbtable('pinterest', 'sessociallogin');
       $yahooHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'yahoo'), 'default', true);
       $yahooTable = Engine_Api::_()->getDbtable('yahoo', 'sessociallogin');
       $hotmailHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'hotmail'), 'default', true);
       $hotmailTable = Engine_Api::_()->getDbtable('hotmail', 'sessociallogin');
        $flickrHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'flickr'), 'default', true);
       $flickrTable = Engine_Api::_()->getDbtable('flickr', 'sessociallogin');
       $vkHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'vk'), 'default', true);
       $vkTable = Engine_Api::_()->getDbtable('vk', 'sessociallogin');
       $counter = 0;
       $arrayData = array();
       $returnUrl = "&restApi=Sesapi";
       if(Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->getApi()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Facebook');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$facebookHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'facebook';
          $counter++;
       }
       if('none' != $settings->getSetting('core_twitter_enable', 'none')
    && $settings->core_twitter_secret){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Twitter');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$twitterHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'twitter';
          $counter++;
       }
       if($linkedinApi && $likedinTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Linkedin');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$linkdinHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'linkedin';
          $counter++;
       }
       if($instagramTable->isConnected() && $instagram){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Instagram');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$instagramHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'instagram';
          $counter++;
       }
       if($googleTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Google Plus');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$googleHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'googleplus';
          $counter++;
       }
       if($pinterestTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Pinterest');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$pinterestHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'pinterest';
          $counter++;
       }
       if($yahooTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Yahoo');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$yahooHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'yahoo';
          $counter++;
       }
       if($hotmailTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Hot Mail');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$hotmailHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'hotmail';
          $counter++;
       }
       if($flickrTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Flickr');
          $arrayData[$counter]['href'] =  $this->getBaseUrl(false,$flickrHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'flickr';
          $counter++;
       }
       if($vkTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Vkontakte');
          $arrayData[$counter]['href'] =  $this->getBaseUrl(false,$vkHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'vkontakte';
          $counter++;
       }
       return $arrayData;
  }

  //get album categories ajax based.
  public function subcategoryAction() {
    $type = $this->_getParam('type',0);
    $category_id = $this->_getParam('category_id', null);
    $module = $this->_getParam('moduleName','');
     $data = array();
    if ($category_id) {
			$subcategory = Engine_Api::_()->getDbtable('categories', $module)->getModuleSubcategory(array('category_id'=>$category_id,'column_name'=>'*','param'=>$type));
      $count_subcat = count($subcategory->toarray());
      if ($count_subcat > 0)
      $data[""] = "";
      if ($subcategory && $count_subcat) {
        foreach ($subcategory as $category) {
          $data[$category->getIdentity()] = Zend_Registry::get('Zend_Translate')->_($category["category_name"]);
        }
      }
    }
    $result["subcategory"] = $data;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
	// get album subsubcategory ajax based
  public function subsubcategoryAction() {
    $type = $this->_getParam('type',0);
    $category_id = $this->_getParam('subcategory_id', null);
    $module = $this->_getParam('moduleName','');
    $data = array();
    if ($category_id) {
      $subcategory = Engine_Api::_()->getDbtable('categories', $module)->getModuleSubsubcategory(array('category_id'=>$category_id,'column_name'=>'*','param'=>$type));
      $count_subcat = count($subcategory->toarray());
      if ($count_subcat > 0)
      $data[""] = "";
      if ($subcategory && $count_subcat) {
        foreach ($subcategory as $category) {
          $data[$category->getIdentity()] = Zend_Registry::get('Zend_Translate')->_($category["category_name"]);
        }
      }
    }
    $result["subsubcategory"] = $data;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
  public function likeAction(){
    $resource_id = $this->_getParam('resource_id',0);
    $resource_type = $this->_getParam('resource_type',0);
    $type = $this->_getParam('reaction_type',0);
    $notificationType = $actionType = $resource_type.'_like';
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    try{
    //make item
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);

      if (!$item) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
      }
    }catch(Exception $e){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $itemTable = Engine_Api::_()->getItemTable($resource_type,$resource_id);
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');

    $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', $resource_type)
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $resource_id);
    $result = $tableLike->fetchRow($select);

    if (count($result) > 0 && $type == 0) {
      //delete
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $result->delete();
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')){
          Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->removeExists($result->like_id);
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }

      $item->save();
      $subject = $item;
     // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Unliked Successfully"));

    } else {
      if(!$type)
        $type = 1;
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
       if(count($result) == 0){
        $like = $tableLike->createRow();
        $like->poster_id = $viewer_id;
        $like->resource_type = $resource_type;
        $like->resource_id = $resource_id;
        $like->poster_type = 'user';
        $like->save();
        $item->like_count = $item->like_count + 1;
        $item->save();
       }else{
        $like = $result;
        $like->save();
        $notActivity = true;
       }
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')){
          Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->isRowExists($like->like_id, $type, '');
        }
        //Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      //Send notification and activity feed work.
      $subject = $item;
      $owner = $subject->getOwner();
	     if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && $actionType && $notificationType && empty($notActivity)) {
	       $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
	       Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	       Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
	       $result = $activityTable->fetchRow(array('type =?' => $actionType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

	       if (!$result) {
	        $action = $activityTable->addActivity($viewer, $subject, $actionType);
	        if ($action)
	          $activityTable->attachActivity($action, $subject);
	       }
	     }
     // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Liked Successfully"));
    }
       //if($subject->getType() == "album_photo"){
        $itemTable = Engine_Api::_()->getItemTable($subject->getType(),$subject->getIdentity());
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $select = $tableLike->select()
              ->from($tableMainLike)
              ->where('resource_type = ?', $subject->getType())
              ->where('poster_id = ?', $viewer_id)
              ->where('poster_type = ?', 'user')
              ->where('resource_id = ?', $subject->getIdentity());
        $resultData = $tableLike->fetchRow($select);
        $response = array();
        if($resultData && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')){
             $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
            $response['reaction_type'] = $item_activity_like->type;
            $response['reactionUserData'] = $this->view->FluentListUsers($subject->likes()->getAllLikesUsers(),'',$subject->likes()->getLike($this->view->viewer()),$this->view->viewer());
        }

        $table = Engine_Api::_()->getDbTable('likes','core');
        $select = $table->select()->from($table->info('name'),array('total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$subject->getIdentity());
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
          $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
          $coreliketableName = $coreliketable->info('name');
          $recTable = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->info('name');
          $select->group('type')->setIntegrityCheck(false);
          $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
          $select->where('resource_type =?',$subject->getType());
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$coreliketableName.'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
        }
        $resultData =  $table->fetchAll($select);

            $response['is_like'] = Engine_Api::_()->sesapi()->contentLike($subject);
            $reactionData = array();
            $reactionCounter = 0;
            if(count($resultData) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
              foreach($resultData as $type){
                $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['total'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                $reactionCounter++;
              }
              $response['reactionData'] = $reactionData;
            }
     // }
     // if(isset($result)){
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $response));
      //}
  }
  protected function _likes($resource_id,$resource_type){
      if($resource_type == "sesadvancedactivity_action"){
          $action = Engine_Api::_()->getItem($resource_type,$resource_id);
          $likesGroup = Engine_Api::_()->sesadvancedcomment()->likesGroup($action);
          return $likesGroup['data'];
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      if ($resource_type != "sesadvancedactivity_action")
          $table = Engine_Api::_()->getDbTable('likes','core');
      else
          $table = Engine_Api::_()->getDbTable('likes','activity');
      $recTable = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->info('name');
      $select = $table->select()->from($table->info('name'),array('total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$resource_id)->group('type')->setIntegrityCheck(false);
      if ($resource_type != "sesadvancedactivity_action") {
          $select->where('resource_type =?', $resource_type);
          $sesCoreLikeTable = Engine_Api::_()->getDbtable('corelikes', 'sesadvancedactivity');
          $sesCoreLikeTableName = $sesCoreLikeTable->info('name');
          $select->setIntegrityCheck(false)
              ->joinLeft($sesCoreLikeTableName, $sesCoreLikeTableName.'.core_like_id ='.$table->info('name').'.like_id', array('type'));
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$sesCoreLikeTableName.'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
      }else{
          $select->where('resource_type =?', $resource_type);
          $sesActivityLikeTable = Engine_Api::_()->getDbtable('activitylikes', 'sesadvancedactivity');
          $sesActivityLikeTableName = $sesActivityLikeTable->info('name');
          $select->setIntegrityCheck(false)
              ->joinLeft($sesActivityLikeTableName, $sesActivityLikeTableName.'.core_like_id ='.$table->info('name').'.like_id', array('type'));
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$sesActivityLikeTableName.'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
      }
      return $table->fetchAll($select);
  }
	public function checkVersion($android,$ios){
		if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
				return  true;
		if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
				return true;
		return false;
	}
	public function commentsAction(){
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->getItem($resource_type,$resource_id);
    $sesAdv = false;
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    if($sesAdv){
      $page = $this->_getParam('page',false);
      $comments = array();
      //likes content
      if($page == 1){
          $likes = $this->_likes($resource_id,$resource_type);
          if(count($likes) > 0){
            $counter = 0;
            $total = 0;
            foreach($likes as $reac){
              $comments["comments"]['likes'][$counter]['reaction_id']  = $reac['type'];
              $comments["comments"]['likes'][$counter]['total']  = $reac['total'];
              $total = $total + $reac['total'];
              $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac['file_id'],'','');
              $comments["comments"]['likes'][$counter]['image']  = $icon['main'];
              $counter++;
            }
            $comments["comments"]["like_stats"]['total_likes'] = $total;
            $comments["comments"]["like_stats"]['likes_fluent_list'] = $this->view->FluentListUsers($subject->likes()->getAllLikes(),'',$subject->likes()->getLike($viewer),$viewer);
          }
      }
     }
      $extraParams = $commentsContent = array();
      //get Comments
      $reverseOrder = false;
      $canComment = $subject->authorization()->isAllowed($viewer, 'comment');
      $canDelete = $subject->authorization()->isAllowed($viewer, 'delete');
      $tableComment = Engine_Api::_()->getDbTable('comments','core');
      $tableCommentName = $tableComment->info('name');
      $commentSelect = $subject->comments()->getCommentSelect();
      if($sesAdv){
        if(strpos($commentSelect,'`engine4_activity_comments`') === FALSE){
          $commentsTableName = Engine_Api::_()->getDbtable('comments', 'core')->info('name');
          $sesCoreCommentsTable = Engine_Api::_()->getDbtable('corecomments', 'sesadvancedactivity');
          $sesCoreCommentsTableName = $sesCoreCommentsTable->info('name');
          $commentSelect->setIntegrityCheck(false)
                //->from($commentsTableName, array('*'))
                ->joinLeft($sesCoreCommentsTableName, $sesCoreCommentsTableName.'.core_comment_id ='.$commentsTableName.'.comment_id', array('*'));
          $commentSelect->where($sesCoreCommentsTableName.'.parent_id =?',0);

        }else{
          $commentsTableName = Engine_Api::_()->getDbtable('comments', 'activity')->info('name');
          $sesCoreCommentsTable = Engine_Api::_()->getDbtable('activitycomments', 'sesadvancedactivity');
          $sesCoreCommentsTableName = $sesCoreCommentsTable->info('name');
          $commentSelect->setIntegrityCheck(false)
                        //->from($commentsTableName, array('*'))
                        ->joinLeft($sesCoreCommentsTableName, $sesCoreCommentsTableName.'.activity_comment_id ='.$commentsTableName.'.comment_id', array('*'));
          $commentSelect->where($sesCoreCommentsTableName.'.parent_id =?',0);
        }
      }
      $commentSelect->reset('order');
      $commentSelect->order('comment_id DESC');
      $paginato = Zend_Paginator::factory($commentSelect);
      $paginato->setCurrentPageNumber($page);
      $paginato->setItemCountPerPage($this->_getParam('limit',5));
      $commentsContent = false;
      $commentsContent = $this->commentsContent($paginato,$subject,true);
      if(count($commentsContent))
      $comments["comment_data"] = $commentsContent;
      $albumenable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum');
      $videoenable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo');
      if($sesAdv){
        $comments['reply_comment'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablenestedcomments', 1) ? true : false;
      } else {
        $comments['reply_comment'] = false;
      }
        if(define(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS <= 2.2){
            $comments['reply_comment'] = false;
        }
      $comments['can_comment'] = $canComment ? true : false;
      if($sesAdv){
        $attachments = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enableattachement', ''));
        $comments['attachment_options'] = $attachments;
      }
      $comments['can_delete'] = $canDelete ? true : false;
      $comments['enable']['album'] = $albumenable ? 1 : 0;
      $comments['enable']['video'] = $videoenable ? 1 : 0;
      $extraParams['pagging']['total_page'] = $paginato->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginato->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginato->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $comments),$extraParams));
  }
  public function deleteAction(){
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));
    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
    // Identify if it's an action_id or comment_id being deleted
    $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
    $this->view->action_id  = $action_id  = (int) $this->_getParam('resource_id', null);
    $resources_type = $this->_getParam('resource_type',false);
    if( $resources_type && $action_id ) {
      $item = Engine_Api::_()->getItem($resources_type, $action_id);
      if( $item instanceof Core_Model_Item_Abstract &&
          (method_exists($item, 'comments') || method_exists($item, 'likes')) ) {
          if( !Engine_Api::_()->core()->hasSubject() ) {
              Engine_Api::_()->core()->setSubject($item);
          }
          //$this->_helper->requireAuth()->setAuthParams($item, $viewer, 'comment');
      }
    }
    if (!$item){
      // tell smoothbox to close
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
    }
    // Send to view script if not POST
    //if (!$this->getRequest()->isPost())
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
    if ($comment_id){
        $comment = $item->comments()->getComment($comment_id);
        // allow delete if profile/entry owner
        $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
        $db->beginTransaction();
          try {
              $item->comments()->removeComment($comment_id);
              $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
              $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
          } catch (Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => ""));
          }
    } else {
      // neither the item owner, nor the item subject.  Denied!
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));
    }  
  }
  public function viewcommentreplyAction()
  {
    // Collect params
    $comment_id = $this->_getParam('comment_id');
    if($this->_getParam('resource_type') == "sesadvancedactivity_action"){
        //$comment = Engine_Api::_()->getItem($this->_getParam('resource_type'),$comment_id);
        $action_id = $this->_getParam('activity_id',$this->_getParam('resource_id'));
      $action    = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActionById($action_id);
    }else{
        //$comment = Engine_Api::_()->getItem($this->_getParam('resource_type'),$comment_id);
      $action_id = $this->_getParam('resource_id');
      $action    = Engine_Api::_()->getItem($this->_getParam('resource_type'),$action_id);
    }
    $page = $this->_getParam('page');
    $viewer    = Engine_Api::_()->user()->getViewer();
    $replies['replies'] = $this->getReplies($action,$comment_id,$page);
    //$replies['comment'] = $comment->toArray();
    $viewMoreData = $this->getReplies($action,$comment_id,$page,true);
    if (count($viewMoreData)){
        $replies['viewMoreReplyData'] = $viewMoreData;
        $replies['viewMoreReplyData']['comment_id'] = $comment_id;
        $replies['viewMoreReplyData']['action_id'] = $action->getIdentity();
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $replies));
  }
  protected function getReplies($subject,$comment_id,$page = "zero",$isPagging = false){
    $commentSelect = $subject->comments()->getCommentSelect();
    if(strpos($commentSelect,'`engine4_activity_comments`') === FALSE){
          $activitycommentsTable = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity');
          $activitycommentsTableName = $activitycommentsTable->info('name');
          $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'core');
          $coreCommentTableName = $coreCommentTable->info('name');
          $select = $coreCommentTable->select()
              ->from($coreCommentTable,'*');
          $select->joinLeft($activitycommentsTableName, "$activitycommentsTableName.core_comment_id = $coreCommentTableName.comment_id",'*')
              ->setIntegrityCheck(false);
          $select->where('parent_id =?', $comment_id);
		}else{
          $activitycommentsTable = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity');
          $activitycommentsTableName = $activitycommentsTable->info('name');
          $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'activity');
          $coreCommentTableName = $coreCommentTable->info('name');
          $select = $coreCommentTable->select()
              ->from($coreCommentTable,'*');
          $select->joinLeft($activitycommentsTableName, "$activitycommentsTableName.activity_comment_id = $coreCommentTableName.comment_id",'*')
              ->setIntegrityCheck(false);
          $select->where('parent_id =?', $comment_id);
		}
    if($page == 'zero'){
       $commentCount = count($select->query()->fetchAll());
       $page = ceil($commentCount/5);
    }
    $select->reset('order');
    $viewMoreReplyData = array();
    $select->order('comment_id DESC');
    $comments = Zend_Paginator::factory($select);
    $comments->setCurrentPageNumber($page);
    $comments->setItemCountPerPage($this->_getParam('limit_data',1));
    if($isPagging && $comments->getCurrentPageNumber() > 1 ):
      if($comment instanceof Activity_Model_Comment){
        $module = 'activity';
      }else{
        $module="core";
      }
     $viewMoreReplyData['module'] = $module;
     $viewMoreReplyData['page'] = $comments->getCurrentPageNumber() - 1;
    endif;
    if($isPagging){
      return $viewMoreReplyData;
    }
    return $this->commentsContent($comments,$subject,false,$viewMoreReplyData);
  }
  protected function commentsContent($comments,$subject,$isComment = false,$viewMoreReplyData = array()){
		$guid = $this->_getParam('guid');
    $array = array();
    $counter = 0;
    
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($comments as $comment){
      $array[$counter] = $comment->toArray();
      $array[$counter]["is_like"] = Engine_Api::_()->sesapi()->contentLike($comment);
      $replies = array();
      if($isComment && $this->checkVersion(3.0,4.0)){
        //if(0){
        //get comment replies
       if($sesAdv)
        $replies  = $this->getReplies($subject,$comment["comment_id"]);
       if(@count($replies)){
         $array[$counter]["replies"] = $replies;
			 }
        $viewMoreData = array();
         if($sesAdv)
          $viewMoreData = $this->getReplies($subject,$comment["comment_id"],'zero',true);
         if (@count($viewMoreData)){
            $array[$counter]['viewMoreReplyData'] = $viewMoreData;
            $array[$counter]['viewMoreReplyData']['comment_id'] = $comment->getIdentity();
            $array[$counter]['viewMoreReplyData']['action_id'] = $subject->getIdentity();
         }
					$likeResult = array();
					$likesGroup = array();
					if($sesAdv)
            $likesGroup = Engine_Api::_()->sesadvancedcomment()->commentLikesGroup($comment,false);
					//echo '<pre>';print_r($likesGroup);
					//echo '<pre>';print_r($likesGroup);die;
					//$photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($comment);
					$reactionData = array();
					$reactionCounter = 0;
					if(@count($likesGroup['data'])){
						foreach($likesGroup['data'] as $type){

							$reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
							$reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $comment->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);
							$reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
							$reactionCounter++;
						}
					}
        if($sesAdv) {
					$array[$counter]['reactionUserData'] = $this->view->FluentListUsers($comment->likes()->getAllLikes(),'',$comment->likes()->getLike($this->view->viewer()),$this->view->viewer());;
					if(count($reactionData))
					$array[$counter]['reactionData'] = $reactionData;
        }
      if($likeRow = $comment->likes()->getLike(!empty($guid) ? Engine_Api::_()->getItemByGuid($guid) : Engine_Api::_()->user()->getViewer()) ){
        $type = '';
        $imageLike = '';
        $text = 'Unlike';
        if($likeRow->getType() == 'activity_like' && $sesAdv) {
          $item_activity_like = Engine_Api::_()->getDbTable('activitylikes', 'sesadvancedactivity')->rowExists($likeRow->like_id);
          $type = $item_activity_like->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type));
          $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
        } else if($likeRow->getType() == 'core_like' && $sesAdv) {
          $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($likeRow->like_id);
          $type = $item_activity_like->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->sesadvancedcomment()->likeImage($type));
          $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
        }
        $likeResult['is_like'] = true;
        $like = true;
      }else{
        $likeResult['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
      }

        if(empty($like)) {
            $array[$counter]["like"]["name"] = "like";
        }else {
            $array[$counter]["like"]["name"] = "unlike";
        }
        $array[$counter]["like"]["type"] = $type;
        $array[$counter]["like"]["image"] = $imageLike;
        $array[$counter]["like"]["title"] = $text ? $this->view->translate($text):'';
			}
      //get hashtags from body
      $array[$counter]['hashTags'] = Engine_Api::_()->sesapi()->gethashtags($comment->body);
      //get mention from body
      $array[$counter]['mention'] = Engine_Api::_()->sesapi()->getMentionTags($comment->body);
    if($sesAdv) {
      if($comment->getType() == 'activity_comment') {
        $activitycomments = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->rowExists($comment->getIdentity());
      } else if($comment->getType() == 'core_comment') {
        $activitycomments = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->rowExists($comment->getIdentity());
      }

      if($activitycomments->file_id){
          $getFilesForComment = Engine_Api::_()->getDbTable('commentfiles','sesadvancedcomment')->getFiles(array('comment_id'=>$comment->comment_id));
          $attachmentCounter = 0;
        foreach($getFilesForComment as $fileid){
          if($fileid->type == 'album_photo'){
            try{
              $photo = Engine_Api::_()->getItem('album_photo',$fileid->file_id);
              if($photo){
                $attachPhoto  = Engine_Api::_()->sesapi()->getPhotoUrls($photo,'','');
                if(count($attachPhoto)){
                  $array[$counter]['attachphotovideo'][$attachmentCounter]["images"] = $attachPhoto;
                  $array[$counter]["attachphotovideo"][$attachmentCounter]["id"] = $photo->getIdentity();
                  $array[$counter]['attachphotovideo'][$attachmentCounter]["type"] = "album_photo";
                }else{
                  continue;
                }
              } else {
                continue;
              }
            }catch(Exception $e){
              continue;
          }
          }else{
            try{
             $video = Engine_Api::_()->getItem('video',$fileid->file_id);
             if($video){
               $videoAttach =  Engine_Api::_()->sesapi()->getPhotoUrls($video,'','');
               if(count($videoAttach)){
                $array[$counter]['attachphotovideo'][$attachmentCounter]["images"] = $videoAttach;
                $array[$counter]["attachphotovideo"][$attachmentCounter]["id"] = $video->getIdentity();
                  $array[$counter]['attachphotovideo'][$attachmentCounter]["type"] = $video->getType();
               }else
                continue;
              } else {
                continue;
              }
            }catch(Exception $e){

            }
          }
          $attachmentCounter++;
        }
      }else if($activitycomments->emoji_id){
        $emoji =  Engine_Api::_()->getItem('sesadvancedcomment_emotionfile',$activitycomments->emoji_id);
        if($emoji){
           $photo = Engine_Api::_()->sesapi()->getPhotoUrls($emoji->photo_id,'','');
           $array[$counter]['emoji_image'] = $photo["main"];
        }
      }
			
      if($activitycomments->preview && !$activitycomments->showpreview){
        $link = Engine_Api::_()->getItem('core_link',$activitycomments->preview);
        $array[$counter]['link']['images']  = Engine_Api::_()->sesapi()->getPhotoUrls($link,'','');
        $array[$counter]['link']['href'] = $this->getBaseUrl(false,$link->getHref());
        $array[$counter]['link']['title'] = $link->title;
        $parseUrl = parse_url($link->uri);
        $desc =  str_replace(array('www.','demo.'),array('',''),$parseUrl['host']);
        $array[$counter]['link']['description'] = $desc;
      }
    }
      //user
      if($comment->poster_type == "user"){
        $user = Engine_Api::_()->getItem('user',$comment->poster_id);
        $array[$counter]['user_image'] = $this->userImage($user->getIdentity(),"thumb.profile");
        $user_id = $user->getIdentity();
      }else{
        $user = Engine_Api::_()->getItem($comment->poster_type,$comment->poster_id);
        $array[$counter]['user_image'] = $this->getBaseUrl(true,$user->getPhotoUrl('thumb.profile'));
        $user_id = $user->getParent()->getIdentity();
      }
        $array[$counter]['user_href'] = $this->getBaseUrl(true,$user->getHref());
      $array[$counter]['user_title'] = $user->getTitle();
      $type = $comment->getType();
      if ($comment->poster_id == $viewer->getIdentity() || $viewer->isAdmin()){
				$array[$counter]["can_delete"] = true;
				$optionCounter = 0;
				if($comment->body){
					$array[$counter]['options'][$optionCounter]['name']= 'edit';
					$array[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Edit');
					$optionCounter++;
				}
				$array[$counter]['options'][$optionCounter]['name']= 'delete';
				$array[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Delete');

     }else{
				$array[$counter]["can_delete"] = false;
     }
      $counter++;
    }
    return $array;
  }
   public function favouriteAction(){
    $resource_id = $this->_getParam('resource_id',0);
    $resource_type = $this->_getParam('resource_type',0);
    $notificationType = $resource_type.'_favourite';
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    try{
    //make item
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if (!$item) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
    }
    }catch(Exception $e){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->getItemfav($resource_type, $resource_id);

    $favItem = Engine_Api::_()->getItemtable($resource_type, $resource_id);
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      $item->favourite_count = $item->favourite_count - 1;
      $item->save();
      if(@$notificationType) {
	      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
	      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
	      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
      }
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Unfavourite Successfully"));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->createRow();
        if($resource_type == "sespage_album" || $resource_type == "sespage_photo" || $resource_type == "sesgroup_album" || $resource_type == "sesgroup_photo" || $resource_type == "sesbusiness_album" || $resource_type == "sesbusiness_photo"){
          $fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        }else{
          $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        }
        $fav->resource_type = $resource_type;
        $fav->resource_id = $resource_id;
        $fav->save();
        $item->favourite_count = $item->favourite_count + 1;
        $item->save();
        // Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      if(@$notificationType && $resource_type != "sesmusic_artist") {
	      $subject = $item;
	      $owner = $subject->getOwner();
	      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
	        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
	        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
	        $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	        if (!$result) {
	          $action = $activityTable->addActivity($viewer, $subject, $notificationType);
	          if ($action)
	            $activityTable->attachActivity($action, $subject);
	        }
	      }
      }
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Favourite Successfully"));
    }
  }
  public function commentLikeAction(){
    $viewer = $this->view->viewer();
    if($viewer->getIdentity() == 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array()));
    $resource_id = $this->_getParam('resource_id',"");
    $resource_type = $this->_getParam('resource_type',"");
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if(!$item)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    //check view privacy
    if (!$this->_helper->requireAuth()->setAuthParams($item, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $canComment = $item->authorization()->isAllowed($viewer, 'comment');
    $canDelete = $item->authorization()->isAllowed($viewer, 'edit');
    $commentLikeStats = array();
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
      $recArray = array();
      $reactions = Engine_Api::_()->getDbTable('reactions','sesadvancedcomment')->getPaginator();
      $counter = 0;
      foreach($reactions as $reac){
        if(!$reac->enabled)
          continue;
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['reaction_id']  = $reac['reaction_id'];
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['title']  = $this->view->translate($reac['title']);
        $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['image']  = $icon['main'];
        $counter++;
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();
      if($viewer_id){
          $itemTable = Engine_Api::_()->getItemTable($resource_type,$resource_id);
          $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
          $tableMainLike = $tableLike->info('name');

          $sesCoreLikeTable = Engine_Api::_()->getDbtable('corelikes', 'sesadvancedactivity');
          $sesCoreLikeTableName = $sesCoreLikeTable->info('name');

          $select = $tableLike->select()
              ->from($tableMainLike)
              ->where('resource_type = ?', $resource_type)
              ->where('poster_id = ?', $viewer_id)
              ->where('poster_type = ?', 'user')
              ->where('resource_id = ?', $resource_id);
          $select->setIntegrityCheck(false)
              ->joinLeft($sesCoreLikeTableName, $sesCoreLikeTableName.'.core_like_id ='.$tableMainLike.'.like_id', array('type'));

          $result = $tableLike->fetchRow($select);
        if($result){
            $commentLikeStats['stats']['reaction_type'] = $result->type;
        }
      }
      $commentLikeStats['stats']['comment_Count'] = (int) Engine_Api::_()->sesadvancedcomment()->commentCount($item,'subject');
    }
    $type = "user_id";
    $id = "user_id";
    if($resource_type == "sespage_album"){
      $type = "sespage_album";
      $id = "owner_id";
    }else if($resource_type == "sespage_photo"){
      $type = "sespage_photo";
      $id = "owner_id";
    }else if($resource_type == "sesgroup_album"){
      $type = "sesgroup_album";
      $id = "owner_id";
    }else if($resource_type == "sesgroup_photo"){
      $type = "sesgroup_photo";
      $id = "owner_id";
    }else if($resource_type == "sesbusiness_album"){
      $type = "sesbusiness_album";
      $id = "owner_id";
    }else if($resource_type == "sesbusiness_photo"){
      $type = "sesbusiness_photo";
      $id = "owner_id";
    }
   
    $commentLikeStats['stats']['is_like'] = Engine_Api::_()->sesapi()->contentLike($item); 
    $commentLikeStats['stats']['like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
    if(isset($item->favourite_count)){
      $commentLikeStats['stats']['is_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($item,'favourites',$item->getModuleName(),$type,$id);
      $commentLikeStats['stats']['favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($item,'favourites',$item->getModuleName(),$type,$id);
    }
    $commentLikeStats['stats']['can_comment'] = $canComment ? true : false;
    $commentLikeStats['stats']['can_delete'] = $canDelete ? true : false;
    $commentLikeStats['stats']['loggedin'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $commentLikeStats));
  }


  public function createAction()
  {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: POST, GET');
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
    ini_set("memory_limit","240M");
    $guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if(!$guid)
        $guid = "";
    }else{
        $guid = "";
    }
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
    
    // Not post
    $subject_id = $this->_getParam('resource_id',false);
    $subject_type = $this->_getParam('resource_type',false);
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    if($sesAdv)
	    $actionTable = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity');
    else
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    // Start transaction
    if(!$subject_id)
      $db = $actionTable->getAdapter();
    else{
      $action = Engine_Api::_()->getItem($subject_type,$subject_id);
      $db = Engine_Api::_()->getItemtable($action->getType())->getAdapter();
    }
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $action_id = $this->view->action_id = $this->_getParam('activity_id', $this->_getParam('action_id', null));
      if(!$subject_id){
       $action = $actionTable->getActionById($action_id);
       $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
      }else{
        //$action = Engine_Api::_()->getItem($subject_type,$subject_id);
        $actionOwner = $action->getOwner();
      }
      if (!$action) {
        $this->view->status = false;
        $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $this->_getParam('body',$_POST['body']);
      //Emojis Work
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
          $body = str_replace($bodyEmoji,$emojisCode,$body);
        }
      }
      //Emojis Work End
      $emoji_id = $_POST['emoji_id'];
      // Check authorization
      if (!$subject_id && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('This user is not allowed to comment on this item.'), 'result' => array()));
      $photoupload  = array();

      // If we're here, we're done
      if(!empty($_FILES["attachmentImage"]) && count($_FILES["attachmentImage"]) > 0 && $sesAdv){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
           $counter = 0;
           foreach($_FILES['attachmentImage']['name'] as $key=>$image){
              $uploadimage = array();
              if ($_FILES['attachmentImage']['name'][$key] == "")
               continue;
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$key];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$key];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$key];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$key];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$key];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $photoupload[$counter] = $photo->getIdentity().'_album_photo';
            $counter++;
          }
          }catch(Exception $e){
            $this->view->error =  $e->getMessage();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
          }
      }
      if(count($_POST['video']) && $sesAdv){
        $counter = 0;
        $uploadData = array();
         ksort($_POST['video']);
        foreach($_POST['video'] as $video){
          if($video == "photo"){
             if(!empty($photoupload[$counter]))
              $uploadData[] = $photoupload[$counter];
             $counter++;
          }else{
             $uploadData[] = $video;
          }
        }
      }
      if($sesAdv) {
        if(count($uploadData) ){
          $uploadData = array_filter($uploadData, 'strlen');
          $_POST['file_id'] = implode(',',$uploadData);
        }else if(count($photoupload)){
          $uploadData = array_filter($uploadData, 'strlen');
          $_POST['file_id'] = implode(',',$photoupload);
        }
      }
      // Add the comment
      if(!$body)
        $body = "";
      $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
      $bodyEmojis = explode(' ', $body);
      foreach($bodyEmojis as $bodyEmoji) {
        $emojisCode = Engine_Api::_()->sesapi()->encode($bodyEmoji);
        $body = str_replace($bodyEmoji,$emojisCode,$body);
      }
      $comment =  $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();
      $sesAdcCommentRow = null;
      if($sesAdv) {
        if($comment->getType() == 'activity_comment') {
          $sesAdcCommentRow = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->isRowExists($comment->comment_id);
        } else if($comment->getType() == 'core_comment') {
          $sesAdcCommentRow = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->isRowExists($comment->comment_id);
        }
      }
      $comment = Engine_Api::_()->getItem($typeC,$comment->comment_id);
      $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
      if($file_id && $file_id != '' && $sesAdcCommentRow){
        $counter = 1;
        $file_ids = explode(',',$file_id);
        $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'sesadvancedcomment');
        foreach($file_ids as $file_id){
          if(!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if(strpos($file_id,'_album_photo')){
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo','',$file_id);
          }else{
            $file->type = 'video';
            $file->file_id = str_replace('_video','',$file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();
          if($counter == 1){
            $sesAdcCommentRow->file_id = $file_id;
            $sesAdcCommentRow->save();
          }
          $counter++;
        }
      }
      if($emoji_id && $sesAdcCommentRow){
        $sesAdcCommentRow->emoji_id = $emoji_id;
        $sesAdcCommentRow->file_id = 0;
        $comment->body = '';
        $comment->save();
        $sesAdcCommentRow->save();
      }
      //sespage comment
      if($guid){
        $comment->poster_type = $guid->getType();
        $comment->poster_id = $guid->getIdentity();
        $comment->save();
        Engine_Hooks_Dispatcher::getInstance()->callEvent('onCommentCreateAfter', $comment);
      }
      //fetch link from comment
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if(!empty($matches[0]) && $sesAdcCommentRow){
        $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
        if($preview){
          $sesAdcCommentRow->preview = $preview;
          $sesAdcCommentRow->save();
        }
      }
      // Notifications
      if($sesAdv)
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'sesadvancedactivity');
      else 
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      // Add notification for owner of activity (if user and not viewer)
      if( (!$subject_id && $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) || ($subject_id && !$viewer->isSelf($actionOwner)) )
      {
        $notifyApi->addNotification($actionOwner, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'commented', array(
          'label' => 'post'
        ));
      }
      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->comments()->getAllCommentsUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'commented_commented', array(
            'label' => 'post'
          ));
        }
      }
      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->likes()->getAllLikesUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'liked_commented', array(
            'label' => 'post'
          ));
        }
      }
      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['bodymention'], $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "comment" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id_reply = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type_reply = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply,$resource_id_reply);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid->getOwner() : $viewer, $viewer, 'sesadvancedcomment_tagged_people', array("commentLink" => $commentLink));
      }
      //Tagging People by status box
      // Stats
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_($e);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $commentContent = $this->commentsContent(array($comment),$action);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));
  }
  public function replyAction()
  {
    ini_set("memory_limit","240M");
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));


    // Not post
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

		$guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if(!$guid)
        $guid = "";
    }else{
        $guid = "";
    }

    // Start transaction
    $db = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $resource_type = $this->_getParam('resource_type',false);
      if(!$resource_type){
        $action_id = $this->view->action_id = $this->_getParam('resource_id', $this->_getParam('action', null));
        $action = Engine_Api::_()->getDbtable('actions', 'sesadvancedactivity')->getActionById($action_id);
        $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
      }else{
        $action = Engine_Api::_()->getItem($resource_type,$this->_getParam('resource_id'));
        $actionOwner = $action->getOwner();
      }

      if (!$action) {
        $this->view->status = false;
        $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
      }
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];
      // Check authorization
      if (!$resource_type && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')){
        $this->view->error = 'This user is not allowed to comment on this item.';
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }

      $photoupload  = array();
      // If we're here, we're done
      if(!empty($_FILES["attachmentImage"]) && count($_FILES["attachmentImage"]) > 0){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
           $counter = 0;
           foreach($_FILES['attachmentImage']['name'] as $key=>$image){
              $uploadimage = array();
              if ($_FILES['attachmentImage']['name'][$key] == "")
               continue;
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$key];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$key];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$key];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$key];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$key];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $photoupload[$counter] = $photo->getIdentity().'_album_photo';
            $counter++;
          }
          }catch(Exception $e){

            $this->view->error =  $e->getMessage();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
          }
      }

      if(count($_POST['video'])){
        $counter = 0;
        $uploadData = array();
         ksort($_POST['video']);
        foreach($_POST['video'] as $video){
          if($video == "photo"){
             if(!empty($photoupload[$counter]))
              $uploadData[] = $photoupload[$counter];
             $counter++;
          }else{
             $uploadData[] = $video;
          }
        }
      }
      if(count($uploadData)){
        $uploadData = array_filter($uploadData, 'strlen');
        $_POST['file_id'] = implode(',',$uploadData);
      }else if(count($photoupload)){
        $uploadData = array_filter($uploadData, 'strlen');
        $_POST['file_id'] = implode(',',$photoupload);
      }

      // Add the comment
      if(!$body)
        $body = "";
      $comment =  $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();

			if($typeC == 'activity_comment') {
        $sesadcTable = Engine_Api::_()->getDbtable('activitycomments', 'sesadvancedactivity');
        $sesadcTableName = $sesadcTable->info('name');
      } else if($typeC == 'core_comment') {
        $sesadcTable = Engine_Api::_()->getDbtable('corecomments', 'sesadvancedactivity');
        $sesadcTableName = $sesadcTable->info('name');
      }

      $comment = Engine_Api::_()->getItem($typeC,$comment->comment_id);
       $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
      if($file_id && $file_id != ''){
        $counter = 1;
        $file_ids = explode(',',$file_id);
        $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'sesadvancedcomment');
        foreach($file_ids as $file_id){
          if(!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if(strpos($file_id,'_album_photo')){
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo','',$file_id);
          }else{
            $file->type = 'video';
            $file->file_id = str_replace('_video','',$file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();
          if($counter == 1){
						if($typeC == 'activity_comment') {
                $sesadcRow = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->isRowExists($comment->comment_id, $file_id);
            } else if($typeC == 'core_comment') {
                $sesadcRow = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->isRowExists($comment->comment_id, $file_id);
            }
            //$comment->file_id = $file_id;
            //$comment->save();
          }
          $counter++;
        }
      }
        if($typeC == 'activity_comment' && empty($sesadcRow)) {
            $sesadcRow = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->isRowExists($comment->comment_id);
        } else if($typeC == 'core_comment' && empty($sesadcRow)) {
            $sesadcRow = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->isRowExists($comment->comment_id);
        }
      $emoji_id = $_POST['emoji_id'];
      if($emoji_id){
          $sesadcRow->emoji_id = $emoji_id;
          $sesadcRow->file_id = 0;
          $comment->body = '';
          $sesadcRow->save();
      }

			  //sespage comment
      if($guid){
          $sesadcRow->poster_type = $guid->getType();
          $sesadcRow->poster_id = $guid->getIdentity();
          $sesadcRow->save();
      }

			$gif_id = $_POST['gif_id'];
      if($gif_id){
          $sesadcRow->gif_id = $gif_id;
          $sesadcRow->file_id = 0;
          $sesadcRow->save();
        $comment->body = '';
        $comment->save();
        $image = Engine_Api::_()->getItem('sesfeedgif_image', $gif_id);
        $image->user_count++;
        $image->save();
      }


      $parentCommentType = 'core_comment';
      if($action->getType() == 'activity_action'){
        $commentType = $action->likes(true);
        if($commentType->getType() == 'activity_action')
          $parentCommentType = 'activity_comment';
      }

       $parentCommentId = $this->_getParam('comment_id',false);
       if($parentCommentType == 'activity_comment') {
            $parentCommentRow = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->rowExists($parentCommentId);
       } else if($parentCommentType == 'core_comment') {
            $parentCommentRow = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->rowExists($parentCommentId);
       }

      $parentComment = Engine_Api::_()->getItem($parentCommentType,$parentCommentId);
      $parentCommentRow->reply_count = new Zend_Db_Expr('reply_count + 1');
      $parentCommentRow->save();
      $sesadcRow->parent_id = $parentCommentId;
      $sesadcRow->save();
      //fetch link from comment
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if(!empty($matches[0])){
        $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
        if($preview){
          $sesadcRow->preview = $preview;
          $sesadcRow->save();
        }
      }

			// Notifications
      // Comment Reply notification to comment owner
      if($parentComment->poster_type == 'user' && $parentComment->poster_id != $viewer->getIdentity()) {
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'sesadvancedactivity');
        $user = Engine_Api::_()->getItem('user', $parentComment->poster_id);
        $notifyApi->addNotification($user, !empty($guid) ? $guid : $viewer, $action, 'sesadvancedcomment_replycomment', array('label' => 'post'));
      }else{
        $type = $parentComment->poster_type;
        $id = $parentComment->poster_id;
        $commentItem = Engine_Api::_()->getItem($type,$id);
        if($commentItem){
          $commentUser = $commentItem->getOwner();
          if($commentUser && $commentUser->getIdentity() != $viewer->getIdentity()){
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'sesadvancedactivity');
            $notifyApi->addNotification($commentUser, !empty($guid) ? $guid : $viewer, $action, 'sesadvancedcomment_replycomment', array('label' => 'post'));
            $viewer = $commentUser;
          }
        }
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['bodymention'], $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "reply" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
       if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id_reply = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type_reply = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply,$resource_id_reply);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid : $viewer, $viewer, 'sesadvancedcomment_taggedreply_people', array("commentLink" => $commentLink));
      }
      //Tagging People by status box
      $db->commit();

    }
    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_($e);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    // Assign message for json
    $this->view->status = true;
    $this->view->message = 'Comment posted';

     $commentContent = $this->commentsContent(array($comment),$action);

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));

  }

	 public function editAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));

    // Not post
     if( !$this->getRequest()->isPost() )
      {
       $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $resource_id = $this->_getParam('resource_id','');
      $resource_type = $this->_getParam('resource_type','');
      $comment_id = $this->view->comment_id = $this->_getParam('comment_id', null);
      $module = $this->_getParam('modulecomment','');
      if(!$resource_id)
        $comment = Engine_Api::_()->getItem($module.'_comment',$comment_id);
      else
        $comment = Engine_Api::_()->getItem('core_comment',$comment_id);

      //previous body
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $previousmatches);
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $this->_getParam('body',$_POST['body']);


      //Feeling Emojis Work
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
          $body = str_replace($bodyEmoji,$emojisCode,$body);
        }
      }
      //Feeling Emojis Work End
      $comment->body = $body;
      $comment->save();
      $sesAdv = false;
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
          $sesAdv = true;
      }else{
          if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
              $sesAdv = false;
          }
      }
      if($sesAdv) {
        if($comment->getType() == 'activity_comment') {
          $sesAdcCommentRow = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->rowExists($comment->getIdentity());
        } else if($comment->getType() == 'core_comment') {
          $sesAdcCommentRow = Engine_Api::_()->getDbTable('corecomments', 'sesadvancedactivity')->rowExists($comment->getIdentity());
        }

        $execute = false;
        $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
        if($file_id && $file_id != ''){
          $counter = 1;
          $file_ids = explode(',',$file_id);
          $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'sesadvancedcomment');
          $tableCommentFile->delete(array('comment_id =?'=>$comment->comment_id));
          foreach($file_ids as $file_id){
            if(!$file_id)
              continue;
            $file = $tableCommentFile->createRow();
            if(strpos($file_id,'_album_photo')){
              $file->type = 'album_photo';
              $file->file_id = str_replace('_album_photo','',$file_id);
            }else{
              $file->type = 'video';
              $file->file_id = str_replace('_video','',$file_id);
            }
            $file->comment_id = $comment->getIdentity();
            $file->save();
            if($counter == 1){
              $sesAdcCommentRow->file_id = $file_id;
              $sesAdcCommentRow->save();
            }
            $execute = true;
            $counter++;
          }
        }
        if(!$execute)
        {
          $sesAdcCommentRow->file_id = 0;
        }
        $emoji_id = $_POST['emoji_id'];
        if($emoji_id){
          $sesAdcCommentRow->emoji_id = $emoji_id;
          $sesAdcCommentRow->file_id = 0;
          $sesAdcCommentRow->save();
          $comment->body = '';
          $comment->save();
        }
        $comment->save();
        //fetch link from comment
        $regex = '/https?\:\/\/[^\" ]+/i';
        $string = $comment->body;
        preg_match($regex, $string, $matches);

        if(!empty($matches[0]) && $previousmatches != $matches){
          $viewer = Engine_Api::_()->user()->getViewer();
          $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
          if($preview){
            $sesAdcCommentRow->preview = $preview;
            $sesAdcCommentRow->save();
          }
        }else if(empty($matches[0]) && $sesAdcCommentRow->preview){
            $sesAdcCommentRow->preview = 0;
            $sesAdcCommentRow->save();
            $link = Engine_Api::_()->getItem('core_link',$sesAdcCommentRow->preview);
            $link->delete();
        }
      }
    $commentContent = $this->commentsContent(array($comment),$action);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));
  }
   public function previewCommentLink($url,$comment,$viewer){

         $contentLink = Engine_Api::_()->sesadvancedcomment()->getMetaTags($url);
         if(!empty($contentLink['title']) && !empty($contentLink['image'])){
            $image = $contentLink['image'];
            $title = $contentLink['title'];
            if(strpos($contentLink['image'],'http') === false){
              $parseUrl = parse_url($url);
              $image = $parseUrl['scheme'].'://'.$parseUrl['host'].'/'.ltrim($contentLink['image'],'/');
            }
         }
          $table = Engine_Api::_()->getDbtable('links', 'core');
          $link = $table->createRow();
          $data['uri'] = $url;
          $data['title'] = $title;
          $data['parent_type']  = $comment->getType();
          $data['parent_id']  = $comment->getIdentity();
          $data['search']  = 0;
          $data['photo_id']  = 0;
          $link->setFromArray($data);
          $link->owner_type = $viewer->getType();
          $link->owner_id = $viewer->getIdentity();
          $thumbnail = (string) @$image;
          $thumbnail_parsed = @parse_url($thumbnail);
          if( $thumbnail && $thumbnail_parsed ){
            $tmp_path = APPLICATION_PATH . '/temporary/link';
            $tmp_file = $tmp_path . '/' . md5($thumbnail);
              if( is_dir($tmp_path) ) {
                $src_fh = fopen($thumbnail, 'r');
                $tmp_fh = fopen($tmp_file, 'w');
                stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                fclose($src_fh);
                fclose($tmp_fh);
                if( ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
                  $ext = Engine_Image::image_type_to_extension($info[2]);
                  $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;
                  $image = Engine_Image::factory();
                  $image->open($tmp_file)
                    ->autoRotate()
                    ->resize(500, 500)
                    ->write($thumb_file)
                    ->destroy();
                  $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                    'parent_type' => $link->getType(),
                    'parent_id' => $link->getIdentity()
                  ));
                  $link->photo_id = $thumbFileRow->file_id;
                  @unlink($thumb_file);
                  @unlink($tmp_file);
                  $link->save();
                  return $link->getIdentity();
                }
              }
          }
        return false;
   }

}
