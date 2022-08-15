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
class Sesevent_Form_Topic_Create extends Engine_Form {

  public function init() {
    $this
            ->setTitle('Post Discussion Topic')
            ->setAttrib('id', 'sesevent_topic_create');

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

    $allowHtml = (bool) $settings->getSetting('sesevent_html', 0);
    $allowBbcode = (bool) $settings->getSetting('sesevent_bbcode', 0);

    if (!$allowHtml) {
      $filter = new Engine_Filter_HtmlSpecialChars();
    } else {
      $filter = new Engine_Filter_Html();
      $filter->setForbiddenTags();
      $allowed_tags = array_map('trim', explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesevent', 'commentHtml')));
      $filter->setAllowedTags($allowed_tags);
    }

    if ($allowHtml || $allowBbcode) {
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
