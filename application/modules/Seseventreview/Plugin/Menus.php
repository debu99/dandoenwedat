<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Menus.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Plugin_Menus {

  public function onMenuInitialize_SeseventreviewProfileEdit() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $review = Engine_Api::_()->core()->getSubject();
    $view = Zend_Registry::isRegistered('Zend_View') ?Zend_Registry::get('Zend_View') : null;

    if (!(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 0)))
      return false;

    if (!$viewer->getIdentity())
      return false;


		if (!$review->authorization()->isAllowed($viewer, 'edit'))
    	  return false;

    return array(
        'label' => $view->translate('Edit Review'),
				'class' => 'sesbasic_icon_edit',
        'route' => 'seseventreview_view',
        'params' => array(
            'action' => 'edit',
            'review_id' => $review->getIdentity(),
            'slug' => $review->getSlug(),
        )
    );
  }

  public function onMenuInitialize_SeseventreviewProfileReport() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $review = Engine_Api::_()->core()->getSubject();
    $view = Zend_Registry::isRegistered('Zend_View') ?Zend_Registry::get('Zend_View') : null;

    if (!(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.report', 1)))
      return false;
	  
    if (!$viewer->getIdentity())
      return false;

    return array(
        'label' => $view->translate('Report'),
        'class' => 'smoothbox sesbasic_icon_report',
        'route' => 'default',
        'params' => array(
            'module' => 'core',
            'controller' => 'report',
            'action' => 'create',
            'subject' => $review->getGuid(),
            'format' => 'smoothbox',
        ),
    );
  }

  public function onMenuInitialize_SeseventreviewProfileShare() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $review = Engine_Api::_()->core()->getSubject();
    $view = Zend_Registry::isRegistered('Zend_View') ?Zend_Registry::get('Zend_View') : null;

    if (!(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.share', 1)))
      return false;

    if (!$viewer->getIdentity())
      return false;

    if (!$viewer->getIdentity())
      return false;

    return array(
        'label' => $view->translate('Share'),
        'class' => 'smoothbox sesbasic_icon_share',
        'route' => 'default',
        'params' => array(
            'module' => 'activity',
            'controller' => 'index',
            'action' => 'share',
            'type' => $review->getType(),
            'id' => $review->getIdentity(),
            'format' => 'smoothbox',
        ),
    );
  }

  public function onMenuInitialize_SeseventreviewProfileDelete() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $review = Engine_Api::_()->core()->getSubject();
    $view = Zend_Registry::isRegistered('Zend_View') ?Zend_Registry::get('Zend_View') : null;

    if (!$viewer->getIdentity())
      return false;

	  if (!$review->authorization()->isAllowed($viewer, 'delete'))
    	  return false;

    return array(
        'label' => $view->translate('Delete Review'),
        'class' => 'smoothbox sesbasic_icon_delete',
        'route' => 'seseventreview_extended',
        'params' => array(
            'action' => 'delete',
            'type' => $review->getIdentity(),
            'format' => 'smoothbox',
        ),
    );
  }

}