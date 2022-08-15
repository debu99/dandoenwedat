<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: General.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_General extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "sesadvancedactivity_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('sesadvancedactivity.licensekey'),
    ));
    $this->getElement('sesadvancedactivity_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    if ($settings->getSetting('sesadvancedactivity.pluginactivated')) {

      // GitHub issue https://github.com/Vaibhav-Agarwal06/sedev/issues/118 @remove
      // $this->addElement('Radio', 'sesadvancedactivity_submitWithAjax', array(
      //     'label' => 'Status Update via AJAX',
      //     'description' => 'Do you want to update the status post via AJAX? [Choosing yes will enable members to update the status updates without refreshing the page.]',
      //     'multiOptions' => array(
      //         1 => 'Yes',
      //         0 => 'No'
      //     ),
      //     'value' => $settings->getSetting('sesadvancedactivity.submitWithAjax', 1),
      // ));

      $this->addElement('Radio', 'sesadvancedactivity_pintotop', array(
        'label' => 'Enable "Pin Posts to Top"',
        'description' => 'Do you want to enable members to Pin any post to the top of the feeds? (If you choose Yes, then the option to Pin any 1 post  at the top of the feed will show on Member Profile page and content profile page wherever the "SNS - Adavnced Activity Feeds" widget is placed.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.pintotop', 1),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_translate', array(
        'label' => 'Show Translate Option',
        'description' => 'Do you want to show Translate option in feeds? [If you choose Yes, then all the feeds will have a translate option.]',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick'=>'showLanguage(this.value);',
        'value' => $settings->getSetting('sesadvancedactivity.translate', 0),
      ));

      $localeObject = Zend_Registry::get('Locale');

      $languages = Zend_Locale::getTranslationList('language', $localeObject);
      $territories = Zend_Locale::getTranslationList('territory', $localeObject);

      $localeMultiOptions = array();
      foreach( array_keys(Zend_Locale::getLocaleList()) as $key ) {
        $languageName = null;
        if( !empty($languages[$key]) ) {
          $languageName = $languages[$key];
        } else {
          $tmpLocale = new Zend_Locale($key);
          $region = $tmpLocale->getRegion();
          $language = $tmpLocale->getLanguage();
          if( !empty($languages[$language]) && !empty($territories[$region]) ) {
            $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
          }
        }
        if( $languageName ) {
          if(strpos($key,'_') === false)
          $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
        }
      }

      $this->addElement('Select', 'sesadvancedactivity_language', array(
        'label' => 'Default Language',
        'description' => 'Choose the language in which you want to translate the feeds.',
        'multiOptions' => $localeMultiOptions,
        'value' => $settings->getSetting('sesadvancedactivity.language', 'en'),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_eneblelikecommentshare', array(
        'label' => 'Enable Feed Actions for Non-Loggedin Users',
        'description' => 'Do you want to enable feed action links - "Like, Comment and Share" for non-loggedin users?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.eneblelikecommentshare', 1),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_bigtext', array(
        'label' => 'Increase Feed Font Size',
        'description' => 'Do you want to increase the font size of the feed?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick'=>'showBigText(this.value);',
        'value' => $settings->getSetting('sesadvancedactivity.bigtext', 1),
      ));

      $this->addElement('Text', 'sesadvancedactivity_textlimit', array(
            'label' => 'Increased Character Limit',
            'description' => 'Enter the number of characters until which the feed font size is increased.',
            'validators' => array(
                array('Int', true),
                array('GreaterThan', true, array(0)),
            ),
            'value' => $settings->getSetting('sesadvancedactivity.textlimit', 120),
      ));

      $this->addElement('Text', 'sesadvancedactivity_fonttextsize', array(
            'label' => 'Increase Font Size',
            'description' => 'Enter the font size (in pixels) of increased characters in feed.',
            'validators' => array(
                array('Int', true),
                array('GreaterThan', true, array(0)),
            ),
            'value' => $settings->getSetting('sesadvancedactivity.fonttextsize', 24),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_enableonthisday', array(
        'label' => 'Enable On This Day Memory',
        'description' => 'Do you want to enable the "On This Day" memory on your website? If you choose "Yes", then users will find the On This Day link on the Member Home Page.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.enableonthisday', 1),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_showcompletepost', array(
        'label' => 'Show Complete Post',
        'description' => 'Do you want to show complete post as posted by the members of your website or give them show "more" / "less" option to show / hide the complete post?',
        'multiOptions' => array(
            1 => 'Yes, show complete post',
            0 => 'No, give "more" / "less" option'
        ),
        'onclick' => 'showcompletepost(this.value)',
        'value' => $settings->getSetting('sesadvancedactivity.showcompletepost', 0),
      ));
      $this->addElement('Text', 'sesadvancedactivity_characterlimit', array(
            'label' => 'Character Limit',
            'description' => 'Enter the character limit below after which users will see "more" option in the feed. When they will click on this "more" link, they will see complete post and "less" option to hide the post.',
            'validators' => array(
                array('Int', true),
                array('GreaterThan', true, array(0)),
            ),
            'value' => $settings->getSetting('sesadvancedactivity.characterlimit', 255),
      ));



      //ads code

      $this->addElement('Radio', 'sesadvancedactivity_adsenable', array(
        'label' => 'Enable Ad Campaigns',
        'description' => 'Do you want to enable the display of ads from SocialEngine Ad Campaign in activity feeds?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onchange'=>'ads(this.value)',
        'value' => $settings->getSetting('sesadvancedactivity.adsenable', 1),
      ));
      $this->getElement('sesadvancedactivity_adsenable')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

      //$adsSc = '<a href="admin/ads/create" target="_blank">Create New Campaign</a>';
      //$descriptionAds = sprintf('Do you want to enable ads in activity feed( %s )',$adsSc);
      $campaigns = Engine_Api::_()->getDbtable('adcampaigns', 'core')->fetchAll();

      if( count($campaigns) > 0 ) {
        // Element: adcampaign_id
        $this->addElement('Select', 'sesadvancedactivity_adcampaignid', array(
          'label' => 'Choose Ad Campaign',
          'description' => 'Choose an ad campaign.',
        ));
        foreach( $campaigns as $campaign ) {
          $this->sesadvancedactivity_adcampaignid->addMultiOption($campaign->adcampaign_id, $campaign->name);
        }
        $this->sesadvancedactivity_adcampaignid->setValue($settings->getSetting('sesadvancedactivity.adcampaignid', ''));
      } else{
        $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('You have not created any Ads Campaign yet, <a href="admin/ads/create" target="_blank">Create New Campaign</a>') . "</span></div>";
        //Add Element: Dummy
        $this->addElement('Dummy', 'sesadvancedactivity_noadcampaignid', array(
            'label' => 'Campaign Id',
            'description' => $description,
        ));
        $this->sesadvancedactivity_noadcampaignid->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      }


      $this->addElement('Radio', 'sesadvancedactivity_adsrepeatenable', array(
        'label' => 'Display Ads For Next Feed Count',
        'description' => 'Do you want to display the ads after each feed count cycle? (For example: if you have chosen 2 in above setting, then after a cycle of 2 feeds, ads will display in feeds.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onchange' => 'repeatAds(this.value)',
        'value' => $settings->getSetting('sesadvancedactivity.adsrepeatenable', 0),
      ));

      $this->addElement('Text', 'sesadvancedactivity_adsrepeattimes', array(
            'label' => "Show 'Ad' After Feed Count",
            'description' => 'Enter the number of feeds after which the ads will display.',
            'validators' => array(
                array('Int', true),
                array('GreaterThan', true, array(0)),
            ),
            'value' => $settings->getSetting('sesadvancedactivity.adsrepeattimes', 15),
      ));
      //end ads code

      if(Engine_Api::_()->sesbasic()->isModuleEnable('sespymk')) {
        $this->addElement('Radio', 'sesadvancedactivity_peopleymk', array(
          'label' => "Enable 'People You May Know' In Feeds",
          'description' => 'Do you want to enable the display of people (members), to the member viewing feed, may know? [If you choose Yes, then members will display in attractive carousel. The member carousel will only display when the member viewing feed will have at least 7 friends to be suggested.]',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'onchange'=>'peopleymk(this.value)',
          'value' => $settings->getSetting('sesadvancedactivity.peopleymk', 1),
        ));
        $this->getElement('sesadvancedactivity_peopleymk')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

//         $this->addElement('Radio', 'sesadvancedactivity_pymkrepeatenable', array(
//           'label' => 'Display Members For Next Feed Count',
//           'description' => 'Do you want to display the members after each feed count cycle? (For example: if you have chosen 2 in above setting, then after a cycle of 2 feeds, members will display in feeds.)',
//           'multiOptions' => array(
//               1 => 'Yes',
//               0 => 'No'
//           ),
//           'value' => $settings->getSetting('sesadvancedactivity.pymkrepeatenable', 0),
//         ));

        $this->addElement('Text', 'sesadvancedactivity_peopleymkrepeattimes', array(
              'label' => "Show 'Members' After Feed Count",
              'description' => 'Enter the number of feeds after which the People You May Know carousel will display.',
              'validators' => array(
                  array('Int', true),
                  array('GreaterThan', true, array(0)),
              ),
              'value' => $settings->getSetting('sesadvancedactivity.peopleymkrepeattimes', 5),
        ));
      }

      // Assign the composing values
      $composePartials = array('tagUseses'=>'Tag People (Members will be able to tag their friends to their status posts. Reodering will not work.)','smilesses'=>'Emoticons (Members will be able to add smileys and emoticons to their posts. Reodering will not work.)','locationses'=>'Check In (Members will be able to add their location to their posts. Reodering will not work.)','shedulepost'=>'Schedule Post (Members will be able to choose publish date and time for their posts. Reodering will not work.)','stickers' => 'Post Stickers');

      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity')) {
        $composePartials = array_merge(array('feelingssctivity' => "Feelings/Activity"), $composePartials);
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $composePartials = array_merge(array('emojisses' => "Emojis"), $composePartials);
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('elivestreaming')) {
        $composePartials = array_merge(array('elivestreaming' => "elive_sesadvancedactivity_settings_general"), $composePartials);
      }
      if(defined('SESFEEDGIFENABLED')) {
        $composePartials = array_merge(array('sesfeedgif' => "GIF"), $composePartials);
      }
      foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
        if( empty($data['composer']) ) {
          continue;
        }
        $title = $data['package']['title'];
        foreach( $data['composer'] as $type => $config ) {
          if($type == 'facebook' || $type == 'twitter' || $type == 'link' || $type == 'tag' || $type == 'sesadvancedactivityfacebook' || $type == 'sesadvancedactivitytwitter')
            continue;
          $addType = 'Add '.ucfirst(str_replace('ses','',$type));
          if($type == 'sesadvancedactivitylink'){
            $addType = 'Add Link';
            $titleA = 'Core';
          }
          else if($type == 'fileupload')
            $addType = 'Add File';
          else if($type == 'buysell')
            $addType = 'Sell Something';
          else if($type == 'sesgroup_photo')
            $addType = 'Add Group Photo';
          if(!empty($titleA)){
            $titleO = $title;
            $title = $titleA;
          }
          $composePartials[$type] = $addType.'('.$title.')';
          if(!empty($titleO)){
            $title = $titleO;
            $titleO = $titleA = '';
          }
        }
      }
       $composerSettings = $settings->getSetting('sesadvancedactivity.composeroptions', 1);
       $composerArray = array();
       foreach($composerSettings as $composerSetting){
         if(isset($composePartials[$composerSetting]))
           $composerArray[$composerSetting] = $composePartials[$composerSetting];
       }

      //get diff
      foreach($composePartials as $key =>$composePartial){
        if(!array_key_exists($key,$composerArray))  {
          $composerArray[$key] = $composePartial;
        }
      }

      $this->addElement('MultiCheckbox', 'sesadvancedactivity_statusboxsettings', array(
        'label' => "Status Box Options Settings",
      ));

      $this->addElement('MultiCheckbox', 'sesadvancedactivity_composeroptions', array(
        'label' => 'Status Box Attachments',
        'description' => 'Select the attachments which will be available in status box.',
        'multiOptions' => $composerArray,
        'escape' => false,
        'value' => $settings->getSetting('sesadvancedactivity.composeroptions', 1),
      ));

      $count = 0;
      $this->addElement('Select', 'sesadvancedactivity_attachment_count', array(
        'label' => 'Photo Count in Feed',
        'description' => 'Select from below how many photos do you want to show in feed. If members will upload more photos than this selected value, then remaining photo count will show on the last photo.',
        'multiOptions' => array(
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
            ++$count => $count,
        ),
        'value' => $settings->getSetting('sesadvancedactivity.attachment.count', 6),
      ));


        //default photos
//New File System Code
$default_photos_main = array('' => '');
$files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
foreach( $files as $file ) {
  $default_photos_main[$file->storage_path] = $file->name;
}
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';


      $this->addElement('Text', 'sesadvancedactivity_welcometabtext', array(
              'label' => "Welcome Tab Title",
              'description' => 'Enter the title of the Welcome tab. If you do not want to show title, then simply leave this field empty.',
              'value' => $settings->getSetting('sesadvancedactivity.welcometabtext', 'Welcome'),
      ));

      if (count($default_photos_main) > 0) {
			$default_photos = $default_photos_main;
      $this->addElement('Select', 'sesadvancedactivity_welcomeicon', array(
          'label' => 'Welcome Tab Icon',
          'description' => 'Choose icon of the Welcome tab. [Note: You can add a new icon from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to show icon.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesadvancedactivity.welcomeicon'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no icon for Welcome Tab. Icon to be chosen for Welcome Tab should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no Icon in the File & Media Manager for Welcome Tab. Please upload the Icon to be chosen for Welcome Tab from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesadvancedactivity_welcomeicon', array(
          'label' => 'Welcome Tab Icon',
          'description' => $description,
      ));
    }
    $this->sesadvancedactivity_welcomeicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));


      $this->addElement('Text', 'sesadvancedactivity_whatsnewtext', array(
              'label' => "What’s New Tab Title",
              'description' => 'Enter the title of the What’s New Tab. If you do not want to show title, then simply leave this field empty.',
              'value' => $settings->getSetting('sesadvancedactivity.whatsnewtext', 'What\'s New'),
        ));

     if (count($default_photos_main) > 0) {
			$default_photos = $default_photos_main;
      $this->addElement('Select', 'sesadvancedactivity_whatsnewicon', array(
          'label' => "What’s New Tab Icon",
          'description' => 'Choose icon of the What’s New Tab. [Note: You can add a new icon from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to show icon.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesadvancedactivity.whatsnewicon'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no icon for What\'s New . Icon to be chosen for What\'s New should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no Icon in the File & Media Manager for What\'s New Tab. Please upload the Icon to be chosen for What\'s New from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesadvancedactivity_whatsnewicon', array(
          'label' => 'Welcome Tab Icon',
          'description' => $description,
      ));
    }
    $this->sesadvancedactivity_whatsnewicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

      $this->addElement('Radio', 'sesadvancedactivity_linkedin_enable', array(
        'label' => 'Publish to LinkedIn',
        'description' => 'Do you want to enable members to choose to publish their posts on LinkedIn?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onchange'=>'linkedin(this.value)',
        'value' => $settings->getSetting('sesadvancedactivity.linkedin.enable', 1),
      ));
      $this->addElement('Text', 'sesadvancedactivity_linkedin_access', array(
        'label' => 'LinkedIn Access Key',
        'description' => '',
        'value' => $settings->getSetting('sesadvancedactivity.linkedin.access', ''),
      ));
      $this->addElement('Text', 'sesadvancedactivity_linkedin_secret', array(
        'label' => 'LinkedIn Secret Key',
        'description' => '',
        'value' => $settings->getSetting('sesadvancedactivity.linkedin.secret', ''),
      ));

//       $this->addElement('Radio', 'sesadvancedactivity_advancednotification', array(
//         'label' => 'Advanced Notifications in Mini Menu',
//         'description' => 'Do you want to enable the advanced notification in mini menu on your website?',
//         'multiOptions' => array(
//             1 => 'Yes',
//             0 => 'No'
//         ),
//         'value' => $settings->getSetting('sesadvancedactivity.advancednotification', 0),
//       ));

      $this->addElement('Radio', 'sesadvancedactivity_reportenable', array(
        'label' => 'Allow To Report',
        'description' => 'Do you want to enable members to report feeds on your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.reportenable', 1),
      ));

       $this->addElement('Radio', 'sesadvancedactivity_socialshare', array(
        'label' => 'Enable Social Share in Feeds',
        'description' => 'Do you want to allow members to share their feeds on other social networking websites?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.socialshare', 1),
      ));

      $this->addElement('MultiCheckbox', 'sesadvancedactivity_privacysettings', array(
        'label' => "Privacy Settings",
      ));

      $this->addElement('Radio', 'sesadvancedactivity_allowprivacysetting', array(
        'label' => 'Allow To Choose Privacy Settings',
        'description' => 'Do you want to allow members to choose privacy for their status updates while creating or editing the posts?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick'=>'hideShowPrivacySettings(this.value)',
        'value' => $settings->getSetting('sesadvancedactivity.allowprivacysetting', 1),
      ));

      $this->addElement('Radio', 'sesadvancedactivity_allowlistprivacy', array(
        'label' => 'Allow To Choose List Privacy',
        'description' => 'Do you want to allow members to choose to share their status posts with their friend lists?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.allowlistprivacy', 1),
      ));
      
      $this->addElement('Radio', 'sesadvancedactivity_groupfeed', array(
        'label' => 'Allow Group Feed',
        'description' => 'Do you want to allow grouping of feed?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sesadvancedactivity.groupfeed', 1),
      ));
      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate your plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }
}
