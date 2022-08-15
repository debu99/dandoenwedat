<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Groups.php 10049 2013-06-06 22:24:49Z shaun $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Model_DbTable_Groups extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = 'Group_Model_Group';

    public function getGroupPaginator($params = array())
    {
        return Zend_Paginator::factory($this->getGroupSelect($params));
    }

    public function getGroupSelect($params = array())
    {
        $table = Engine_Api::_()->getItemTable('group');
        $select = $table->select();
        $viewer = Engine_Api::_()->user()->getViewer();
        // User-based
        if (!empty($params['owner']) && $params['owner'] instanceof Core_Model_Item_Abstract) {
            $owner = $params['owner'];
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif (!empty($params['user_id'])) {
            $owner = Engine_Api::_()->getItem('user', $params['user_id']);
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif (isset($params['users']) && is_array($params['users'])) {
            foreach ($params['users'] as &$id) {
                if (!is_numeric($id)) {
                    $id = 0;
                }
            }

            $params['users'] = array_filter($params['users']);

            if (empty($params['users'])) {
                return $select->where('1 != 1');
            }
            $select->where('user_id IN (?)', $params['users']);
            if (!in_array($viewer->level_id, $this->_excludedLevels)) {
                $getViewer = $this->getViewerGroups();
                if (empty($getViewer)) {
                    $select->where("view_privacy != ? ", 'member');
                } else {
                    $select->where("view_privacy != 'member' OR (view_privacy = 'member' AND group_id IN (?))", $this->getViewerGroups());
                }
            }
        } else {
            $param = array();
            $select = $this->getItemsSelect($param, $select);
        }

        // Search
        if (isset($params['search'])) {
            $select->where('search = ?', (bool) $params['search']);
        }
        // Category
        if (!empty($params['category_id'])) {
            $select->where('category_id = ?', $params['category_id']);
        }

        //Full Text
        if (!empty($params['search_text'])) {
            $select->where("(`description` LIKE ? OR `title` LIKE ?)", '%' . $params['search_text'] . '%');
        }

        // Order
        if (!empty($params['order'])) {
            $select->order($params['order']);
        } else {
            $select->order('creation_date DESC');
        }

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select, 'user_id');

        return $select;
    }

    public function getItemsSelect($params, $select = null)
    {
        if ($select == null) {
            $select = $this->select();
        }
        if (isset($params['search'])) {
            $select->where("search = ?", $params['search']);
        }
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('group.allow.unauthorized', 0)){
            return $select;
        }
        $registeredPrivacy = array('everyone', 'registered');
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$viewer->getIdentity()) {
            $select->where("view_privacy = ?", 'everyone');
        } elseif (!in_array($viewer->level_id, $this->_excludedLevels)) {
            $select->Where("view_privacy IN (?)", $registeredPrivacy);
            $getViewer = $this->getViewerGroups();
            if (!empty($getViewer)) {
                $select->orWhere("view_privacy = 'member' AND group_id IN (?)", $this->getViewerGroups());
            }
            $subquery = $select->getPart(Zend_Db_Select::WHERE);
            $select->reset(Zend_Db_Select::WHERE);
            $select->where(implode(' ', $subquery));
        }


        return $select;
    }

    public function getProfileItemsSelect($owner, $select = null)
    {
        if ($select == null) {
            $select = $this->select();
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        if (!empty($owner)) {
            $ownerId = $owner->getIdentity();
        }

        $isOwnerOrAdmin = false;
        if (!empty($viewerId) && ($ownerId == $viewerId || in_array($viewer->level_id, $this->_excludedLevels))) {
            $isOwnerOrAdmin = true;
        }

        if (!empty($owner) && $owner instanceof Core_Model_Item_Abstract) {
            $select->where('user_id = ?', $ownerId);

            if ($isOwnerOrAdmin) {
                return $select;
            }

            if ($viewer->getIdentity()) {
                $select->where("view_privacy != ?", 'member');
                $getViewer = $this->getViewerGroups();
                if (!empty($getViewer)) {
                    $select->orWhere("view_privacy = 'member' AND user_id = $ownerId AND group_id IN (?)", $this->getViewerGroups());
                }
                return $select;
            }

            $select->where("view_privacy = ?", 'everyone');
        }

        return $select;
    }

    public function getViewerGroups()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $groupMembershipTable = Engine_Api::_()->getDbtable('membership', 'group');
        return $groupMembershipTable->getMembershipsOfIds($viewer);
    }
}
