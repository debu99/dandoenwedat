<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Cover.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Form_CoverPhoto_Cover extends Engine_Form {

  public function init() {

    $this
      ->setTitle('Upload Group Cover Photo')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('id', 'cover_photo_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setAttrib('name', 'Upload a Cover Photo');

    $this->addElement('File', 'Filedata', array(
      'label' => 'Choose a cover photo.',
      'validators' => array(
          array('Extension', false, 'jpg,jpeg,png,gif'),
      ),
      'onchange' => 'javascript:uploadPhoto();'
    ));
  }
}
