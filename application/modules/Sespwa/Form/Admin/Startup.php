<?php

class Sespwa_Form_Admin_Startup extends Engine_Form {
  protected $_mode;

  public function init() {

    $this->setTitle('Choose startup screen data')
            ->setDescription('');

    // Get available files
      //New File System Code
      $images = array('' => '');
      $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
      foreach( $files as $file ) {
        $images[$file->storage_path] = $file->name;
      }

    $this->addElement('Text', 'title', array(
        'label' => 'Title',
        'value'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title'),
    ));
      $this->addElement('Radio', 'copyright', array(
          'label' => 'Show Copyright',
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
          'value'=>'1',
      ));
    $this->addElement('Select', 'logo', array(
        'label' => 'Select Logo Image',
        'multiOptions' => $images,
    ));
  }

}
