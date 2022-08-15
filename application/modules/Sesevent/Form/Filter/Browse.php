<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Browse.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Filter_Browse extends Engine_Form {

  protected $_locationSearch;
  protected $_kilometerMiles;
  protected $_friendsSearch;
  protected $_searchTitle;
	protected $_alphabetSearch;
  protected $_searchType;
	protected $_friendType;
  protected $_browseBy;
  protected $_categoriesSearch;
	protected $_stateSearch;
	protected $_citySearch;
	protected $_countrySearch;
	protected $_zipSearch;
	protected $_venueSearch;
	protected $_endDate;
	protected $_startDate;
  
	public function setAlphabetSearch($title) {
    $this->_alphabetSearch = $title;
    return $this;
  }
  
  public function getAlphabetSearch() {
    return $this->_alphabetSearch;
  }
	
	public function setStartDate($title) {
    $this->_startDate = $title;
    return $this;
  }
  
  public function getStartDate() {
    return $this->_startDate;
  }
	public function setEndDate($title) {
    $this->_endDate = $title;
    return $this;
  }
  
  public function getEndDate() {
    return $this->_endDate;
  }
	
	
  public function setLocationSearch($title) {
    $this->_locationSearch = $title;
    return $this;
  }
  
  public function getLocationSearch() {
    return $this->_locationSearch;
  }
  
  public function setKilometerMiles($title) {
    $this->_kilometerMiles = $title;
    return $this;
  }
  
  public function getKilometerMiles() {
    return $this->_kilometerMiles;
  }
  
  public function setFriendsSearch($title) {
    $this->_friendsSearch = $title;
    return $this;
  }
  public function getFriendsSearch() {
    return $this->_friendsSearch;
  }
	
	public function setFriendType($title) {
    $this->_friendType = $title;
    return $this;
  }

  public function getFriendType() {
    return $this->_friendType;
  }
	
  public function setSearchType($title) {
    $this->_searchType = $title;
    return $this;
  }

  public function getSearchType() {
    return $this->_searchType;
  }
  public function setSearchTitle($title) {
    $this->_searchTitle = $title;
    return $this;
  }

  public function getSearchTitle() {
    return $this->_searchTitle;
  }
  
  public function setBrowseBy($title) {
    $this->_browseBy = $title;
    return $this;
  }
  
  public function getBrowseBy() {
    return $this->_browseBy;
  }
  
  public function setCategoriesSearch($title) {
    $this->_categoriesSearch = $title;
    return $this;
  }
  
  public function getCategoriesSearch() {
    return $this->_categoriesSearch;
  }
  
	
	
	
	 public function setCountrySearch($title) {
    $this->_countrySearch = $title;
    return $this;
  }
  
  public function getCountrySearch() {
    return $this->_countrySearch;
  }
	 public function setStateSearch($title) {
    $this->_stateSearch = $title;
    return $this;
  }
  
  public function getStateSearch() {
    return $this->_stateSearch;
  }
	 public function setCitySearch($title) {
    $this->_citySearch = $title;
    return $this;
  }
  
  public function getCitySearch() {
    return $this->_citySearch;
  }
	 public function setZipSearch($title) {
    $this->_zipSearch = $title;
    return $this;
  }
  
  public function getZipSearch() {
    return $this->_zipSearch;
  }
	 public function setVenueSearch($title) {
    $this->_venueSearch = $title;
    return $this;
  }
  
  public function getVenueSearch() {
    return $this->_venueSearch;
  }
  public function init() {
  	
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$identity = $view->identity;
    $this
    ->setAttribs(array(
      'id' => 'filter_form',
      'class' => 'global_form_box',
    ))
    ->setMethod('GET')
    ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesevent', 'controller' => 'index', 'action' => 'browse')));

    if ($this->getSearchTitle() != 'no') {
      $this->addElement('Text', 'search_text', array(
												'label' => 'Search Events/Keyword:',
												'class'=>$this->getSearchTitle() == 'hide' ? $hideClass : '',
			
      ));
    }
    $hideClass = 'sesevent_widget_advsearch_hide_'.$identity;
    if ($this->getBrowseBy() != 'no') {
      $filterOptions = $this->_searchType;
      $arrayOptions = $filterOptions;
      $filterOptions = array();
      foreach ($arrayOptions as $key => $filterOption) {
					$value = str_replace(array('SP',''), array(' ',' '), $filterOption);
					$optionKey = Engine_Api::_()->sesevent()->getColumnName($value);
          if($value == "starttime"){
            $value = "Start Time";
          }
					$filterOptions[$optionKey] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
					$this->addElement('Select', 'order', array(
				'label' => 'Browse By:',
				'multiOptions' => $filterOptions,
				'class'=>$this->getBrowseBy() == 'hide' ? $hideClass : '',
      ));
    }
    
    if ($this->_friendsSearch != 'no') {
      $this->addElement('Select', 'view', array(
				'label' => 'View:',
				'multiOptions' => array(''),
				'class'=>$this->getFriendsSearch() == 'hide' ? $hideClass : '',
      ));
    }
    
    if ($this->_categoriesSearch != 'no') {
      $categories =  $categories = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategoriesAssoc(array('uncategories'=>true));
      if (count($categories) > 0) {
				 $categories = array('' => 'All Category') + $categories;
			$this->addElement('Select', 'category_id', array(
					'label' => 'Category:',
					'multiOptions' => $categories,
					'onchange' => 'showSubCategory(this.value);',
					'class'=>$this->_categoriesSearch == 'hide' ? $hideClass : '',
			));
			$this->addElement('Select', 'subcat_id', array(
						'label' => "Sub Category",
						'allowEmpty' => true,
						'required' => false,
						'class'=>$this->_categoriesSearch == 'hide' ? $hideClass : '',
						'multiOptions' => array('0' => 'Please select sub category'),
						'registerInArrayValidator' => false,
						'onchange' => "showSubSubCategory(this.value);"
			));
			//Add Element: Sub Sub Category
			$this->addElement('Select', 'subsubcat_id', array(
					'label' => "3rd Category",
					'allowEmpty' => true,
					'registerInArrayValidator' => false,
					'class'=>$this->_categoriesSearch == 'hide' ? $hideClass : '',
					'required' => false,
					'multiOptions' => array('0' => 'Please select 3rd category'),
			));
      }
    }
		if($this->getAlphabetSearch() != 'no'){
			$alphabetArray[] = '';
			foreach (range('A', 'Z') as $char) {
					$alphabetArray[strtolower($char)] =  $char ;
			}
				$this->addElement('Select', 'alphabet', array(
      'label' => 'Alphabet:',
			 'multiOptions' => $alphabetArray,
			 'class'=>$this->getAlphabetSearch() == 'hide' ? $hideClass : '',
      'filters' => array(
				new Engine_Filter_Censor(),
				new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
		}
    $restapi=Zend_Controller_Front::getInstance()->getRequest()->getParam( 'restApi', null );
    if($this->getStartDate() != 'no' && $restapi != 'Sesapi'){
				$this->addElement('Text', 'start_date', array(
      'label' => 'Start Date:',
			'class'=>$this->getStartDate() == 'hide' ? $hideClass : '',
      'filters' => array(
				new Engine_Filter_Censor(),
				new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
		}
		if($restapi == 'Sesapi'){
      $startdate = new Engine_Form_Element_Date('start_date');
      $startdate->setLabel("Start Date");
      $startdate->setAllowEmpty(true);
      $startdate->setRequired(false);
      $this->addElement($startdate);

      $enddate = new Engine_Form_Element_Date('end_date');
      $enddate->setLabel("End Date:");
      $enddate->setAllowEmpty(true);
      $enddate->setRequired(false);
      $this->addElement($enddate);
    }
		if($this->getEndDate() != 'no' && $restapi != 'Sesapi'){
			$this->addElement('Text', 'end_date', array(
      'label' => 'End Date:',
			'class'=>$this->getEndDate() == 'hide' ? $hideClass : '',
      'filters' => array(
				new Engine_Filter_Censor(),
				new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
		}
		$cookiedata = Engine_Api::_()->sesbasic()->getUserLocationBasedCookieData();
    if ($this->getLocationSearch() == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {
    
    $optionsenableglotion = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('optionsenableglotion','a:6:{i:0;s:7:"country";i:1;s:5:"state";i:2;s:4:"city";i:3;s:3:"zip";i:4;s:3:"lat";i:5;s:3:"lng";}'));
    
    $this->addElement('Text', 'location', array(
      'label' => 'Location:',
      'id' =>'locationSesList',
			'class'=>$this->getLocationSearch() == 'hide' ? $hideClass : '',
			'value'=>!empty($cookiedata['location']) ? $cookiedata['location'] : '',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));

    $this->addElement('Text', 'lat', array(
      'id' =>'latSesList',
			'style'=>'display:none',
			'value'=>!empty($cookiedata['lat']) ? $cookiedata['lat'] : '',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
    
    $this->addElement('Text', 'lng', array(
      'id' =>'lngSesList',
			'style'=>'display:none',
			'value'=>!empty($cookiedata['lng']) ? $cookiedata['lng'] : '',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
    
		if ($this->_kilometerMiles != 'no' && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) {
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.search.type',1) == 1)
				$searchType = 'Miles:';
			else
				$searchType = 'Kilometer:';
				//Add Element: Sub Category
				$this->addElement('Select', 'miles', array(
					'label' => $searchType,
					'allowEmpty' => true,
					'class'=>$this->getKilometerMiles() == 'hide' ? $hideClass : '',
					'required' => false,
					'multiOptions' => array('0'=>'','1'=>'1','5'=>'5','10'=>'10','20'=>'20','50'=>'50','100'=>'100','200'=>'200','500'=>'500','1000'=>'1000'),
					'value'=>1000,
					'registerInArrayValidator' => false,
				));
			}
		}
		
		if ($this->_countrySearch != 'no' && in_array('country', $optionsenableglotion)) {
			$locale = Zend_Registry::get('Zend_Translate')->getLocale();
		  $territories = Zend_Locale::getTranslationList('territory', $locale, 2);
		  asort($territories);
			$arrayTerr = array(''=>'');
			foreach($territories as $key=>$val)
				$arrayTerr[$val] = $val;
			//Add Element: country
			$this->addElement('Select', 'country', array(
					'label' => "Country:",
					'allowEmpty' => true,
					'class'=>$this->getCountrySearch() == 'hide' ? $hideClass : '',
					'registerInArrayValidator' => false,
					'required' => false,
					'multiOptions' => $arrayTerr,
			));
		}
		if ($this->_stateSearch != 'no' && in_array('state', $optionsenableglotion)) {
      $this->addElement('Text', 'state', array(
				'label' => 'State:',
				'class'=>$this->getStateSearch() == 'hide' ? $hideClass : '',
      ));
    }
    if ($this->_citySearch != 'no' && in_array('city', $optionsenableglotion)) {
      $this->addElement('Text', 'city', array(
				'label' => 'City:',
				'class'=>$this->getCitySearch() == 'hide' ? $hideClass : '',
      ));
    }
		if ($this->_zipSearch != 'no' && in_array('zip', $optionsenableglotion)) {
      $this->addElement('Text', 'zip', array(
				'label' => 'Zip:',
				'class'=>$this->getZipSearch() == 'hide' ? $hideClass : '',
      ));
    }
		if ($this->_venueSearch != 'no') {
      $this->addElement('Text', 'venue', array(
				'label' => 'Venue:',
				'class'=>$this->getVenueSearch() == 'hide' ? $hideClass : '',
      ));
    }
		
		$this->addElement('Cancel', 'advanced_options_search_'.$identity, array(
        'label' => 'Show Advanced Settings',
        'link' => true,
				'class'=>'active',
        'href' => 'javascript:;',
        'onclick' => 'return false;',
        'decorators' => array(
            'ViewHelper'
        )
    	));
		
      $this->addElement('Button', 'submit', array(
	  'label' => 'Search',
	  'type' => 'submit'
      ));
    $this->addElement('Dummy','loading-img-sesevent', array(
        'content' => '<img src="application/modules/Core/externals/images/loading.gif" alt="Loading" />',
   ));
  }

}
