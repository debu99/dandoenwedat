<?php

class Sesbasic_Model_DbTable_Userdetails extends Engine_Db_Table {

  protected $_rowClass = "Sesbasic_Model_Userdetail";
  protected $_name = "sesbasic_userdetails";
  
  function getUserDetails($user_id = null){
    if(!$user_id)
      return array();
    $select = $this->select()->where('user_id =?',$user_id)->limit(1);
    return $this->fetchRow($select);
  }
}
