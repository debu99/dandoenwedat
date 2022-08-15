<?php

class Sesbasic_Form_Tellafriend extends Engine_Form {

 public $_error = array();

    public function init() {

        /*$item_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
				$item_type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type', null);
        $item = Engine_Api::_()->getItem($item_type, $item_id);
				$item_title = ucfirst(str_replace(array('sesevent_',''),'',$item->getType()));*/
        $titleShare = 'Tell a friend';
        $this->setTitle($titleShare )
                ->setAttrib('name', 'sesbasic_tellafriend');						    
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        if(!empty($viewer_id)) {
	  $this->addElement('Hidden', 'sender_name', array('value' => $viewer->displayname,'order'=>98));
	  $this->addElement('Hidden', 'sender_email', array('value' => $viewer->email));
        }
        else {
	  $this->addElement('Text', 'sender_name', array(
	      'label' => 'Your Name',
	      'allowEmpty' => false,
	      'required' => true,
	      'filters' => array(
		  'StripTags',
		  new Engine_Filter_Censor(),
		  new Engine_Filter_StringLength(array('max' => '63')),
	  )));

	  $this->addElement('Text', 'sender_email', array(
	      'label' => 'Your Email',
	      'allowEmpty' => false,
	      'required' => true,
	      'filters' => array(
		  'StripTags',
		  new Engine_Filter_Censor(),
		  new Engine_Filter_StringLength(array('max' => '63')),
	  )));
        }
        $this->addElement('Text', 'reciver_emails', array(
            'label' => 'To',
            'allowEmpty' => false,
            'required' => true,
           // 'description' => 'Separate multiple addresses with commas',
            'filters' => array(
                new Engine_Filter_Censor(),
            ),
        ));
       // $this->reciver_emails->getDecorator("Description")->setOption("placement", "append");

        $this->addElement('textarea', 'message', array(
            'label' => 'Add a note',
            'required' => true,
            'allowEmpty' => false,
            'attribs' => array('rows' => 4, 'cols' => 150),
            'value' => '',
            'filters' => array(
                'StripTags',
                new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
                new Engine_Filter_Censor(),
            ),
        ));

        $this->addElement('Button', 'submit', array(
        'type' => 'submit',
				'label' => 'Send',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    		));

				$this->addElement('Cancel', 'cancel', array(
						'label' => 'Cancel',
						'link' => true,
						'prependText' => ' or ',
						'onclick' => 'javascript:parent.Smoothbox.close()',
						'decorators' => array(
								'ViewHelper',
						),
				));
				$this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    }

}
