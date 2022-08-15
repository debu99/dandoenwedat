<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Page.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sespwa_Model_Page extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = array('search', 'title', 'description', 'keywords');

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    // identified
    if( !empty($this->url) ) {
      $id = str_replace(array('_', ' '), '-', $this->url);
    } else if( !empty($this->name) ) {
      $id = str_replace(array('_', ' '), '-', $this->name);
    } else {
      $id = $this->page_id;
    }

    $params = array_merge(array(
      'route' => 'default',
      'reset' => true,
      'module' => 'sespwa',
      'controller' => 'pages',
      'action' => $id
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }

  public function allowedToView($viewer)
  {
    // Check if empty
    if( empty($this->levels) ) {
      return true;
    }

    // Check if not array
    $allowedLevels = Zend_Json::decode($this->levels);
    if( !is_array($allowedLevels) ) {
      return true;
    }

    // set up current $viewer's level_id
    if( !empty($viewer->level_id) ) {
      $level_id = $viewer->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    }

    // Check if allowed
    if( in_array($level_id, $allowedLevels) ) {
      return true;
    } else {
      return false;
    }
  }

  public function isSearchable()
  {
    return $this->custom === 1 && parent::isSearchable();
  }
}
