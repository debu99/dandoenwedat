<?php

class Sesbasic_SaveController extends Core_Controller_Action_Standard {

  public function indexAction() {

    //Get viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id))
      return;

    //Get subject id and subject type
    $subject_id = $this->_getParam('subject_id');
    $subject_type = $this->_getParam('subject_type');
    $save_id = $this->_getParam('save_id');

    //Resource according to subject id and subject type
    $item = Engine_Api::_()->getItem($subject_type, $subject_id);

    if (empty($save_id)) {

      //Check already save or not
      $isSave = Engine_Api::_()->getDbTable('saves', 'sesbasic')->isSave($item, $viewer);

      if (empty($isSave)) {

        $saveTable = Engine_Api::_()->getDbTable('saves', 'sesbasic');
        $db = $saveTable->getAdapter();
        $db->beginTransaction();
        try {

          if (!empty($item))
            $save_id = $saveTable->addSave($item, $viewer)->save_id;
          $this->view->save_id = $save_id;
          $db->commit();

          //Activity Feed and attachement
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, 'save_sesevent_event');
          if ($action) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
          }
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
      } else {
        $this->view->save_id = $isSave;
      }
    } else {

      //Remove saved entry
      Engine_Api::_()->getDbTable('saves', 'sesbasic')->removeSave($item, $viewer);
    }
  }

}
