<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Slide_Create extends Engine_Form {

  public function init() {
 		$slide_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if ($slide_id)
      $slide = Engine_Api::_()->getItem('sesevent_slidephoto', $slide_id);
    $this->setTitle('Upload New Slide')->setDescription('');
		$this->setAttrib('enctype', 'multipart/form-data');
    /*$this->addElement('Text', 'title', array(
        'label' => 'Photo Title',
        'description' => 'Enter a title for this photo.',
        'allowEmpty' => false,
        'required' => false,
        'filters' => array(
            new Engine_Filter_Censor(),
            'StripTags',
            new Engine_Filter_StringLength(array('max' => '255'))
        ),
        'autofocus' => 'autofocus',
    ));*/

   
   /* $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';

    $editorOptions = array(
        'html' => (bool) $allowed_html,
    );

    if (!empty($upload_url)) {
      $editorOptions['plugins'] = array(
          'table', 'fullscreen', 'preview', 'paste',
          'code', 'textcolor', 'link'
      );

      $editorOptions['toolbar1'] = array(
          'undo', 'redo', 'removeformat', 'pastetext', '|', 'code',
          'link', 'fullscreen',
          'preview'
      );
    }

   
      $this->addElement('TinyMce', 'description', array(
          'label' => 'Description',
          'required' => false,
          'allowEmpty' => false,
          'editorOptions' => $editorOptions,
      ));*/
   if(isset($slide)){
			$allowed_empty = true;
			$required = false; 
		}else{
			$allowed_empty = false;
			$required = true; 	
		}
		$this->addElement('File', 'file', array(
        'allowEmpty' => $allowed_empty,
        'required' => $required,
        'label' => 'Choose Slide',
        'description' => 'Choose to photo for this slide.',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg');
     if (isset($slide) && $slide->photo_id) {
      $img_path = Engine_Api::_()->storage()->get($slide->photo_id, '')->getPhotoUrl();
      if (strpos($img_path, 'http') === FALSE) {
        $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      } else
        $path = $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'cat_icon_preview', array(
            'src' => $path,
            'width' => 100,
            'height' => 100,
        ));
      } 
    }
    $this->addElement('Select', 'active', array(
        'label' => 'Enabled',
        'description' => 'Do you want to enable this slide?',
        'multiOptions' => array(
            0 => 'No',
            1 => 'Yes'
        ),
        'value' => 1,
    ));


    $this->addElement('Button', 'submit', array(
        'label' => 'Upload',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'slides')),
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
