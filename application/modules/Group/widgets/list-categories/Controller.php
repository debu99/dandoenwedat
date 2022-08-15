<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9791 2016-12-08 20:41:41Z pamela $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2016 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Widget_ListCategoriesController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $this->view->categories = Engine_Api::_()->getApi('categories', 'core')
        ->getNavigation('group');
    if( count($this->view->categories) <= 1 ) {
      return $this->setNoRender();
    }
  }
}
