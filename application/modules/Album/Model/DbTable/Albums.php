<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Albums.php 10264 2014-06-06 22:08:42Z lucas $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Model_DbTable_Albums extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = 'Album_Model_Album';

    public function getSpecialAlbum(User_Model_User $user, $type)
    {
        $select = $this->select()
            ->where('owner_type = ?', $user->getType())
            ->where('owner_id = ?', $user->getIdentity())
            ->where('type = ?', $type)
            ->order('album_id ASC')
            ->limit(1);

        $album = $this->fetchRow($select);

        // Create wall photos album if it doesn't exist yet
        if( null === $album ) {
            $translate = Zend_Registry::get('Zend_Translate');

            $album = $this->createRow();
            $album->owner_type = 'user';
            $album->owner_id = $user->getIdentity();
            $album->title = $translate->_(ucfirst($type) . ' Photos');
            $album->type = $type;

            $settings = Engine_Api::_()->getApi('settings', 'core');
            $album->search = (int) $settings->getSetting('album_searchable', 0);
            if( $type == 'message' ) {
                $album->search = 0;
            }

            $album->save();

            // Authorizations
            if( $type != 'message' ) {
                $auth = Engine_Api::_()->authorization()->context;
                $auth->setAllowed($album, 'everyone', 'view',    true);
                $auth->setAllowed($album, 'everyone', 'comment', true);
            }
        }

        return $album;
    }

    public function getAlbumSelect($options = array())
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        if( !empty($options['owner']) ) {
            $owner = $options['owner'];
            $ownerId = $owner->getIdentity();
        }
        $excludedLevels = array(1, 2, 3);   // level_id of Superadmin,Admin & Moderator
        $isOwnerOrAdmin = false;
        if( !empty($viewerId)
            && ((isset($ownerId) && ($ownerId == $viewerId))
                || in_array($viewer->level_id, $excludedLevels)) ) {
            $isOwnerOrAdmin = true;
        }
        $select = $this->select();

        if( !empty($options['search']) && is_numeric($options['search']) && !$isOwnerOrAdmin ) {
            $select->where('search = ?', $options['search']);
        }

        $select = Engine_Api::_()->network()->getNetworkSelect($this->info('name'), $select);
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('album.allow.unauthorized', 0)){
            return $select;
        }
        if( !empty($owner) && $owner instanceof Core_Model_Item_Abstract ) {
            $select
                ->where('owner_type = ?', $owner->getType())
                ->where('owner_id = ?', $ownerId)
                ->order('modified_date DESC')
            ;

            if( $isOwnerOrAdmin ) {
                return $select;
            }

            $isOwnerViewerLinked = true;

            if( $viewer->getIdentity() ) {
                $restrictedPrivacy = array('owner');

                $ownerFriendsIds = $owner->membership()->getMembersIds();
                if( !in_array($viewerId, $ownerFriendsIds) ) {
                    array_push($restrictedPrivacy, 'owner_member');

                    $friendsOfFriendsIds = array();
                    foreach( $ownerFriendsIds as $friendId ) {
                        $friend = Engine_Api::_()->getItem('user', $friendId);
                        $friendMembersIds = $friend->membership()->getMembersIds();
                        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
                    }

                    if( !in_array($viewerId, $friendsOfFriendsIds) ) {
                        array_push($restrictedPrivacy, 'owner_member_member');

                        $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
                        $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
                        $ownerNetwork = $netMembershipTable->getMembershipsOfIds($owner);
                        if( !array_intersect($viewerNetwork, $ownerNetwork) ) {
                            $isOwnerViewerLinked = false;
                        }
                    }
                }
                if( $isOwnerViewerLinked ) {
                    $select->where("view_privacy NOT IN (?)", $restrictedPrivacy);
                    return $select;
                }
            }

            $select->where("view_privacy = ?", 'everyone');
        }

        return $select;
    }

    public function getAlbumPaginator($options = array())
    {
        return Zend_Paginator::factory($this->getAlbumSelect($options));
    }
}
