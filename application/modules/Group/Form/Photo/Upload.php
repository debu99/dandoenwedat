<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Upload.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Form_Photo_Upload extends Engine_Form
{
  public function init()
  {
    // Init form
    $this
      ->setTitle('Add New Photos')
      ->setDescription('Choose photos on your computer to add to this album. (2MB maximum)')
      ->setAttrib('id', 'form-upload')
      ->setAttrib('class', 'global_form group_form_upload')
      ->setAttrib('name', 'albums_create')
      ->setAttrib('enctype','multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    
    $this->addElement('HTMLUpload', 'file', [
      'form' => '#form-upload',
      'multi' => true,
      'url' => $this->getView()->url([
        'controller' => 'photo',
        'action' => 'upload-photo'
      ], 'group_extended'),
      'accept' => 'image/*',
    ]);
    /*
    $this->addElement('Hidden', 'file', array(
      'decorators' => array(
        array('ViewScript', array(
          'viewScript' => '_formUpload.tpl',
        ))
      )
    ));
    self::addDefaultDecorators($this->file);
    */
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Photos',
      'type' => 'submit',
    ));
  }
}