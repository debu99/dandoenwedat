<?php

class Sesbasic_TooltipController extends Core_Controller_Action_Standard {

  public function indexAction() {
    $guid = $this->_getParam('guid', false);
    if (!$guid)
      return;
    $this->view->subject = $subject = Engine_Api::_()->getItemByGuid($guid);

    if (!$subject)
      return;
    $settings = Engine_Api::_()->getApi('settings', 'core');

    if ($subject->getType() == 'user' && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')) {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto', 'coverphoto'));
      $this->view->moduleEnableTip = $settings->getSetting($subject->getType() . '_settings_tooltip', array('title', 'mainphoto', 'coverphoto', 'socialshare', 'location', 'friendCount', 'mutualFriendCount', 'likeButton', 'message', 'view', 'like', 'follow', 'friendButton', 'age', 'profileType', 'rating'));
      $this->view->socialshare_icon_limit = $settings->getSetting('socialshare.icon.limit', 2);
      $this->view->socialshare_enable_plusicon = $settings->getSetting('socialshare.enable.plusicon', 1);
      $this->renderScript('tooltip/member-data.tpl');
    } elseif ($subject->getType() == 'crowdfunding') {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto'));
      $this->view->moduleEnableTip = $settings->getSetting('crowdfunding_settings_tooltip', array('title','ownerhoto','coverphoto','category','socialshare','ownername', 'stats'));
      $this->view->socialshare_icon_limit = $settings->getSetting('socialshare.icon.limit', 2);
      $this->view->socialshare_enable_plusicon = $settings->getSetting('socialshare.enable.plusicon', 1);
      $this->renderScript('tooltip/crowdfunding-data.tpl');
    } elseif ($subject->getType() == 'sesblog_blog') {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto', 'coverphoto'));
      $this->view->moduleEnableTip = $settings->getSetting($subject->getType() . '_settings_tooltip', array('title', 'mainphoto', 'socialshare', 'location', 'view', 'like', 'rating'));
      $this->view->socialshare_icon_limit = $settings->getSetting('socialshare.icon.limit', 2);
      $this->view->socialshare_enable_plusicon = $settings->getSetting('socialshare.enable.plusicon', 1);
      $this->renderScript('tooltip/blog-data.tpl');
    } else if ($subject->getType() == 'sesevent_event') {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto', 'coverphoto', 'category'));
      $this->view->moduleEnableTip = $settings->getSetting($subject->getType() . '_settings_tooltip', array('title', 'mainphoto', 'coverphoto', 'category', 'socialshare', 'location', 'hostedby', 'startendtime', 'buybutton'));
      $this->view->socialshare_icon_limitevent = $settings->getSetting('socialshare.icon.limitevent', 2);
      $this->view->socialshare_enable_plusiconevent = $settings->getSetting('socialshare.enable.plusiconevent', 1);
    } elseif ($subject->getType() == 'sescontest_contest') {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto', 'coverphoto', 'category'));
      $this->view->moduleEnableTip = $settings->getSetting($subject->getType() . '_settings_tooltip', array('title', 'mainphoto', 'coverphoto', 'category', 'socialshare', 'entries', 'joinNow', 'recentEntries','description', 'media', 'friendsParticipating'));
      $socialshare_icon_limitcontest = $settings->getSetting('socialshare.icon.limitcontest', 2);
      $socialshare_enable_plusiconcontest = $settings->getSetting('socialshare.enable.plusiconcontest', 1);

      $users = $this->view->viewer()->membership()->getMembershipsOfIds();
      $participateTable = Engine_Api::_()->getDbTable('participants', 'sescontest');
      $participantTableName = $participateTable->info('name');
      if (count($users) < 1)
        $this->view->friends = array();
      else {
        if (in_array('friendsParticipating', $this->view->moduleEnableTip)) {
          $userTable = Engine_Api::_()->getItemTable('user');
          $userTableName = $userTable->info('name');
          $select = $userTable->select()
                  ->setIntegrityCheck(false)
                  ->from($userTableName, array('user_id', 'displayname', 'photo_id'))
                  ->join($participantTableName, $userTableName . '.user_id=' . $participantTableName . '.owner_id', null)
                  ->where($participantTableName . '.contest_id =?', $subject->contest_id)
                  ->where($userTableName . '.user_id IN (?)', $users)
                  ->limit('8')
                  ->order('Rand()');
          $this->view->friends = $userTable->fetchAll($select);
        } else {
          $this->view->friends = array();
        }
      }
      $this->view->entries = 0;
      if (in_array('recentEntries', $this->view->moduleEnableTip)) {
        $select = $participateTable->select()
                ->from($participantTableName, array('*'))
                ->where($participantTableName . '.contest_id =?', $subject->contest_id)
                ->limit('4')
                ->order('creation_date DESC');
        $this->view->entries = $participateTable->fetchAll($select);
      }
      $this->view->params = array('socialshare_enable_plusicon' => $socialshare_enable_plusiconcontest, 'socialshare_icon_limit' => $socialshare_icon_limitcontest);
      $this->renderScript('tooltip/contest-data.tpl');
    } else {
      $this->view->globalEnableTip = $settings->getSetting('sesbasic_settings_tooltip', array('title', 'mainphoto'));
      $this->view->moduleEnableTip = $settings->getSetting($subject->getType() . '_settings_tooltip', array('title', 'mainphoto', 'socialshare', 'comment', 'view', 'like'));
      //common tooltip
      $this->renderScript('tooltip/basic-data.tpl');
    }
  }

}
