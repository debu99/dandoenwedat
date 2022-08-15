<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Search.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Transaction_Search extends Engine_Form {
	protected $_contentId;
	
	public function getContentId() {
    return $this->_contentId;
  }

  public function setContentId($content_id) {
    $this->_contentId = $content_id;
    return $this;
  }
  public function init() {
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
			$identity = $view->identity;
		if($this->_contentId){
			$identity = $this->_contentId;
    }
		$params = Engine_Api::_()->sescredit()->getWidgetParams($identity);
	
    $show_criterias = $params['show_option'];
    $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->setAttribs(array('id' => 'filter_form', 'class' => 'global_form_box'))->setMethod('GET');
    $this->setAction($view->url(array('module' => 'sescredit', 'controller' => 'index', 'action' => 'transaction'), 'default', true));
    $this->addElement('Select', 'point_type', array(
        'label' => 'Credit Point Type',
        'multiOptions' => Engine_Api::_()->sescredit()->getTypes(),
        'value' => 0,
    ));
    if (in_array('view', $show_criterias)) {
      $filterOptions = array();
      foreach ($params['criteria'] as $key => $viewOption) {
        if (!$viewerId && ($key == 1 || $key == 2 || $key == 3))
          continue;
        if (is_numeric($key))
          $columnValue = $viewOption;
        else
          $columnValue = $key;
        switch ($columnValue) {
          case '0':
            $value = 'All Credits';
            break;
          case 'today':
            $value = 'Today';
            break;
          case 'week':
            $value = 'This Week';
            break;
          case 'month':
            $value = 'This Month';
            break;
        }
        $filterOptions[$columnValue] = ucwords($value);
      }
      $this->addElement('Select', 'show', array(
          'label' => 'Show',
          'multiOptions' => $filterOptions,
          'value' => isset($params['default_view_search_type']) ? $params['default_view_search_type'] : '',
      ));
    }
    if (in_array('chooseDate', $show_criterias)) {
      if (isset($_GET['starttime']) && isset($_GET['endtime']))
        $dateRange = $_GET['starttime'] . '-' . $_GET['endtime'];
      else
        $dateRange = '';
      $this->addElement('Text', 'show_date_field', array(
          'label' => 'Choose Date Range',
          'autoComplete' => 'off',
          'value' => $dateRange,
      ));
    }
    $this->addElement('Hidden', 'page', array(
        'order' => 100
    ));
    $this->addElement('Hidden', 'tag', array(
        'order' => 101
    ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Search',
        'type' => 'submit'
    ));
  }

}
