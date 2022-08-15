<?php
class Sesbasic_Model_DbTable_Menusicons extends Engine_Db_Table {

  protected $_rowClass = 'Sesbasic_Model_Menusicon';

  public function getRow($resource){
    $select = $this->select()->where('menu_id =?',$resource)->limit(1);
    return $this->fetchRow($select);
  }
  public function deleteNotification($resource, $moduleName = ''){
    $getRow = $this->getRow($resource);
    if($getRow && empty($moduleName)){
       $getRow->delete();
    } else if($moduleName == 'sespwa') {
        $getRow->sespwa_icon_id = 0;
        $getRow->save();
    }
  }
  public function addSave($resource = null,$file_id = 0, $font_icon = '', $icon_type = 0, $moduleName = '') {
    $row = $this->getRow($resource);
    if(!$row)
      $row = $this->createRow();
    $row->menu_id = $resource;
    if(!empty($moduleName) && $moduleName == 'sespwa') {
        $row->sespwa_icon_id = $file_id;
    } else {
        $row->icon_id = $file_id;
    }
    if(!empty($moduleName) && $moduleName == 'sesytube') {
        $row->activeicon = $file_id;
    }
    $row->font_icon = $font_icon;
    $row->icon_type = $icon_type;
    $row->type = 'icon';
    $row->save();
    return $row;
  }

  public function addActiveSave($resource = null,$file_id = 0, $font_icon = '', $icon_type = 0, $moduleName = '') {
    $row = $this->getRow($resource);
    if(!$row)
      $row = $this->createRow();
    $row->menu_id = $resource;
    if(!empty($moduleName) && $moduleName == 'sesytube') {
        $row->activeicon = $file_id;
    }
    $row->font_icon = $font_icon;
    $row->icon_type = $icon_type;
    $row->type = 'icon';
    $row->save();
    return $row;
  }
}
