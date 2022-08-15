<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Plugin_Menus
{
  public function canCreateGroups()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create events
    if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewGroups()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    // Must be able to view events
    if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_GroupMainManage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$viewer->getIdentity() )
    {
      return false;
    }
    return true;
  }

  public function onMenuInitialize_GroupMainCreate()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if( !$viewer->getIdentity() )
    {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('group', null, 'create') )
    {
      return false;
    }

    return true;
  }
  
  public function onMenuInitialize_GroupProfileEdit()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }
    
    return array(
      'label' => 'Edit Group Details',
      'class' => 'icon_group_edit',
      'route' => 'group_specific',
      'params' => array(
        'controller' => 'group',
        'action' => 'edit',
        'group_id' => $subject->getIdentity(),
        'ref' => 'profile'
      )
    );
  }

  public function onMenuInitialize_GroupProfileStyle()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() || !$subject->authorization()->isAllowed($viewer, 'edit') )
    {
      return false;
    }

    if( !$subject->authorization()->isAllowed($viewer, 'style') )
    {
      return false;
    }

    return array(
      'label' => 'Edit Group Style',
      'class' => 'smoothbox icon_style',
      'route' => 'group_specific',
      'params' => array(
        'action' => 'style',
        'group_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      )
    );
  }

  public function onMenuInitialize_GroupProfileMember()
  {
    $menu = array();
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() )
    {
      return false;
    }

    $row = $subject->membership()->getRow($viewer);

    // Not yet associated at all
    if( null === $row )
    {
      if( $subject->membership()->isResourceApprovalRequired() ) {
        $menu[] =  array(
          'label' => 'Request Membership',
          'class' => 'smoothbox icon_group_join',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'group_id' => $subject->getIdentity(),
          ),
        );
      } else {
        $menu[] =  array(
          'label' => 'Join Group',
          'class' => 'smoothbox icon_group_join',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'group_id' => $subject->getIdentity()
          ),
        );
      }
    }

    // Full member
    // @todo consider owner
    else if( $row->active )
    {
      if( !$subject->isOwner($viewer) ) {
        $menu[] =  array(
          'label' => 'Leave Group',
          'class' => 'smoothbox icon_group_leave',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'group_id' => $subject->getIdentity()
          ),
        );
      }
    }

    else if( !$row->resource_approved && $row->user_approved )
    {
      $menu[] =  array(
        'label' => 'Cancel Membership Request',
        'class' => 'smoothbox icon_group_cancel',
        'route' => 'group_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'group_id' => $subject->getIdentity()
        ),
      );
    }

    else if( !$row->user_approved && $row->resource_approved )
    {
      $menu[] = array(
          'label' => 'Accept Membership Request',
          'class' => 'smoothbox icon_group_accept',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'group_id' => $subject->getIdentity()
          ),
      );

      $menu[] =  array(
          'label' => 'Ignore Membership Request',
          'class' => 'smoothbox icon_group_reject',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'group_id' => $subject->getIdentity()
          ),
      );
    }

    else
    {
      throw new Group_Model_Exception('Wow, something really strange happened.');
    }

    $canDelete = Engine_Api::_()->authorization()->isAllowed($subject, null, 'delete');
    if( $canDelete ) {
      $menu[] = array(
        'label' => 'Delete Group',
        'class' => 'smoothbox icon_group_delete',
        'route' => 'group_specific',
        'params' => array(
          'action' => 'delete',
          'group_id' => $subject->getIdentity()
        ),
      );
    }

    if( count($menu) == 1 ) {
      return $menu[0];
    }
    return $menu;
  }

  public function onMenuInitialize_GroupProfileReport()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }
    
    if( !$viewer->getIdentity() ||
        $subject->isOwner($viewer) ) {
      return false;
    } else {
      return array(
        'label' => 'Report',
        'class' => 'smoothbox icon_report',
        'route' => 'default',
        'params' => array(
          'module' => 'core',
          'controller' => 'report',
          'action' => 'create',
          'subject' => $subject->getGuid(),
          'format' => 'smoothbox',
        ),
      );
    }
  }

  public function onMenuInitialize_GroupProfileInvite()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if( $subject->getType() !== 'group' ) {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$subject->authorization()->isAllowed($viewer, 'invite') ) {
      return false;
    }

    return array(
      'label' => 'Invite Members',
      'class' => 'smoothbox icon_invite',
      'route' => 'group_extended',
      'params' => array(
        //'module' => 'group',
        'controller' => 'member',
        'action' => 'invite',
        'group_id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_GroupProfileShare()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' )
    {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() )
    {
      return false;
    }
    
    return array(
      'label' => 'Share Group',
      'class' => 'smoothbox icon_share',
      'route' => 'default',
      'params' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'share',
        'type' => $subject->getType(),
        'id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }

  public function onMenuInitialize_GroupProfileMessage()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject->getType() !== 'group' ) {
      throw new Group_Model_Exception('Whoops, not a group!');
    }

    if( !$viewer->getIdentity() || !$subject->isOwner($viewer) ) {
      return false;
    }

    return array(
      'label' => 'Message Members',
      'class' => 'icon_message',
      'route' => 'messages_general',
      'params' => array(
        'action' => 'compose',
        'to' => $subject->getIdentity(),
        'multi' => 'group'
      )
    );
  }
}
