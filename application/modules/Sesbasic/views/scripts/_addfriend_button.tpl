
<?php
  if(empty($subject))
  $subject = $this->subject;
  $table = Engine_Api::_()->getDbtable('block', 'user');
  $viewer = Engine_Api::_()->user()->getViewer();
  $select = $table->select()->where('user_id = ?', $subject->getIdentity())->where('blocked_user_id = ?', $viewer->getIdentity())->limit(1);
  $row = $table->fetchRow($select);
?>

<?php
  $tokenName = 'token_' . $subject->getGuid();
  $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret');
  $tokenValue = $this->token(null, $tokenName, $salt);
?>

<?php if( $row == NULL ): ?>
  <?php if( $this->viewer()->getIdentity() ): ?>
    <?php    
      if( null === $viewer ) {
        $viewer = Engine_Api::_()->user()->getViewer();
      }

      if( !$viewer || !$viewer->getIdentity() || $subject->isSelf($viewer) ) {
        return '';
      }

      $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);

      // Get data
      if( !$direction ) {
        $row = Engine_Api::_()->sesbasic()->getRow($subject, $viewer);
      // $row = $subject->membership()->getRow($viewer);
      }
      else {
        $row = Engine_Api::_()->sesbasic()->getRow($viewer, $subject);
        //$row = $viewer->membership()->getRow($subject);
      }
          // Check if friendship is allowed in the network
      $eligible =  (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
      if($eligible == 0){
        return '';
      }
    
      // check admin level setting if you can befriend people in your network
      else if( $eligible == 1 ) {

        $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
        $networkMembershipName = $networkMembershipTable->info('name');

        $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
        $select
          ->from($networkMembershipName, 'user_id')
          ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
          ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
          ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
          ;

        $data = $select->query()->fetch();

        if(empty($data)){
          return '';
        }
      }

      if( !$direction ) {
        // one-way mode
        if( null === $row ) { ?>
        	<a class="sesbasic_button smoothbox" href="<?php echo $this->escape($this->url(array('controller' => 'friends', 'action' => 'add', 'user_id' => $subject->user_id, 'format' => 'smoothbox'), 'user_extended' , true)); ?>"><i class='fa fa-check'></i><span><?php echo $this->translate("Follow");?></span></a>
       <?php } else if( $row->resource_approved == 0 ) { ?>
       	<a class="sesbasic_button smoothbox" href="<?php echo $this->escape($this->url(array('controller' => 'friends', 'action' => 'cancel', 'user_id' => $subject->user_id, 'format' => 'smoothbox'), 'user_extended' , true)); ?>"><i class='fa fa-times'></i><span><?php echo $this->translate("Cancel Follow Request");?></span></a>
       <?php  } else { ?>
       	<a class="sesbasic_button smoothbox" href="<?php echo $this->escape($this->url(array('controller' => 'friends', 'action' => 'remove', 'user_id' => $subject->user_id, 'format' => 'smoothbox'), 'user_extended' , true)); ?>"><i class='fa fa-times'></i><span><?php echo $this->translate("Unfollow");?></span></a>
       <?php }
      } else {
        // two-way mode
        if( null === $row ) {
          echo "<a href='javascript:void(0);' class='sesbasic_button sesmember_add_btn sesbasic_member_addfriend_request' data-tokenname='".$tokenName."' data-tokenvalue='".$tokenValue."' data-src = '".$subject->user_id."' data-rel='button'><i class='fa fa-user-plus'></i><span>".$this->translate('Add Friend')."</span></a>";
        } else if( $row->user_approved == 0 ) {
          echo "<a href='javascript:void(0);' class='sesbasic_button sesmember_cancel_request_btn sesbasic_member_cancelfriend_request' data-tokenname='".$tokenName."' data-tokenvalue='".$tokenValue."' data-src = '".$subject->user_id."' data-rel='button'><i class='fa fa-user-times'></i><span>".$this->translate('Cancel Friend Request')."</span></a>";
        } else if( $row->resource_approved == 0 ) {
          echo "<a href='javascript:void(0);' class='sesbasic_button sesmember_actapt_request_btn sesbasic_member_acceptfriend_request' data-tokenname='".$tokenName."' data-tokenvalue='".$tokenValue."' data-src = '".$subject->user_id."' data-rel='button'><i class='fa fa-user-plus'></i><span>".$this->translate('Accept Friend Request')."</span></a>";
        } else if( $row->active ) {
          echo "<a href='javascript:void(0);' class='sesbasic_button sesmember_remove_friend_btn sesbasic_member_removefriend_request' data-tokenname='".$tokenName."' data-tokenvalue='".$tokenValue."' data-src = '".$subject->user_id."' data-rel='button'><i class='fa fa-user-times'></i><span>".$this->translate('Remove Friend')."</span></a>";
        }
      }
    ?>
  <?php endif; ?>
<?php endif; ?>
