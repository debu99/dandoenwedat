<?php ?>

<?php
  $row = $this->row;
  $subject = $this->subject;
  // two-way mode
  if( null === $row ) {
    echo "<a href='javascript:void(0);' class='sesbasic_btn sesmember_add_btn sesbasic_member_addfriend_request' data-tokenname='".$this->tokenName."' data-tokenvalue='".$this->tokenValue."' data-src = '".$subject->user_id."'><i class='fa fa-user-plus'></i><span><i class='fa fa-caret-down'></i>".$this->translate('Add Friend')."</span></a>";
  } else if( $row->user_approved == 0 ) {
    echo "<a href='javascript:void(0);' class='sesbasic_btn sesmember_cancel_request_btn sesbasic_member_cancelfriend_request' data-tokenname='".$this->tokenName."' data-tokenvalue='".$this->tokenValue."' data-src = '".$subject->user_id."'><i class='fa fa-user-times'></i><span><i class='fa fa-caret-down'></i>".$this->translate('Cancel Friend Request')."</span></a>";
  } else if( $row->resource_approved == 0 ) {
    echo "<a href='javascript:void(0);' class='sesbasic_btn sesmember_actapt_request_btn sesbasic_member_acceptfriend_request' data-tokenname='".$this->tokenName."' data-tokenvalue='".$this->tokenValue."' data-src = '".$subject->user_id."'><i class='fa fa-user-plus'></i><span><i class='fa fa-caret-down'></i>".$this->translate('Accept Friend Request')."</span></a>";
  } else if( $row->active ) {
    echo "<a href='javascript:void(0);' class='sesbasic_btn sesmember_remove_friend_btn sesbasic_member_removefriend_request' data-tokenname='".$this->tokenName."' data-tokenvalue='".$this->tokenValue."' data-src = '".$subject->user_id."'><i class='fa fa-user-times'></i><span><i class='fa fa-caret-down'></i>".$this->translate('Remove Friend')."</span></a>";
  }

?>
