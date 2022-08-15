<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Append.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Append extends Engine_Form {

  public function init() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $listCount = Engine_Api::_()->getDbtable('lists', 'sesevent')->getListsCount(array('viewer_id' => $viewer->getIdentity(), 'column_name' => array('list_id', 'title')));
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesevent_event', 'addlist_maxevent');
    $this->setTitle('Add Event To List')
            ->setAttrib('id', 'form-list-append')
            ->setAttrib('name', 'list_add')
            ->setAction($_SERVER['REQUEST_URI']);
    $lists = array();
    if ($quota > count($listCount) || $quota == 0)
      $lists[0] = Zend_Registry::get('Zend_Translate')->_('Create New List');
    
    if ($quota > count($listCount) || $quota == 0) {
			$this->addElement('Select', 'list_id', array(
        'label' => 'Choose List',
        'multiOptions' => $lists,
        'onchange' => "updateTextFields()",
    ));
      $this->addElement('Text', 'title', array(
          'label' => 'List Name',
          'placeholder' => 'Enter List Name',
          'style' => '',
          'filters' => array(
              new Engine_Filter_Censor(),
          ),
      ));
      $this->addElement('Textarea', 'description', array(
          'label' => 'List Description',
          'placeholder' => 'Enter List Description',
          'maxlength' => '300',
          'filters' => array(
              'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '300')),
              new Engine_Filter_EnableLinks(),
          ),
      ));
      //Init album art
      $this->addElement('File', 'mainphoto', array(
          'label' => 'List Photo',
      ));
      $this->mainphoto->addValidator('Extension', false, 'jpg,png,gif,jpeg');
			  //Privacy List View
    $this->addElement('Checkbox', 'is_private', array(
        'label' => Zend_Registry::get('Zend_Translate')->_("Do you want to make this list private?"),
        'value' => 0,
        'disableTranslator' => true
    ));
    //Element: execute
    $this->addElement('Button', 'execute', array(
        'label' => 'Add Lists',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
		$orCondition = ' or ';
    } else {
			$orCondition = '';
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("You have already created the maximum number of lists allowed. If you would like to create a new list, please delete an old one first. Currently, you can only add events in your existing lists.") . "</span></div>";
      $this->addElement('Dummy', 'dummy', array(
          'description' => $description,
      ));
      $this->dummy->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
  
    //Element: cancel
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => $orCondition,
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    //DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array('decorators' => array('FormElements', 'DivDivDivWrapper')));
  }

}
