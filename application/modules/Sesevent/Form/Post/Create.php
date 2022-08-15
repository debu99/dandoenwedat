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
class Sesevent_Form_Post_Create extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Reply')
            ->setAction(
                    Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble(array('action' => 'post', 'controller' => 'topic'), 'sesevent_extended', true)
    );

    $viewer = Engine_Api::_()->user()->getViewer();
    $settings = Engine_Api::_()->getApi('settings', 'core');

     //UPLOAD PHOTO URL
      $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'index', 'action' => "upload-image"), 'default', true);

      $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';

      $editorOptions = array(
          'upload_url' => $upload_url,
          'html' => (bool) $allowed_html,
      );

      if (!empty($upload_url)) {
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
		$this->addElement('TinyMce', 'body', array(
	      'label' => 'Body',
	      'maxlength' => '10000',
				 'editorOptions' => $editorOptions,
	    ));
    $this->addElement('Checkbox', 'watch', array(
        'label' => 'Send me notifications when other members reply to this topic.',
        'value' => '1',
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Post Reply',
        'ignore' => true,
        'type' => 'submit',
    ));

    $this->addElement('Hidden', 'topic_id', array(
        'order' => '920',
        'filters' => array(
            'Int'
        )
    ));

    $this->addElement('Hidden', 'ref');
  }

}
