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
class Sesevent_Form_Create extends Engine_Form {
    protected $_parent_type;
    protected $_parent_id;
    protected $_defaultProfileId;
    protected $_smoothboxType;
    protected $_fromApi;
    public function getDefaultProfileId() {
        return $this->_defaultProfileId;
    }
    public function setDefaultProfileId($default_profile_id) {
        $this->_defaultProfileId = $default_profile_id;
        return $this;
    }
    public function getSmoothboxType() {
            return $this->_smoothboxType;
    }
    public function setSmoothboxType($smoothboxType) {
        $this->_smoothboxType = $smoothboxType;
        return $this;
    }
    public function setParent_type($value) {
        $this->_parent_type = $value;
    }
    public function setParent_id($value) {
        $this->_parent_id = $value;
    }

    public function getFromApi() {
        return $this->_fromApi;
    }
    public function setFromApi($fromApi) {
        $this->_fromApi = $fromApi;
        return $this;
    }
    public function init() {

    if (Engine_Api::_()->core()->hasSubject('sesevent_event'))
      $event = Engine_Api::_()->core()->getSubject();
		if($this->getSmoothboxType())
			$hideClass = 'sesevent_hideelement_smoothbox';
		else
			$hideClass = '';
		$viewer = Engine_Api::_()->user()->getViewer();
    
    $translate = Zend_Registry::get('Zend_Translate');  

    $this->setTitle($translate->translate('Create New Event'))
            ->setAttrib('id', 'sesevent_create_form')
					  ->setAttrib('enctype', 'multipart/form-data')
            ->setMethod("POST")
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    
    if($this->getSmoothboxType())
      $this->setAttrib('class','global_form sesevent_smoothbox_create');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $moduleName = $request->getModuleName();
    $controllerName = $request->getControllerName();
    $actionName = $request->getActionName();
    // Title
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

        $timesChangesTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.limit.change.title', 2);
    $this->addElement('Text', 'title', array(
        'label' => $translate->translate('Event Name'),
        'autocomplete' => 'off',
        'allowEmpty' => false,
        'required' => true,
        'description' => sprintf( $translate->translate('You can change title of event maximum %s time.'),$timesChangesTitle),
        'validators' => array(
            array('NotEmpty', true),
            array('StringLength', false, array(1, 180)),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        ),
    ));

		$custom_url_value = isset($event->custom_url) ? $event->custom_url : (isset($_POST["custom_url"]) ? $_POST["custom_url"] : "");
		if($actionName !=  'edit'){
			// Custom Url
			$this->addElement('Dummy', 'custom_url_event', array(
					'label' => $translate->translate('Custom URL'),
					'content' => '<input type="text" name="custom_url" id="custom_url" value="' . $custom_url_value . '"><i class="fa fa-check" id="sesevent_custom_url_correct" style="display:none;"></i><i class="fa fa-times" id="sesevent_custom_url_wrong" style="display:none;"></i><span class="sesevent_check_availability_btn"><img src="application/modules/Core/externals/images/loading.gif" id="sesevent_custom_url_loading" alt="Loading" style="display:none;" /><button id="check_custom_url_availability" type="button" name="check_availability" >Check Availability</button></span>',
			));
		}
    if($actionName == 'create') {
	    if($settings->getSetting('sesevent.eevecredescription', 1))
		    $eevecredescription = true;
	    else
		    $eevecredescription = false;
    }else if($actionName == 'edit') {
	    $eevecredescription = true;
    }

		$descriptionMandatory= $settings->getSetting('sesevent.event.description', '1');
		if ($descriptionMandatory == 1) {
				$required = true;
				$allowEmpty = false;
			} else {
				$required = false;
				$allowEmpty = true;
    }
    
    if($eevecredescription) {
				 $this->addElement('Textarea', 'description', array(
	      'label' => $translate->translate('Event Description'),
				'allowEmpty'=>$allowEmpty,
        'required'=>$required,
        'class'=> $viewer->isAdmin()? 'tinymce': "",
        'editorOptions' => $editorOptions
	    ));
    }
     //Category
   // $categories = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategoriesAssoc(array('member_levels' => 1));
   $categories = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategoriesAssoc();
   $event_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('event_id', 0);
   if (count($categories) > 0) {
       $setting = Engine_Api::_()->getApi('settings', 'core');
       $categorieEnable = $settings->getSetting('sesevent.category.enable', '1');
       if ($categorieEnable == 1) {
         $required = true;
         $allowEmpty = false;
       } else {
         $required = false;
         $allowEmpty = true;
       }
     $categories = array('' => 'Choose Category') + $categories;
     $this->addElement('Select', 'category_id', array(
         'label' => $translate->translate('Category'),
         'multiOptions' => $categories,
         'allowEmpty' => $allowEmpty,
         'required' => $required,
         'onchange' => "showSubCategory(this.value);showFields(this.value,1,this.class,this.class,'resets');",
     ));
     //Add Element: 2nd-level Category
     $this->addElement('Select', 'subcat_id', array(
         'label' => "2nd-level Category",
         'allowEmpty' => true,
         'required' => false,
         'multiOptions' => array('0' => ''),
         'registerInArrayValidator' => false,
         'onchange' => "showSubSubCategory(this.value);showFields(this.value,1,this.class,this.class,'resets');"
     ));
     //Add Element: Sub Sub Category
     $this->addElement('Select', 'subsubcat_id', array(
         'label' => "3rd-level Category",
         'allowEmpty' => true,
         'registerInArrayValidator' => false,
         'required' => false,
         'multiOptions' => array('0' => ''),
         'onchange' => 'showFields(this.value,1);showFields(this.value,1,this.class,this.class,"resets");'
     ));

     if($actionName == 'create') {
	    if($settings->getSetting('sesevent.eevecremainphoto', 1))
		    $eevecremainphoto = true;
	    else
		    $eevecremainphoto = false;
    } elseif($actionName == 'edit') {
	    $eevecremainphoto = false;
    }
		if($eevecremainphoto) {
			$photoMandatory= $settings->getSetting('sesevent.mainphotomand', '1');
			 if ($photoMandatory == 1) {
          $required = true;
          $allowEmpty = false;
        } else {
          $required = false;
          $allowEmpty = true;
        }
			$requiredClass = $required ? ' requiredClass' : '';
			//Main Photo
			$this->addElement('File', 'photo', array(
					'label' => $translate->translate('Main Photo'),
					'onclick'=>'javascript:sesJqueryObject("#photo").val("")',
					'onchange'=>'handleFileBackgroundUpload(this,event_main_photo_preview)',
			));
			$this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg');

      $this->addElement('Dummy', 'photo-uploader', array(
				'label' => 'Main Photo',
        'content' => '<div id="dragandrophandlerbackground" class="sesevent_upload_dragdrop_content sesbasic_bxs'.$requiredClass.'"><div class="sesevent_upload_dragdrop_content_inner"><i class="fa fa-camera"></i><span class="sesevent_upload_dragdrop_content_txt">'.$translate->translate('Add photo for your event').'</span></div></div>'
      ));
      $this->addElement('Image', 'event_main_photo_preview', array(
            'width' => 300,
            'height' => 200,
            'value' => '1',
            'disable' => true,
      ));
      $this->addElement('Dummy', 'removeimage', array(
        'content' => '<a class="icon_cancel form-link" id="removeimage1" style="display:none; "href="javascript:void(0);" onclick="removeImage();"><i class="far fa-trash"></i>'.$translate->translate('Remove').'</a>',
      ));
      $this->addElement('Hidden', 'removeimage2', array(
        'value' => 1,
        'order' => 10000000012,
      ));
    }
  
    $this->addDisplayGroup(array('title','custom_url_event','description', 'category_id','subcat_id','subsubcat_id','photo-uploader','event_main_photo_preview','photo'), "what", array("legend"=> $translate->translate('What')));

    $this->addElement('text', 'min_participants', array(
      'label' => $translate->translate('Minimum Participants'),
      'required' => true,
      'allowEmpty' => false,
      'placeholder' => $translate->translate('minimum 2 participants'),
      'validators' => $viewer->isAdmin()? array(): array(
        array('GreaterThan', true, array(1)),
      )
    ));
    
    $this->addElement('text', 'max_participants', array(
      'label' => $translate->translate('Maximum Participants'),
      'required' => true,
      'allowEmpty' => false,
      'placeholder' => $viewer->isAdmin()? $translate->translate('maximum participants'): $translate->translate('maximum 30 participants'),
      'validators' => $viewer->isAdmin()? array(): array(
        array('LessThan', true, array(31)),
      )
    ));
    // https://framework.zend.com/manual/1.12/en/zend.validate.set.html
    $selectedAgeCategories = $actionName == "edit"? $event->getAgeCategoriesFromInterval(): array();
    $allAgeCategories = array(
      '18'=> '18-28',
      '29'=> '29-39',
      '40'=> '40-50',
      '51'=> '51-61',
      '62'=> '62-72',
      '73'=> '73-88',
    );

    $offsitehost = '';
    $offsitehostArr = Engine_Api::_()->getDbTable('hosts', 'sesevent')->getHosts(array('nolimit'=>true,'owner_id'=>$viewer->getIdentity(),'type'=>'offsite'));
     $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(count($offsitehostArr)){
      foreach($offsitehostArr as $key=>$valHost){
        $valHostData['id'] = $valHost['host_id'];
        $host = Engine_Api::_()->getItem('sesevent_host',$valHostData['id']);
        $valHostData['url'] = $valHost->getHref();
        $valHostData['photo'] = $view->itemPhoto($host, "thumb.icon");
        $valHostData['title'] = $view->string()->escapeJavascript($valHost['host_name']);
        $offsitehost .= "<option value='".$valHost['host_id']."' data-src='".json_encode($valHostData,JSON_HEX_QUOT | JSON_HEX_TAG)."'>".$valHost['host_name']."</option>";
      }
    }
    
    if($restapi != 'Sesapi'){
        $this->addElement('dummy', 'event_host', array(
          'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/Sesevent/views/scripts/_hostCreate.tpl',
          'class' => 'form element',
          'offsitehost'=>$offsitehost,
          'isEdit' =>$actionName != 'edit' ? 0 : 1,
          'host_id' => isset($event) ? $event->host : '',
            )))
      ));
    }
  
    if ($restapi == 'Sesapi'){
      $offerArray = array(''=>'');
      if(count($offsitehostArr)){
        foreach($offsitehostArr  as $key=>$valHost){
          $offerArray = array($valHost['host_id'] =>$view->string()->escapeJavascript($valHost['host_name']));
        }
      }else{
          $offerArray = array('0' =>$view->string()->escapeJavascript('No host created by you yet.'));
      }
      $tablename = Engine_Api::_()->getDbtable('users', 'user');
      $select = $tablename->select();
      $select->limit(30);
      $data = array(''=>'');
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
         $data[$friend->getIdentity()] = $friend->getTitle();
      }
      
      $hostarray = array(
        'choose_host' => 'Choose Host',
        'new' => 'Add New',
      );
  
      $hostarraya = array(
        '' => 'Please select type',
        'offsite' => 'Off-Site',
        'site' => 'On-Site',
        'myself' => 'Myself',
      );
  
      $this->addElement('select', 'choose_host', array(
        'label' => $translate->translate('Organizer Name'),
        'description' => 'Choose Organizer?',
        'multiOptions' => $hostarray,
        'required' => false,
        'value' => 'choose_host',
      ));
      
      $this->addElement('select', 'host_type', array(
        'label' => $translate->translate('Organizer Name'),
        'description' => 'Host Type',
        'multiOptions' => $hostarraya,
        'required' => false,
        'value' => 'myself',
      ));
      
      $this->addElement('select', 'event_host', array(
        'label' => 'Event Host',
        'description' => 'Event Host',
        'multiOptions' => $offerArray,
        'required' => false,
        'value' => '0',
      ));
      
      $this->addElement('select', 'selectonsitehost', array(
        'label' => 'Event Site Host',
        'description' => 'Event Host',
        'multiOptions' => $data,
        'required' => false,
        'value' => '',
      ));
      
      $this->addElement('text', 'host_name', array(
        'label' => 'Host Name',
        'required' => false,
        'value' => '',
      ));
      
      $this->addElement('text', 'host_email', array(
        'label' => 'Host Email',
        'required' => false,
        'value' => '',
      ));
      
      $this->addElement('text', 'host_phone', array(
        'label' => 'Host Phone',
        'required' => false,
        'value' => '',
      ));
      
      $this->addElement('text', 'host_description', array(
        'label' => 'Host Description',
        'required' => false,
        'value' => '',
      ));

      $this->addElement('file', 'host_photo', array(
        'label' => 'Host Photo',
        'required' => false,
        'value' => '',
      ));

      if($_GET['sesapi_platform'] != 1){
        $this->addElement('Checkbox', 'include_social_links', array(
            'label' => 'Include Social Links',
        ));
      }else{
        $this->addElement('select', 'include_social_links', array(
          'label' => 'Include Social Links',
          'description' => "",
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
          'required' => false,
          'value' => '0',
        ));
      }
      $this->addElement('text', 'facebook_url', array(
          'label' => 'Host Facebook URL',
          'required' => false,
          'value' => '',

      ));
     $this->addElement('text', 'twitter_url', array(
          'label' => 'Host Twitter URL',
          'required' => false,
          'value' => '',

      ));
      $this->addElement('text', 'website_url', array(
          'label' => 'Host Website URL',
          'required' => false,
          'value' => '',

      ));
     $this->addElement('text', 'linkdin_url', array(
          'label' => 'Host LinkedIn URL',
          'required' => false,
          'value' => '',

      ));

     $this->addElement('text', 'googleplus_url', array(
          'label' => 'Host Google Plus URL',
          'required' => false,
          'value' => '',
      ));
    }

    $keyAgeCategoryUser = $viewer->getAgeCategory();
    $selectedAgeCatoriesWithoutConstraint = $selectedAgeCategories;
    $selectedAgeCategories[$keyAgeCategoryUser] = $allAgeCategories[$keyAgeCategoryUser];

    $this->addElement('MultiCheckbox', 'age_categories', array(
      'label' => $translate->translate('Age Categories'),
      'multiOptions' => $allAgeCategories,
      'required' => false,
      'value' => $actionName == "edit"? ($viewer->isAdmin()? $selectedAgeCatoriesWithoutConstraint: $selectedAgeCategories) : $allAgeCategories,
      "disable"=> $viewer->isAdmin()? array(): array($keyAgeCategoryUser),
      "description" =>$viewer->isAdmin()? "": $translate->translate('Your own age category can\'t be unchecked'),
    ));

    $options = array(
      'Undistributed' => $translate->translate("Undistributed"),
      '50/50' => "50/50"
    );

    if($viewer->isAdmin()) {
      $options['Ladies only'] = $translate->translate("Ladies only");
      $options['Men only'] = $translate->translate("Men only");
    } else {
      $genderUser = $viewer->getGender()['label'];
      if( $genderUser === "Male") {
        $options['Men only'] = $translate->translate("Men only");
      } else if ($genderUser === "Female") {
        $options['Ladies only'] = $translate->translate("Ladies only");
      }
    }
    $this->addElement('Radio', 'gender_destribution', array(
      'label' => $translate->translate('Gender Distribution'),
      'multiOptions' =>$options,
      'required' => false,
      'value' => 'Undistributed'
    ));
    $this->addDisplayGroup(array('choose_host','host_type','event_host','selectonsitehost','host_name','host_email','host_phone','host_description','host_photo','include_social_links','facebook_url','twitter_url','website_url','linkdin_url','googleplus_url','min_participants', 'max_participants','age_categories', 'gender_destribution'), "who", array("legend"=> $translate->translate('Who')));


       if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {

           $locale = Zend_Registry::get('Zend_Translate')->getLocale();
           $territories = Zend_Locale::getTranslationList('territory', $locale, 2);
           asort($territories);
           $countrySelect = '';
           $countrySelected = '';
           if (count($territories)) {
               $countrySelect = '<option value="">Choose Country</option>';
               if (isset($event)) {
                   $itemlocation = Engine_Api::_()->getDbtable('locations', 'sesbasic')->getLocationData('sesevent_event', $event->getIdentity());
                   if ($itemlocation)
                       $countrySelected = $itemlocation->country;
               }
               foreach ($territories as $key => $valCon) {
                   if ($valCon == $countrySelected)
                       $countrySelect .= '<option value="' . $valCon . '" selected >' . $valCon . '</option>';
                   else
                       $countrySelect .= '<option value="' . $valCon . '" >' . $valCon . '</option>';
               }
           }
           $regionOptions = array();
           $regionOptions[''] = $translate->translate("Please select region");
           foreach (Engine_Api::_()->getDbtable('regions', 'user')->fetchAll() as $region) {
               $regionOptions[$region->getIdentity()] = $region->getTitle();
           }
           $this->addElement('dummy', 'event_location', array(
               'decorators' => array(array('ViewScript', array(
                   'viewScript' => 'application/modules/Sesevent/views/scripts/_location.tpl',
                   'class' => 'form element',
                   'event' => $event ?? '',
                   'countrySelect' => $countrySelect,
                   'itemlocation' => $itemlocation ?? '',
                   'regionOptions' => $regionOptions ?? '',
                   'regionValue' => $event->region_id ?? 0
               )))
           ));
       }

    if($actionName == 'create') {
	    if($settings->getSetting('sesevent.eevecretimezone', 1))
		    $eevecretimezone = true;
	    else
		    $eevecretimezone = false;
    } elseif($actionName == 'edit') {
	    $eevecretimezone = true;
    }

    $restapi=Zend_Controller_Front::getInstance()->getRequest()->getParam( 'restApi', null );
    if ($restapi == 'Sesapi'){

            $apitimezoneArray = array(
            'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
            'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
            'US/Central' => '(UTC-6) Central Time (US & Canada)',
            'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
            'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
            'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
            'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
            'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
            'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
            'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
            'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
            'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
            'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
            'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
            'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
            'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
            'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
            'Iran' => '(UTC+3:30) Tehran',
            'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
            'Asia/Kabul' => '(UTC+4:30) Kabul',
            'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
            'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
            'Asia/Katmandu' => '(UTC+5:45) Nepal',
            'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
            'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
            'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
            'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
            'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
            'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
            'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
            'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
        );
        $this->addElement('select', 'timezone', array(
            'label' => 'Timezone',
            'description' => 'Select Event Timezone?',
            'class'=>$hideClass,
            'multiOptions' => $apitimezoneArray,
            'required' => true,
            'order' => 6,
            'value' => '',

        ));

        if($_GET['sesapi_platform'] != 1){
//             Start time
            $startdate = new Engine_Form_Element_Date('start_date');
            $startdate->setLabel($translate->translate("Start Date"));
            $startdate->setAllowEmpty(false);
            $startdate->setRequired(true);
            $this->addElement($startdate);

            $start = new Engine_Form_Element_Date('start_time');
            $start->setLabel($translate->translate("Start Time"));
            $start->setAllowEmpty(false);
            $start->setRequired(true);
            $this->addElement($start);

            if($viewer->isAdmin()) {
              // End time
              $enddate = new Engine_Form_Element_Date('end_date');
              $enddate->setLabel($translate->translate("End Date"));
              $enddate->setAllowEmpty(false);
              $enddate->setRequired(true);
              $this->addElement($enddate);
            }

            $end = new Engine_Form_Element_Date('end_time');
            $end->setLabel($translate->translate("End Time"));
            $end->setAllowEmpty(false);
            $end->setRequired(true);
            $this->addElement($end);
        }else{
            $startdate = new Engine_Form_Element_Date('start_time');
            $startdate->setLabel($translate->translate("Start Date & Time"));
            $startdate->setAllowEmpty(false);
            $startdate->setRequired(true);
            $this->addElement($startdate);

            $start = new Engine_Form_Element_Date('end_time');
            $start->setLabel($translate->translate("End Date & Time"));
            $start->setAllowEmpty(false);
            $start->setRequired(true);
            $this->addElement($start);
        }

     }

    if(isset($event) && empty($_POST)){
			 // Convert and re-populate times
      $start = strtotime($event->starttime);
      $end = strtotime($event->endtime);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($event->timezone);
     	$start_date = date('m/d/Y',($start));
			$start_time = date('H:i',($start));
			$endDate = date('Y-m-d H:i:s', ($end));
			$end_date = date('m/d/Y',strtotime($endDate));
			$end_time = date('H:i',strtotime($endDate));
      date_default_timezone_set($oldTz);
		}else if(empty($_POST)){
			$startDate = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' + 1 day'));
			$start_date = date('m/d/Y',strtotime($startDate));
			$start_time = date('H:i',strtotime($startDate));
			$endDate = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' + 4 days'));
			$end_date = date('m/d/Y',strtotime($endDate));
			$end_time = date('H:i',strtotime($endDate));
		}else{
			$start_date = date('m/d/Y',strtotime($_POST['start_date']));
			$start_time = date('H:i',strtotime($_POST['start_time']));
			$end_date = date('m/d/Y',strtotime($_POST['end_date']));
			$end_time = date('H:i',strtotime($_POST['end_time']));
		}

		$this->addElement('dummy', 'event_custom_datetimes', array(
			'decorators' => array(array('ViewScript', array(
									'viewScript' => 'application/modules/Sesevent/views/scripts/_customdates.tpl',
									'class' => 'form element',
									'start_date'=>$start_date,
									'end_date'=>$end_date,
                  'start_time'=>$start_time,
									'end_time'=>$end_time,
									'start_time_check'=>isset($event) ? 0 : 1,
									'subject'=>isset($event) ? $event : '',
							)))
    ));
		if($eevecretimezone) {
			$timezoneArray = array(
            'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
            'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
            'US/Central' => '(UTC-6) Central Time (US & Canada)',
            'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
            'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
            'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
            'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
            'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
            'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
            'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
            'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
            'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
            'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
            'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
            'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
            'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
            'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
            'Iran' => '(UTC+3:30) Tehran',
            'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
            'Asia/Kabul' => '(UTC+4:30) Kabul',
            'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
            'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
            'Asia/Katmandu' => '(UTC+5:45) Nepal',
            'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
            'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
            'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
            'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
            'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
            'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
            'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
            'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
            'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
        );
		$this->addElement('dummy', 'event_timezone_popup', array(
			'decorators' => array(array('ViewScript', array(
									'viewScript' => 'application/modules/Sesevent/views/scripts/_timezone.tpl',
									'class' => 'form element',
									'timezone'=>$timezoneArray,
									'event'=>isset($event) ? $event : '',
									'viewer'=>$viewer,
							)))
      ));
    }

    $this->addDisplayGroup(array('event_custom_datetimes','event_timezone_popup'), "when", array("legend"=> $translate->translate("When")));

    $this->addElement('text', 'tel_host', array(
      'label' => $translate->translate('Tel. Host'),
      'required' => true,
      'placeholder' => $translate->translate('Telephone Number Host'),
      'description' => $translate->translate('This will only be visible to people who\'ve joined the event.'),
      'validators' => array(
        array('Regex', true, array('/^\+{0,1}[0-9]{8,20}$/')),
      )
    ));

    $this->addElement('text', 'meeting_time', array(
      'label' => $translate->translate('Meeting Time'),
      'required' => false,
      'placeholder' => $translate->translate('e.g. 12:00')
    ));

    $this->addElement('text', 'meeting_point', array(
      'label' => $translate->translate('Meeting Point'),
      'required' => false,
      'placeholder' => $translate->translate('e.g. In front of the cinema'),
      'description' => $translate->translate("Specify the meeting point in max 50 characters."),
      'validators' => array(
        array('StringLength', false, array("max" => 50)),
      )
    ));

       $this->addElement('text', 'meeting_url', array(
           'label' => $translate->translate('Meeting Url'),
           'required' => false,
           'placeholder' => $translate->translate('e.g. https://dandoenwedat.com'),
           'validators' => array(
               array('Regex', true, array('(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'))
           )
       ));
       $this->getElement('meeting_url')->getValidator('Regex')->setMessage('Meeting Point must be url when this event is online', 'regexNotMatch');
    $this->addDisplayGroup(array('tel_host','meeting_time','meeting_point','meeting_url'), "meeting", array("legend"=> $translate->translate("Meeting Point")));


    $this->addElement('Checkbox', 'is_additional_costs', array(
      'label' => $translate->translate('Additional Costs'),
      'onchange' => 'additionalCostsToggle();',
      'value' => 0,
    ));

    $amountValidator = new Zend_Validate_Float(array('locale' => $locale));
    $amountValidator->setMessage(
      "Please specify the amount ('%value% ) in the correct format.",
      Zend_Validate_Float::NOT_FLOAT
    );

    $this->addElement('Text', 'additional_costs_amount', array(
      'label' => $translate->translate('Amount'),
      'placeholder' => $translate->translate('0.00'),
      'class' => 'additional-costs-toggle',
      'validators' => array(
        $amountValidator
      )
    ));

    $this->addElement('TinyMce', 'additional_costs_description', array(
      'label' => $translate->translate('Additional Costs description'),
      'class'=>$viewer->isAdmin()? 'additional-costs-toggle': 'additional-costs-toggle',
      'editorOptions' => $editorOptions,
      'placeholder' => $translate->translate("e.g. Museum ticket, payable in cash"),
      'description' => $translate->translate("Specify what the costs are for and how it needs to be payed. Max. 100 characters."),
      'validators' => array(
        array('StringLength', false, array("max"=> 100)),
      )

    ));

    $this->addDisplayGroup(array('is_additional_costs', 'additional_costs_amount','additional_costs_description'), "costs", array("legend"=> "Costs"));

    $defaultProfileId = "0_0_" . $this->getDefaultProfileId();
    $customFields = new Sesbasic_Form_Custom_Fields(array(
          'item' => isset($event) ? $event : 'sesevent_event',
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
    }
    if($actionName == 'create') {
	    if($settings->getSetting('sesevent.eventcustom', 1))
		    $eventcustom = true;
	    else
		    $eventcustom = false;
    } elseif($actionName == 'edit') {
	    $eventcustom = true;
    }

    $optionalElementsForDisplayGroup = array();
		if($eventcustom && $viewer->isAdmin()) {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        $this->addElement('select', 'is_custom_term_condition', array(
          'label' => $translate->translate('Custom Term And Conditions'),
          'description' => "",
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
          'value' => '0'
        ));
      } else{
        // Custom Term And Condition
        $this->addElement('Checkbox', 'is_custom_term_condition', array(
            'label' => $translate->translate('Custom Term And Conditions'),
            'value' => 0
        ));
      }
			if($tinymce){
        //Overview
        $this->addElement('TinyMce', 'custom_term_condition', array(
            'label' => $translate->translate('Term And Conditions Description'),
            'class'=>'tinymce',
            'editorOptions' => $editorOptions,
        ));
			} else {
					 //Overview
	      $this->addElement('Textarea', 'custom_term_condition', array(
	        'label' =>  $translate->translate('Term And Conditions Description'),
	        'filters' => array(
	            'StripTags',
	            new Engine_Filter_Censor(),
	            new Engine_Filter_EnableLinks(),
	        ),
	      ));
      }
     $optionalElementsForDisplayGroup[] = 'is_custom_term_condition';
     $optionalElementsForDisplayGroup[] = 'custom_term_condition';
    }

    if($actionName == 'create') {
	    if($settings->getSetting('sesevent.eevecretags', 1))
		    $eevecretags = true;
	    else
		    $eevecretags = false;
    } elseif($actionName == 'edit') {
	    $eevecretags = true;
    }

    if($eevecretags) {
      //Tags
      $this->addElement('Text', 'tags', array(
        'label' => 'Tags (Keywords)',
        'autocomplete' => 'off',
        'description' => 'Separate tags with commas.',
        'filters' => array(
            new Engine_Filter_Censor(),
        ),
      ));
      $this->tags->getDecorator("Description")->setOption("placement", "append");
     $optionalElementsForDisplayGroup[] = 'tags';
    }

  if (Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'allow_levels')) {

        $levelOptions = array();
        $levelValues = array();
        foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level) {
            $levelOptions[$level->level_id] = $level->getTitle();
            $levelValues[] = $level->level_id;
        }
        // Select Member Levels
        $this->addElement('multiselect', 'levels', array(
            'label' => 'Member Levels',
            'multiOptions' => $levelOptions,
            'description' => 'Choose the Member Levels to which this Event will be displayed. (Note: Hold down the CTRL key to select or de-select specific member levels.)',
            'value' => $levelValues,
        ));

       $optionalElementsForDisplayGroup[] = 'levels';

  }

    if (Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'allow_network')) {
      $networkOptions = array();
      $networkValues = array();
      foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
        $networkOptions[$network->network_id] = $network->getTitle();
        $networkValues[] = $network->network_id;
      }

      // Select Networks
      $this->addElement('multiselect', 'networks', array(
          'label' => 'Networks',
          'multiOptions' => $networkOptions,
          'description' => 'Choose the Networks to which this Event will be displayed. (Note: Hold down the CTRL key to select or de-select specific networks.)',
          'value' => $networkValues,
      ));

     $optionalElementsForDisplayGroup[] = 'networks';

    }

    // Privacy
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_view');
    $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_comment');
    $photoOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_photo');
    $videoOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_video');
    $musicOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_music');
    $topicOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_topic');
    $ratingOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesevent_event', $viewer, 'auth_rating');

    if ($this->_parent_type == 'user') {
      $availableLabels = array(
          'everyone' => 'Everyone',
          'registered' => 'All Registered Members',
          'owner_network' => 'Friends and Networks',
          'owner_member_member' => 'Friends of Friends',
          'owner_member' => 'Friends Only',
          'member' => 'Event Guests Only',
          'owner' => 'Just Me'
      );
      $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
      $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
      $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));
      $videoOptions = array_intersect_key($availableLabels, array_flip($videoOptions));
      $musicOptions = array_intersect_key($availableLabels, array_flip($musicOptions));
      $topicOptions = array_intersect_key($availableLabels, array_flip($topicOptions));
      $ratingOptions = array_intersect_key($availableLabels, array_flip($ratingOptions));
    } else if ($this->_parent_type == 'group') {
      $availableLabels = array(
          'everyone' => 'Everyone',
          'registered' => 'All Registered Members',
          'parent_member' => 'Group Members',
          'member' => 'Event Guests Only',
          'owner' => 'Just Me',
      );
      $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
      $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
      $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));
    }


    // View
    if (!empty($viewOptions) && count($viewOptions) >= 1) {
      // Make a hidden field
      if (count($viewOptions) == 1) {
        $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_view', array(
            'label' =>  $translate->translate('View Privacy'),
            'description' => $translate->translate('Who may see this event?'),
						'class'=>$hideClass,
            'multiOptions' => $viewOptions,
            'value' => key($viewOptions),
        ));
        $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
      }
     $optionalElementsForDisplayGroup[] = 'auth_view';
    }
    // Comment
    if (!empty($commentOptions) && count($commentOptions) >= 1) {
      // Make a hidden field
      if (count($commentOptions) == 1) {
        $this->addElement('hidden', 'auth_comment', array('value' => key($commentOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_comment', array(
            'label' => $translate->translate('Comment Privacy'),
            'description' => $translate->translate('Who may post comments on this event?'),
						'class'=>$hideClass,
            'multiOptions' => $commentOptions,
            'value' => key($commentOptions),
        ));
        $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
      }
    }
   $optionalElementsForDisplayGroup[] = 'auth_comment';

    // Photo
    if (!empty($photoOptions) && count($photoOptions) >= 1) {
      // Make a hidden field
      if (count($photoOptions) == 1) {
        $this->addElement('hidden', 'auth_photo', array('value' => key($photoOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_photo', array(
            'label' => $translate->translate('Photo Upload Privacy'),
            'description' => $translate->translate('Who may upload photos to this event?'),
            'multiOptions' => $photoOptions,
						'class'=>$hideClass,
            'value' => key($photoOptions)
        ));
        $this->auth_photo->getDecorator('Description')->setOption('placement', 'append');
      }
     $optionalElementsForDisplayGroup[] = 'auth_photo';

    }

    //video
    if (!empty($videoOptions) && count($videoOptions) >= 1 && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')) {
      // Make a hidden field
      if (count($videoOptions) == 1) {
        $this->addElement('hidden', 'auth_video', array('value' => key($videoOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_video', array(
            'label' => $translate->translate('Video Upload Privacy'),
            'description' => $translate->translate('Who may upload videos to this event?'),
            'multiOptions' => $videoOptions,
						'class'=>$hideClass,
            'value' => key($videoOptions)
        ));
        $this->auth_video->getDecorator('Description')->setOption('placement', 'append');
       $optionalElementsForDisplayGroup[] = 'auth_video';
      }
    }

    //music
    if (!empty($musicOptions) && count($musicOptions) >= 1 && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventmusic')) {
      // Make a hidden field
      if (count($musicOptions) == 1) {
        $this->addElement('hidden', 'auth_music', array('value' => key($musicOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_music', array(
            'label' => $translate->translate('Music Upload Privacy'),
            'description' => $translate->translate('Who may upload musics to this event?'),
						'class'=>$hideClass,
            'multiOptions' => $musicOptions,
            'value' => key($musicOptions)
        ));
        $this->auth_music->getDecorator('Description')->setOption('placement', 'append');
       $optionalElementsForDisplayGroup[] = 'auth_music';
      }
    }
    //topic
    if (!empty($topicOptions) && count($topicOptions) >= 1) {
      // Make a hidden field
      if (count($topicOptions) == 1) {
        $this->addElement('hidden', 'auth_topic', array('value' => key($topicOptions)));
        // Make select box
      } else {
        $this->addElement('Select', 'auth_topic', array(
            'label' => $translate->translate('Topic Post Privacy'),
						'class'=>$hideClass,
            'description' => $translate->translate('Who may post topics to this event?'),
            'multiOptions' => $topicOptions,
            'value' => key($topicOptions)
        ));
        $this->auth_topic->getDecorator('Description')->setOption('placement', 'append');
       $optionalElementsForDisplayGroup[] = 'auth_topic';
      }
    }

    // Search
    $this->addElement('Checkbox', 'search', array(
        'label' => $translate->translate('People can search for this event'),
				'class'=>$hideClass,
        'value' => True
    ));
   $optionalElementsForDisplayGroup[] = 'search';
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')) {
			// Search
	    $this->addElement('Checkbox', 'is_sponsorship', array(
	        'label' => $translate->translate('Do you want to enable sponsorship for this event'),
					'class'=>$hideClass,
	        'value' => false
      ));
     $optionalElementsForDisplayGroup[] = 'is_sponsorship';
    }

    if($settings->getSetting('sesevent.rsvpevent', 1)) {
	    // Approval
	    $this->addElement('Checkbox', 'approval', array(
	        'label' => $translate->translate('People must be invited to RSVP for this event'),
					'class'=>$hideClass,
	        'value' => false,
      ));
     $optionalElementsForDisplayGroup[] = 'approval';
    }

    if($settings->getSetting('sesevent.inviteguest', 1)) {
	    // Invite
	    $this->addElement('Checkbox', 'auth_invite', array(
	        'label' => $translate->translate('Invited guests can invite other people as well'),
					'class'=>$hideClass,
	        'value' => true
      ));
     $optionalElementsForDisplayGroup[] = 'auth_invite';
    }

    if($actionName == 'create') {
	    if($settings->getSetting('sesevent.draft', 1))
		    $draft = true;
	    else
		    $draft = false;
    } elseif($actionName == 'edit') {
	    $draft = true;
    }

    if($draft) {
	    $this->addElement('Select', 'draft', array(
	        'label' => 'Status',
					'class'=> $hideClass,
	        'description' => $translate->translate('If this entry is published, it cannot be switched back to draft mode.'),
	        'multiOptions' => array( '1' => 'Published','0' => 'Saved As Draft',),
	        'value' => 1,
	    ));
       $this->draft->getDecorator('Description')->setOption('placement', 'append');
      $optionalElementsForDisplayGroup[] = 'draft';
    }

    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => $translate->translate('Save Changes'),
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
		if(!$this->getSmoothboxType()){
			$this->addElement('Cancel', 'cancel', array(
					'label' => $translate->translate('cancel'),
					'link' => true,
					'href' =>  Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesevent_general', true),
					'prependText' => ' or ',
					'decorators' => array(
							'ViewHelper',
					),
      ));
		} else {

			$this->addElement('Cancel', 'advanced_options', array(
        'label' => 'Show Advanced Settings',
        'link' => true,
				'class'=>'active',
        'href' => 'javascript:;',
        'onclick' => 'return false;',
        'decorators' => array(
            'ViewHelper'
        )
      ));
      $optionalElementsForDisplayGroup[] = 'advanced_options';

			$this->addElement('Dummy', 'brtag', array(
					'content' => '<span style="margin-top:5px;"></span>',
      ));
      $optionalElementsForDisplayGroup[] = 'brtag';

			$this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'href' => '',
				'prependText' => ' or ',
        'onclick' => 'sessmoothboxclose();',
        'decorators' => array(
            'ViewHelper'
        )
      ));
    }

    $this->addDisplayGroup($optionalElementsForDisplayGroup, 'additional_settings', array("legend"=> 'Additional Settings'));
    $this->addElement('Radio', 'has_agreed', array(
      'value' => '0',
      'multiOptions' => array(
        '1' => $translate->translate("Yes"),
        '0' => $translate->translate("No"),
      ),
      "label" => $translate->translate('<span class="terms">I agree with the <a  href="https://dandoenwedat.com/pages/event-regulations" target="_blank">event regulations</a> of dandoenwedat.</span>'),
    ));

    $this->has_agreed->addValidator(new Zend_Validate_Accepted());
    $this->has_agreed->getDecorator('Label')->setOptions(array('escape' => false));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }
}
