<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Widget_ProfileLinksController extends Engine_Content_Widget_Abstract
{
  protected $_childCount;

  public function indexAction()
  {
    // Don't render this if not authorized
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject();
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      return $this->setNoRender();
    }

    // Get paginator
    $table = Engine_Api::_()->getDbtable('links', 'core');
    $sesAdvLinktableName = Engine_Api::_()->getDbtable('links', 'sesadvancedactivity')->info('name');
    $select = $table->select()
        ->setIntegrityCheck(false)
        ->from($table->info('name'),array('*'))
        ->joinLeft($sesAdvLinktableName, $sesAdvLinktableName.'.core_link_id ='.$table->info('name').'.link_id', 'ses_aaf_gif')
        ->where('parent_type = ?', $subject->getType())
        ->where('parent_id = ?', $subject->getIdentity())
        ->where('search = ?', 1)
        ->order('creation_date DESC');

    $sesadvancedactivity_profilelink = Zend_Registry::isRegistered('sesadvancedactivity_profilelink') ? Zend_Registry::get('sesadvancedactivity_profilelink') : null;
    if(empty($sesadvancedactivity_profilelink)) {
      return $this->setNoRender();
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 8));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
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
