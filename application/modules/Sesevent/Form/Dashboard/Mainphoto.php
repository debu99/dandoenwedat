<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Mainphoto.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Dashboard_Mainphoto extends Engine_Form {

    public function init() {
		$translate = Zend_Registry::get('Zend_Translate');
    $sesevent = Engine_Api::_()->core()->getSubject();        
		$this->addElement('File', 'background', array(
          'onchange'=>'handleFileBackgroundUpload(this,event_main_photo_preview)',
      ));
      $this->background->addValidator('Extension', false, 'jpg,jpeg,png,gif');
      $this->addElement('Dummy', 'drag-drop-background', array(
        'content' => '<div id="dragandrophandlerbackground" class="sesevent_upload_dragdrop_content sesbasic_bxs"><div class="sesevent_upload_dragdrop_content_inner"><i class="fa fa-camera"></i><span class="sesevent_upload_dragdrop_content_txt">'.$translate->translate('Add photo for your event').'</span></div></div>'
      ));
      $this->addElement('Image', 'event_main_photo_preview', array(
            'width' => 300,
            'height' => 200,
            'value' => '1',
						'src'=>$sesevent->getPhotoUrl(),
            'disable' => true,
      ));      

		$settings = Engine_Api::_()->getApi('settings', 'core');
		$mantatoryCheck = $settings->getSetting('sesevent.eevecremainphoto', 1);
		
      $this->addElement('Dummy', 'removeimage', array(
        'content' => '<a class="icon_cancel form-link" id="removeimage1" style="display:none; "href="javascript:void(0);" onclick="removeImage();"><i class="far fa-trash"></i>'.$translate->translate('Change Photo').'</a>',
      ));
      $this->addElement('Hidden', 'removeimage2', array(
        'value' => 1,
        'order' => 10000000012,
      ));
			$this->addElement('Button', 'execute', array(
        'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
		
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $url = $view->url(array('action' => 'remove-mainphoto', 'event_id' => $sesevent->custom_url), "sesevent_dashboard", true);
        if ($sesevent->photo_id != 0 && !$mantatoryCheck) {
            $this->addElement('Button', 'cancel', array(
                'label' => 'Remove Photo',
								'prependText' => ' or ',
								 'class' => 'secondary_button',
								 'link' => true,
								 'href'=>$url,
                'onclick' => "removePhotoEvent('$url');",
                'decorators' => array(
                    'ViewHelper',
                ),
            ));
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

}