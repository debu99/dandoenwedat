<?php

class Sesadvancedactivity_Plugin_Task_Cleanup extends Core_Plugin_Task_Abstract {

  public function execute() {
  
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->query('DELETE from engine4_activity_stream WHERE action_id NOT IN (SELECT action_id FROM engine4_activity_actions);');
    
    $db->query('DELETE from engine4_activity_notifications WHERE object_id NOT IN (SELECT comment_id FROM engine4_activity_comments) AND object_type = "activity_comment";');
    
    $db->query('DELETE from engine4_activity_notifications WHERE object_id NOT IN (SELECT comment_id FROM engine4_core_comments) AND object_type = "core_comment";');
    
  }
}