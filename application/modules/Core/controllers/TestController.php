<?php

class Core_TestController extends Core_Controller_Action_Standard {

    public function runTaskAction() {
        $plugin = $this->getParam('plugin', false);
        $task_id = $this->getParam('id', false);
        
        $table = Engine_Api::_()->getDbtable('tasks', 'core');
        
        if ($plugin) {
            $select = $table->select()->where('plugin = ?', $plugin);
            $task = $table->fetchRow($select);
        } else if ($task_id) {
            $task = $table->find($task_id)->current();
        }
        
        if (!$task) {
            exit('No tasks found!<br>');
        }
        
        $plugin = new $task->plugin($task);
        if ($plugin) {
            $plugin->execute();
            exit('Executed!');
        } else {
            exit('No plugin found!');
        }
    }

}
