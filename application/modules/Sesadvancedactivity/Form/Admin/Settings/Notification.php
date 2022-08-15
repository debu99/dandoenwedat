<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Notification.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_Notification extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Intelligent Automatic Notification Settings')
            ->setDescription('Here, you can configure the pre build intelligent automatic notification settings which will intelligently display in the feeds.');
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		
    //UPLOAD PHOTO URL
      $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'index', 'action' => "upload-image"), 'default', true);

      $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';

      $editorOptions = array(
          'upload_url' => $upload_url,
          'html' => (bool) $allowed_html,
      );

      if (!empty($upload_url)) {
				$editorOptions['editor_selector'] = 'tinymce';
				$editorOptions['mode'] = 'specific_textareas';
        $editorOptions['plugins'] = array(
            'table', 'fullscreen', 'media', 'preview', 'paste',
            'code', 'image', 'textcolor', 'jbimages', 'link'
        );

        $editorOptions['toolbar1'] = array(
            'undo', 'redo', 'removeformat', 'pastetext', '|', 'code',
            'media', 'image', 'jbimages', 'link', 'fullscreen',
            'preview'
        );
      }
    
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    $baseURL = $protocol.$_SERVER['HTTP_HOST'];
    $baseURLStatic = $baseURL.$view->layout()->staticBaseUrl;;
    
    $this->addElement('Radio', 'sesadvancedactivity_notificationday', array(
      'label' => 'Greeting Notification',
      'description' => 'Do you want to enable the greeting notification on the feed? [If you choose "Yes", then members will see "Good Morning",  "Good Afternoon" or "Good Evening" as per the time they first view their feed in a day for 1 time.]',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'onchange'=>'dayText(this.value);',
      'value' => $settings->getSetting('sesadvancedactivity.notificationday', 1),
    ));
    $design = '<div style="padding:20px 10px;font-size:17px;font-weight:bold;position:relative;">
    <span style="color:#f8b62d;">
    	<img src="'.$baseURL.'/favicon.ico" alt="favicon" class="favicon" style="margin-right:10px;margin-top:3px;float:left;" /> 
    	NOTIFICATION_TIME NOTIFICATION_USER!
    </span>
    <span style="	position:absolute;right:10px;bottom:5px;top:5px;text-align:right;">
      <img src="NOTIFICATION_IMAGE" alt="Notification Image" style="max-height:100%;" />
    </span>
	</div>';
    $this->addElement('TinyMce', 'sesadvancedactivity_notificationdaytext', array(
      'label' => 'Greeting Message',
      'description' => 'Configure the notification for greeting "Good Morning",  "Good Afternoon" or "Good Evening" for the day.',
      'class'=>'tinymce',
      'allowEmpty'=>false,
      'required'=>true,
		  'editorOptions' => $editorOptions,
      'value' => $settings->getSetting('sesadvancedactivity.notificationdaytext', $design),
    ));
    
    $this->addElement('Radio', 'sesadvancedactivity_dobadd', array(
      'label' => 'Remind To Add Date of Birth',
      'description' => 'Do you want to allow to remind members to add their date of births, if they have not already added?',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'value' => $settings->getSetting('sesadvancedactivity.dobadd', 1),
    ));
    
    $this->addElement('Radio', 'sesadvancedactivity_notificationbirthday', array(
      'label' => 'Enable Birthday Wish',
      'description' => 'Do you want to enable the birthday wish to the member viewing the feed? [If you choose "Yes", then if a member view feed on his birthday, then he will see this message]',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'onclick'=>"notificationbday(this.value)",
      'value' => $settings->getSetting('sesadvancedactivity.notificationbirthday', 1),
    ));
    $design = '<div style="background-image:url('.$baseURLStatic.'application/modules/Sesadvancedactivity/externals/images/bday-bg.png);background-position:center top;background-repeat:no-repeat;padding:90px 20px 30px;text-align:center;">
    <span style="font-family:"ostrich_sansheavy";font-size:40px;color:#ffb700;font-weight:bold;display:block;">
      Happy Birthday <span style="font-family:"ostrich_sansheavy";color:#d13562;">BIRTHDAY_USER_NAME</span>!
    </span>
    <span style="font-size:17px;display:block;">Thanks for being here, enjoy your day now!!</span>
    <span style="display:block;">
      <img src="'.$baseURLStatic.'application/modules/Sesadvancedactivity/externals/images/bday-cake.png" alt="" style="max-width:80px;margin-top:30px;" />
    </span>
	</div>
  ';
    $this->addElement('TinyMce', 'sesadvancedactivity_notificationbirthdaytext', array(
      'label' => 'Birthday Wish Message',
      'description' => 'Configure the message for birthday wish to members.',
      'class'=>'tinymce',
      'allowEmpty'=>false,
      'required'=>true,
		  'editorOptions' => $editorOptions,
      'value' => $settings->getSetting('sesadvancedactivity.notificationbirthdaytext', $design),
    ));
    
    
    
    
    
    
    
    
    
    
    
    $this->addElement('Radio', 'sesadvancedactivity_friendnotificationbirthday', array(
      'label' => 'Friend’s Birthday Reminder Notification',
      'description' => 'Do you want to show members this notification on the birthday of their friends?',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'onclick'=>'birthdayText(this.value)',
      'value' => $settings->getSetting('sesadvancedactivity.friendnotificationbirthday', 1),
    ));
    $design = '<div style="position:relative;"><img src="'.$baseURLStatic.'/application/modules/Sesadvancedactivity/externals/images/sendwish-bg.png" alt="" style="width:100%;" /><img src="'.$baseURL.'/favicon.ico" alt="favicon" class="favicon" style="position:absolute;left:10px;top:10px;width:auto;" /><a href="javascript:;" class="fas fa-times" style="position:absolute;right:10px;top:8px;color:#fff !important;text-decoration:none !important;line-height:0;font-size:15px;text-shadow:0 0 5px rgba(0, 0, 0, .5);"></a></div><div class="sesact_sendwish_cont sesbasic_clearfix"><div class="sesact_sendwish_photo" style="margin:-100px auto 30px;height:150px;width:150px;border-radius:5px;overflow:hidden;box-shadow:0 0 15px rgba(0, 0, 0, .1);position:relative;">BIRTHDAY_USER_IMAGE</div><div style="font-size: 17px;padding: 0 30px;font-weight: bold;margin-bottom: 5px;text-align:center;">It\'s BIRTHDAY_USER_NAME birthday!</div><div style="margin-bottom: 20px;padding: 0 30px;text-align:center;">We just think you would not want to miss a chance to wish your friend a Very Happy Birthday!</div></div>'; 
    $this->addElement('TinyMce', 'sesadvancedactivity_friendnotificationbirthdaytext', array(
      'label' => 'Birthday Notification Message',
      'description' => 'Configure the notification for friend’s birthday reminder.',
      'class'=>'tinymce',
      'allowEmpty'=>false,
      'required'=>true,
		  'editorOptions' => $editorOptions,
      'value' => $settings->getSetting('sesadvancedactivity.friendnotificationbirthdaytext', $design),
    ));
    

    $this->addElement('Radio', 'sesadvancedactivity_notificationfriends', array(
      'label' => 'Find Friends',
      'description' => 'Do you want to enable members to find their friends from their feeds?',
      'onchange' => 'showDays(this.value)',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'value' => $settings->getSetting('sesadvancedactivity.notificationfriends', 1),
    ));
    
    $this->addElement('Text', 'sesadvancedactivity_notificationfriendsdays', array(
      'label' => 'Friends Count',
      'description' => 'Enter the number of friends count until which members will see "Find Friend" section.',
      'validators' => array(
          array('Int', true),
      ),
      'value' => $settings->getSetting('sesadvancedactivity.notificationfriendsdays', 30),
    ));

    

    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}