<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: SearchList.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_SearchList extends Engine_Form {

  public function init() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $front = Zend_Controller_Front::getInstance();
    $module = $front->getRequest()->getModuleName();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();

    $content_table = Engine_Api::_()->getDbtable('content', 'core');
    $params = $content_table->select()
            ->from($content_table->info('name'), array('params'))
            ->where('name = ?', 'sesevent.list-browse-search')
            ->query()
            ->fetchColumn();
    $params = Zend_Json_Decoder::decode($params);

    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box',
            ))
            ->setMethod('GET');

    if ($module == 'sesevent' && $controller == 'list' && $action == 'browse') {
      $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    } else {
      $this->setAction($view->url(array('module' => 'sesevent', 'controller' => 'list', 'action' => 'browse'), 'default', true));
    }
    parent::init();

    if (!empty($params['searchOptionsType']) && in_array('searchBox', $params['searchOptionsType'])) {
      $this->addElement('Text', 'title_name', array(
          'label' => 'Search List',
          'placeholder' => 'Enter List Name',
      ));
    }

    if (!empty($params['searchOptionsType']) && in_array('view', $params['searchOptionsType'])) {
      $this->addElement('Select', 'show', array(
          'label' => 'View',
          'multiOptions' => array(
              '1' => 'Everyone\'s Lists',
              '2' => 'Only My Friends\' Lists',
          ),
      ));
    }

    if (!empty($params['searchOptionsType']) && in_array('show', $params['searchOptionsType'])) {
      $this->addElement('Select', 'popularity', array(
          'label' => 'List By',
          'multiOptions' => array(
              '' => 'Select Popularity',
              'creation_date' => 'Most Recent',
              'featured' => "Only Featured",
							'sponsored' => "Only Sponsored",
              'view_count' => 'Most Viewed',
              'event_count' => 'Most Event List',
              'favourite_count' => 'Most Favorite',
							'like_count' => 'Most Liked',
          ),
      ));
    }
    $this->addElement('Hidden', 'user');

    //Element: execute
    $this->addElement('Button', 'execute', array(
        'label' => 'Search',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
  }

}