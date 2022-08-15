<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Edit.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Host_Edit extends Engine_Form
{
  public function init()
  {
    $user_level = Engine_Api::_()->user()->getViewer()->level_id;
    $user = Engine_Api::_()->user()->getViewer();
		$host = $host =  Engine_Api::_()->core()->getSubject();
    // Init form
    $this
      ->setTitle('Edit Host')
      ->setDescription('')
      ->setAttrib('id', 'edit-host')
      ->setAttrib('name', 'edit_host')
      ->setAttrib('enctype','multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;   
		 $settings = Engine_Api::_()->getApi('settings', 'core');
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
		if($settings->getSetting('sesevent.tinymce', 1))
		    $tinymce = true;
	    else
		    $tinymce = false;	
		
    $this->addElement('Text', 'host_name', array(
      'label' => 'Host Name',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		
		$this->addElement('Text', 'host_email', array(
      'label' => 'Host Email',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		$this->addElement('Text', 'host_phone', array(
      'label' => 'Host Phone',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		
		if($tinymce){
	    //Overview
	    $this->addElement('TinyMce', 'host_description', array(
	        'label' => 'Host Description',
					'class'=>'tinymce',
					 'editorOptions' => $editorOptions,
	    ));
			}else{
					 //Overview
	    $this->addElement('Textarea', 'host_description', array(
	        'label' => 'Host Description',
	        'filters' => array(
	            'StripTags',
	            new Engine_Filter_Censor(),
	            new Engine_Filter_EnableLinks(),
	        ),
	    ));
			}
		
		$this->addElement('File', 'host_photo', array(
        'label' => 'Host Photo',
        'description' => ''
    ));
    $this->host_photo->addValidator('Extension', false, 'jpg,jpeg,png,PNG,JPG,JPEG');

    if (isset($host) && $host->photo_id) {
      $img_path = Engine_Api::_()->storage()->get($host->photo_id, '')->getPhotoUrl();
      if (strpos($img_path, 'http') === FALSE) {
        $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      } else
        $path = $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'host_icon_preview', array(
            'src' => $path,
            'width' => 100,
            'height' => 100,
        ));
      }
      $this->addElement('Checkbox', 'remove_host_img', array(
          'label' => 'Yes, delete this host photo.'
      ));
    }
		
		$this->addElement('Text', 'facebook_url', array(
      'label' => 'Host Facebook Url',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		$this->addElement('Text', 'twitter_url', array(
      'label' => 'Host Twitter Url',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		
		$this->addElement('Text', 'website_url', array(
      'label' => 'Host Website Url',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		
		$this->addElement('Text', 'linkdin_url', array(
      'label' => 'Host linkedin Url',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));
		$this->addElement('Text', 'googleplus_url', array(
      'label' => 'Host Google Plus Url',
      'value' => '',
			'maxlength' => '255',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
      )
    ));		
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Edit Host',
      'type' => 'submit',
    ));
    
  }  
}