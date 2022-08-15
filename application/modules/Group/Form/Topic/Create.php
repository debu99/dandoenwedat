<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 10264 2014-06-06 22:08:42Z lucas $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Form_Topic_Create extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Post Discussion Topic')
      ->setAttrib('id', 'group_topic_create');

    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
      'validators' => array(
        array('StringLength', true, array(1, 64)),
      )
    ));
    $viewer = Engine_Api::_()->user()->getViewer();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $allowHtml = (bool) $settings->getSetting('group_html', 0);
    $allowBbcode = (bool) $settings->getSetting('group_bbcode', 0);
    
    if( !$allowHtml ) {
      $filter = new Engine_Filter_HtmlSpecialChars();
    } else {
      $filter = new Engine_Filter_Html();
      $filter->setForbiddenTags();
      $allowedTags = array_map('trim', explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'group', 'commentHtml')));
      $filter->setAllowedTags($allowedTags);
    }
    
    if( $allowHtml || $allowBbcode ) {
      $uploadUrl = "";
    
      if( Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') ) {
        $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('controller'=>'index', 'action'=>'upload-photo'), 'group_extended', true);

      }

      $editorOptions = array(
        'uploadUrl' => $uploadUrl,
        'bbcode' => $settings->getSetting('forum_bbcode', 0),
        'html' => $settings->getSetting('forum_html', 0)
      );

      $this->addElement('TinyMce', 'body', array(
        'disableLoadDefaultDecorators' => true,
        'editorOptions' => $editorOptions,
        'required' => true,
        'allowEmpty' => false,
        'decorators' => array('ViewHelper'),
        'filters' => array(
          $filter,
          new Engine_Filter_Censor(),
        ),
      ));
    } else {
      $this->addElement('Textarea', 'body', array(
        'label' => 'Message',
        'allowEmpty' => false,
        'required' => true,
        'filters' => array(
          new Engine_Filter_Censor(),
          new Engine_Filter_HtmlSpecialChars(),
          //new Engine_Filter_EnableLinks(),
        ),
      ));
    }

    $this->addElement('Checkbox', 'watch', array(
      'label' => 'Send me notifications when other members reply to this topic.',
      'value' => true,
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Post New Topic',
      'ignore' => true,
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'type' => 'link',
      'link' => true,
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
