<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_ListBrowseSearchController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $searchOptionsType = $this->_getParam('searchOptionsType', array('searchBox', 'view', 'show'));
    if (empty($searchOptionsType))
      return $this->setNoRender();
    $this->view->form = $formFilter = new Sesevent_Form_SearchList();
    if ($formFilter->isValid($requestParams))
      $values = $formFilter->getValues();
    else
      $values = array();
    $this->view->formValues = array_filter($values);
    if (@$values['show'] == 2 && $viewer->getIdentity())
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    unset($values['show']);
  }

}
