<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: EditList.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_EditList extends Engine_Form {

  public function init() {
    parent::init();
    $this->setTitle('Edit List')
            ->setAttrib('id', 'form-upload-event')
            ->setAttrib('name', 'list_edit')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->addElement('Text', 'title', array(
        'label' => 'List Name',
        'placeholder' => 'Enter List Name',
        'maxlength' => '63',
        'filters' => array(
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '63')),
        )
    ));
    //Init descriptions
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
    $list_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('list_id');
    if ($list_id) {
      $photoId = Engine_Api::_()->getItem('sesevent_list', $list_id)->photo_id;
      if ($photoId) {
        $img_path = Engine_Api::_()->storage()->get($photoId, '')->getPhotoUrl();
        $path = $img_path;
        if (isset($path) && !empty($path)) {
          $this->addElement('Image', 'list_mainphoto_preview', array(
              'label' => 'List Photo Preview',
              'src' => $path,
							'onclick' =>'javascript:;',
              'width' => 100,
              'height' => 100,
          ));
        }
      }
    }
    if ($list_id) {
      $photoId = Engine_Api::_()->getItem('sesevent_list', $list_id)->photo_id;
      if ($photoId) {
        $this->addElement('Checkbox', 'remove_photo', array(
            'label' => 'Yes, remove list photo.'
        ));
      }
    }
		 //Privacy List View
    $this->addElement('Checkbox', 'is_private', array(
        'label' => Zend_Registry::get('Zend_Translate')->_("Do you want to make this list private?"),
        'value' => 0,
        'disableTranslator' => true
    ));
    //Init file uploader
    /*$fancyUpload = new Engine_Form_Element_FancyUpload('file');
    $fancyUpload->clearDecorators()
            ->addDecorator('FormFancyUpload')
            ->addDecorator('viewScript', array(
                'viewScript' => '_FancyUpload.tpl',
                'placement' => '',
    ));
    Engine_Form::addDefaultDecorators($fancyUpload);
    $this->addElement($fancyUpload);*/
    //Pre-fill form values
    $this->addElement('Hidden', 'list_id');
   // $this->removeElement('fancyuploadfileids');

    //Element: execute
    $this->addElement('Button', 'execute', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesevent_general', true),
        'onclick' => '',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    // DisplayGroup: buttons
    $this->addDisplayGroup(array(
        'execute',
        'cancel',
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper'
        ),
    ));
  }

}
