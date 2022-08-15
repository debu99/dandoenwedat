<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filter.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Transaction_Filter extends Engine_Form {

  public function init() {
    $this->clearDecorators()
            ->addDecorator('FormElements')
            ->addDecorator('Form')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));
    $this->setAttribs(array('id' => 'filter_form', 'class' => 'global_form_box'))->setMethod('GET');
    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    if ($actionName != 'statstics') {
      $this->addElement('Text', 'owner_name', array(
          'label' => 'Owner Name',
          'placeholder' => 'Enter Owner Name',
          'decorators' => array(
              'ViewHelper',
              array('Label', array('tag' => null, 'placement' => 'PREPEND')),
              array('HtmlTag', array('tag' => 'div'))
          ),
      ));

      $this->addElement('Select', 'point_type', array(
          'label' => 'Credit Point Type',
          'multiOptions' => Engine_Api::_()->sescredit()->getTypes(),
          'value' => 0,
      ));
    }
    $this->addElement('Select', 'show', array(
        'label' => 'Show',
        'multiOptions' => array('0' => 'All Credits', '1' => 'Today', '2' => 'This Week', '3' => 'This Month'),
        'value' => 0,
    ));
    if (isset($_GET['starttime']) && isset($_GET['endtime']))
      $dateRange = $_GET['starttime'] . '-' . $_GET['endtime'];
    else
      $dateRange = '';
    $this->addElement('Text', 'show_date_field', array(
        'label' => 'Choose Date Range',
        'autoComplete' => 'off',
        'value' => $dateRange,
    ));
    $this->addElement('Button', 'search', array(
        'label' => 'Search',
        'type' => 'submit',
        'ignore' => true,
    ));
    $this->addElement('Hidden', 'order', array(
        'order' => 10004,
    ));
    $this->addElement('Hidden', 'order_direction', array(
        'order' => 10002,
    ));

    $this->addElement('Hidden', 'credit_id', array(
        'order' => 10003,
    ));
    //Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }

}
