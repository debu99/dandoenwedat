<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Courses
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Level.php 2019-08-28 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 class Sesadvancedactivity_Form_Admin_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {
    
    parent::init();

    // My stuff
    $this
    ->setTitle('Member Level Settings')
    ->setDescription("These settings are applied as per member level. Start by selecting the member level you want to modify, then adjust the settings for that level from below.");

      if( !$this->isPublic() ) {

        $settings = Engine_Api::_()->getApi('settings', 'core');
        // Assign the composing values
        $composePartials = array('tagUseses'=>'Tag People (Members will be able to tag their friends to their status posts. Reodering will not work.)','smilesses'=>'Emoticons (Members will be able to add smileys and emoticons to their posts. Reodering will not work.)','locationses'=>'Check In (Members will be able to add their location to their posts. Reodering will not work.)','shedulepost'=>'Schedule Post (Members will be able to choose publish date and time for their posts. Reodering will not work.)','enablefeedbg' => 'Background Images in (Text Only Posts)');
        $allowSettings = array("feelingssctivity"=>"","locationses"=>"","shedulepost"=>"","enablefeedbg"=>"","fileupload"=>"","buysell"=>"","sesadvancedactivitytargetpost"=>"","sesfeedgif"=>"");

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
          $composePartials = array_merge(array('sesfeedgif' => "Post GIF"), $composePartials);
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
            else if($type == 'sesadvancedactivitytargetpost')
              $addType = 'Post Targeting';
            if(!empty($titleA)){
              $titleO = $title;
              $title = $titleA;
            }
            $composePartials[$type] = $addType.' ('.$title.')';
            if(!empty($titleO)){
              $title = $titleO;
              $titleO = $titleA = '';
            }
          }
        }

        //get diff
        foreach($composePartials as $key =>$composePartial){
          if(!array_key_exists($key,$composerArray))  {
            $composerArray[$key] = $composePartial;
          }
        }

      $this->addElement('MultiCheckbox', 'composeroptions', array(
        'label' => 'Status Box Attachments',
        'description' => 'Choose from below the attachments which you want to enable in the status update box on your website.',
        'multiOptions' => array_intersect_key($composerArray, $allowSettings),
        'escape' => false,
      ));

      $commentsOptions = array('stickers' => 'Post Stickers','gif' => "Post GIF");

      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji'))
        $commentsOptions['emojis'] = "Post Emojis";
      else
        $commentsOptions['emotions'] = "Post Emoticons";

      $this->addElement('MultiCheckbox', 'cmtattachement', array(
          'label' => 'Status Box & Comments Attachments',
          'description' => 'Choose from below the attachments which you want to enable in the status update box, comments, and replies on your website.',
          'multiOptions' => $commentsOptions,
          'escape' => false,
      ));
      $this->getElement('cmtattachement')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
      
        // Element: max
      $this->addElement('Text', 'sesfeedbg_max', array(
        'label' => 'Feed Background Images Count',
        'description' => 'Enter the number of background images to be shown in the status box to the users of this member level. (Maximum 12 background images are recommended.)',
        'validators' => array(
          array('Int', true),
          array('Between', false, array('min' => 2, 'max' => 12))
        ),
        'value' => 12,
      ));
    }
  }
}
