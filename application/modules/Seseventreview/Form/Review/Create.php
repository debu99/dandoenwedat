<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Form_Review_Create extends Engine_Form {
  protected $_defaultProfileId;
  public function getDefaultProfileId() {
    return $this->_defaultProfileId;
  }
  public function setDefaultProfileId($default_profile_id) {
    $this->_defaultProfileId = $default_profile_id;
    return $this;
  }
  public function init() {
		$this->setAttrib('id', 'sesevent_review_form');
		
		$type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type');
    if ($type) {
			$item = Engine_Api::_()->getItemByGuid($type);
    }else if(Engine_Api::_()->core()->getSubject()){
			$subject = Engine_Api::_()->core()->getSubject();
			$item =  Engine_Api::_()->getItem($subject->content_type,$subject->content_id);
		}
		

    $this->addElement('Dummy', 'review_star', array(
      'label' => 'Review',
      'decorators' => array(array('ViewScript', array(
      'viewScript' => '/application/modules/Seseventreview/views/scripts/review-rating.tpl',
			
      'class' => 'form element')))
    ));
	
		$this->addElement('Dummy', 'review_parameters', array(
      'label' => 'Review',
      'decorators' => array(array('ViewScript', array(
			'item'=>$item,
      'viewScript' => '/application/modules/Seseventreview/views/scripts/review-parameters.tpl',
      'class' => 'form element')))
    ));
    $this->addElement('Hidden', 'rate_value',array( 'order' => 878));
		$this->addElement('Hidden', 'category_id',array( 'order' => 879,'value'=>$item->category_id));
		$this->addElement('Hidden', 'subcat_id',array( 'order' => 880,'value'=>$item->subcat_id));
		$this->addElement('Hidden', 'subsubcat_id',array( 'order' => 881,'value'=>$item->subsubcat_id));
		$orderC = 881;
		if(isset($subject)){
			$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview')->getParameters(array('content_id'=>$subject->getIdentity(),'user_id'=>$subject->owner_id));
			foreach($reviewParameters as $val){
				$this->addElement('Hidden', 'review_parameter_value_'.$val['parameter_id'],array( 'order' => $orderC++,'value'=>$val['rating'],'class'=>"sesvent_review_values"));
			}
		}
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.title', 1)) {
      $this->addElement('Text', 'title', array(
          'label' => 'Review Title',
          'allowEmpty' => false,
          'required' => true,
          'maxlength' => "255",
      ));
    }
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.pros', 1)) {
      $this->addElement('Text', 'pros', array(
          'label' => 'Pros',
          'allowEmpty' => false,
          'required' => true,
          'maxlength' => "255",
      ));
    }
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.cons', 1)) {
      $this->addElement('Text', 'cons', array(
          'label' => 'Cons',
          'allowEmpty' => false,
          'required' => true,
          'maxlength' => "255",
      ));
    }
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.summary', 1)) {
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.tinymce', 1)) {
        $user = Engine_Api::_()->user()->getViewer();
        $user_level = Engine_Api::_()->user()->getViewer()->level_id;
        $allowed_html = '';
        $allowed_html = Engine_Api::_()->authorization()->getPermission($user_level, 'sesevent', 'auth_html');
        $editorOptions = array(
            'html' => (bool) $allowed_html,
        );
        $this->addElement('TinyMce', 'description', array(
            'label' => 'Description',
           // 'disableLoadDefaultDecorators' => true,
            'required' => true,
            'allowEmpty' => false,
            //'decorators' => array(
              //  'ViewHelper'
            //),
            'editorOptions' => $editorOptions,
        ));
      } else {
        $this->addElement('Textarea', 'description', array(
            'label' => 'Description',
            'allowEmpty' => false,
            'required' => true,
            'maxlength' => "300",
        ));
      }
    }
    $defaultProfileId = "0_0_" . $this->getDefaultProfileId();
    $customFields = new Seseventreview_Form_Custom_Fields(array(
        'item' => isset($subject) ? $subject : 'eventreview',
        'decorators' => array(
            'FormElements'
    )));
    $customFields->removeElement('submit');
    if ($customFields->getElement($defaultProfileId)) {
      $customFields->getElement($defaultProfileId)
              ->clearValidators()
              ->setRequired(false)
              ->setAllowEmpty(true);
    }
    $this->addSubForms(array(
        'fields' => $customFields
    ));
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.recommended', 1)) {
      $this->addElement('Radio', 'recommended', array(
          'label' => 'Recommended',
          'description' => 'Do you recommended this review to user?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => 1,
      ));
    }
    //Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Submit',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
		$tabId =  Engine_Api::_()->sesbasic()->pageTabIdOnPage('seseventreview.content-profile-reviews','sesevent_profile_index','widget');
		$tabData = '';
		if($tabId){
			$tabData = '/tab/'.$tabId->content_id;	
		}
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
				'href' =>  $item->getHref().$tabData,
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }
}