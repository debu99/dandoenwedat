<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Widget_ProfileBlogsController extends Engine_Content_Widget_Abstract
{
  protected $_childCount;

  public function indexAction()
  {
    // Don't render if blog item not available
    if( !Engine_Api::_()->hasItemType('blog') ) {
      return $this->setNoRender();
    }

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $group = Engine_Api::_()->core()->getSubject('group');
    if( !$group->authorization()->isAllowed($viewer, 'view') ) {
      return $this->setNoRender();
    }

    // Just remove the title decorator
    $this->getElement()->removeDecorator('Title');
    
    $params['user_id'] = $viewer->getIdentity();
    $params['owner_id'] = $group->user_id;

    // Get paginator
    $this->view->paginator = $paginator = $group->getBlogsPaginator($params);
    $this->view->canAdd = $canAdd = $group->authorization()->isAllowed(null,  'blog') && Engine_Api::_()->authorization()->isAllowed('blog', null, 'create');

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Do not render if nothing to show and cannot upload
    if( $paginator->getTotalItemCount() <= 0 && !$canAdd ) {
      return $this->setNoRender();
    }
    
    // Add count to title if configured
    if( $this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0 ) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
  }

  public function getChildCount()
  {
    return $this->_childCount;
  }
}
