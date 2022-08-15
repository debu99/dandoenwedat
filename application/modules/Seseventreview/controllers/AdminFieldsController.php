<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminFieldsController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_AdminFieldsController extends Fields_Controller_AdminAbstract {

  protected $_requireProfileType = true;
  protected $_fieldType = 'eventreview';

  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_reviewsettings');
		 $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventreview_admin_main', array(), 'seseventreview_admin_main_categories');
    $this->view->subsubNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seseventreview_admin_categories', array(), 'seseventreview_admin_main_subfields');

    parent::indexAction();
  }

  public function fieldCreateAction() {

    $content_type = $this->_getParam('content_type', null);
    if ($content_type == 'sesevent_category') {
      $content_type = 'sesevent_event';
    }
    $module_name = $this->_getParam('module_name', null);

    if ($this->_requireProfileType || $this->_getParam('option_id')) {
      $option = Engine_Api::_()->fields()->getOption($this->_getParam('option_id'), $this->_fieldType);
    } else
      $option = null;

    // Check type param and get form class
    $cfType = $this->_getParam('type');
    if (!empty($cfType)) {
      $adminFormClass = Engine_Api::_()->fields()->getFieldInfo($cfType, 'adminFormClass');
    }

    if (empty($adminFormClass) || !@class_exists($adminFormClass)) {
      $adminFormClass = 'Fields_Form_Admin_Field';
    }

    $this->view->form = $form = new $adminFormClass();
    $form->setTitle('Add Question');
    $form->removeElement('show');
    $form->removeElement('search');
    $form->addElement('hidden', 'show', array('value' => 0));
    $form->removeElement('display');
    $this->view->formAlt = $formAlt = new Fields_Form_Admin_Map();
    $formAlt->setAction($this->view->url(array('action' => 'map-create')));
    $fieldMaps = Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType);
    $fieldList = array();
    $fieldData = array();
    foreach (Engine_Api::_()->fields()->getFieldsMeta($this->_fieldType) as $field) {
      if ($field->type == 'profile_type')
        continue;
      // Ignore fields in the same category as we have selected
      foreach ($fieldMaps as $map) {
        if ((!$option || !$map->option_id || $option->option_id == $map->option_id ) && $field->field_id == $map->child_id) {
          continue 2;
        }
      }
      // Add
      $fieldList[] = $field;
      $fieldData[$field->field_id] = $field->label;
    }

    $this->view->fieldList = $fieldList;
    $this->view->fieldData = $fieldData;
    if (count($fieldData) < 1) {
      $this->view->formAlt = null;
    } else {
      $formAlt->getElement('field_id')->setMultiOptions($fieldData);
    }

    // Check method/data
    if (!$this->getRequest()->isPost()) {
      $form->populate($this->_getAllParams());
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $field = Engine_Api::_()->fields()->createField($this->_fieldType, array_merge(array(
        'option_id' => ( is_object($option) ? $option->option_id : '0' ),
                    ), $form->getValues()));

    $this->view->status = true;
    $this->view->field = $field->toArray();
    $this->view->option = is_object($option) ? $option->toArray() : array('option_id' => '0');
    $this->view->form = null;

    // Re-render all maps that have this field as a parent or child
    $maps = array_merge(
            Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('field_id', $field->field_id), Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('child_id', $field->field_id)
    );

    $html = array();
    foreach ($maps as $map) {
      $html[$map->getKey()] = $this->view->adminFieldMeta($map);
    }

    $this->view->htmlArr = $html;
  }

  public function fieldEditAction() {

    $field = Engine_Api::_()->fields()->getField($this->_getParam('field_id'), $this->_fieldType);
    // Check type param and get form class
    $cfType = $this->_getParam('type', $field->type);
    $adminFormClass = null;
    if (!empty($cfType)) {
      $adminFormClass = Engine_Api::_()->fields()->getFieldInfo($cfType, 'adminFormClass');
    }
    if (empty($adminFormClass) || !@class_exists($adminFormClass)) {
      $adminFormClass = 'Fields_Form_Admin_Field';
    }
    // Create form
    $this->view->form = $form = new $adminFormClass();
    $form->setTitle('Edit Event Review Category Profile Question');
    $form->removeElement('show');
    $form->removeElement('search');
    $form->addElement('hidden', 'show', array('value' => 0));
    $form->removeElement('display');
    $linkCount = count(Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)
                    ->getRowsMatching('child_id', $field->field_id));
    if ($linkCount >= 2) {
      $form->addNotice($this->view->translate(array(
                  'This question is synced. Changes you make here will be applied in %1$s other place.',
                  'This question is synced. Changes you make here will be applied in %1$s other places.',
                  $linkCount - 1), $this->view->locale()->toNumber($linkCount - 1)));
    }
    // Check method/data
    if (!$this->getRequest()->isPost()) {
      $form->populate($field->toArray());
      $form->populate($this->_getAllParams());
      if (is_array($field->config)) {
        $form->populate($field->config);
      }
      $this->view->search = $field->search;
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    Engine_Api::_()->fields()->editField($this->_fieldType, $field, $form->getValues());
    $this->view->status = true;
    $this->view->field = $field->toArray();
    $this->view->form = null;
    // Re-render all maps that have this field as a parent or child
    $maps = array_merge(
            Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('field_id', $field->field_id), Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('child_id', $field->field_id)
    );
    $html = array();
    foreach ($maps as $map) {
      $html[$map->getKey()] = $this->view->adminFieldMeta($map);
    }
    $this->view->htmlArr = $html;
  }

  // Headings
  public function headingCreateAction() {

    parent::headingCreateAction();
    $form = $this->view->form;
    if ($form) {
      $form->removeElement('display');
      $form->addElement('hidden', 'display', array('value' => 1, 'order' => 10000));
      $form->removeElement('show');
      $form->addElement('hidden', 'show', array('value' => 0, 'order' => 90000));
    }
  }

  //Edit Headings
  public function headingEditAction() {

    //Here we have inherit field module funtion from: Fields_Controller_AdminAbstract
    parent::headingEditAction();
    $form = $this->view->form;
    if ($form) {
      $form->removeElement('display');
      $form->addElement('hidden', 'display', array('value' => 1));
      $form->removeElement('show');
      $form->addElement('hidden', 'show', array('value' => 0));
    }
  }

  // Profile types
  public function typeCreateAction() {

    $field = Engine_Api::_()->fields()->getField($this->_getParam('field_id'), $this->_fieldType);
    // Validate input
    if ($field->type !== 'profile_type') {
      throw new Exception(sprintf('invalid input, type is "%s", expected "profile_type"', $field->type));
    }
    // Create form
    $this->view->form = $form = new Seseventreview_Form_Admin_Manage_Type();
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Create New Profile Type from Duplicate of Existing
    if ($form->getValue('duplicate') != 'null') {
      $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
          'field_id' => $field->field_id,
          'label' => $form->getValue('label'),
      ));
      // Get New Option ID
      $db = Engine_Db_Table::getDefaultAdapter();
      $new_option_id = $db->select('option_id')
              ->from('engine4_eventreview_fields_options')
              ->where('label = ?', $form->getValue('label'))
              ->query()
              ->fetchColumn();
      // Get list of Field IDs From Duplicated member Type
      $field_map_array = $db->select()
              ->from('engine4_eventreview_fields_maps')
              ->where('option_id = ?', $form->getValue('duplicate'))
              ->query()
              ->fetchAll();

      $field_map_array_count = count($field_map_array);
      // Check if the Member type is blank
      if ($field_map_array_count == 0) {
        // Create new blank option
        $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
            'field_id' => $field->field_id,
            'label' => $form->getValue('label'),
        ));
        $this->view->option = $option->toArray();
        $this->view->form = null;
        return;
      }
      for ($c = 0; $c < $field_map_array_count; $c++) {
        $child_id_array[] = $field_map_array[$c]['child_id'];
      }
      unset($c);
      $field_meta_array = $db->select()
              ->from('engine4_eventreview_fields_meta')
              ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
              ->query()
              ->fetchAll();
      // Copy each row
      for ($c = 0; $c < $field_map_array_count; $c++) {
        $db->insert('engine4_eventreview_fields_meta', array(
            'type' => $field_meta_array[$c]['type'],
            'label' => $field_meta_array[$c]['label'],
            'description' => $field_meta_array[$c]['description'],
            'alias' => $field_meta_array[$c]['alias'],
            'required' => $field_meta_array[$c]['required'],
            'display' => $field_meta_array[$c]['display'],
            'publish' => $field_meta_array[$c]['publish'],
            'search' => $field_meta_array[$c]['search'],
            'show' => $field_meta_array[$c]['show'],
            'order' => $field_meta_array[$c]['order'],
            'config' => $field_meta_array[$c]['config'],
            'validators' => $field_meta_array[$c]['validators'],
            'filters' => $field_meta_array[$c]['filters'],
            'style' => $field_meta_array[$c]['style'],
            'error' => $field_meta_array[$c]['error'],
            'icon' => $field_meta_array[$c]['icon'],
                )
        );
        // Add original field_id to array => new field_id to new corresponding row
        $child_id_reference[$field_meta_array[$c]['field_id']] = $db->lastInsertId();
      }
      unset($c);
      // Create new map from array using new field_id values and new Option ID
      $map_count = count($field_map_array);
      for ($i = 0; $i < $map_count; $i++) {
        $db->insert('engine4_eventreview_fields_maps', array(
            'field_id' => $field_map_array[$i]['field_id'],
            'option_id' => $new_option_id,
            'child_id' => $child_id_reference[$field_map_array[$i]['child_id']],
            'order' => $field_map_array[$i]['order'],
                )
        );
      }
    } else {
      // Create new blank option
      $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
          'field_id' => $field->field_id,
          'label' => $form->getValue('label'),
      ));
    }
    $this->view->option = $option->toArray();
    $this->view->form = null;
    // Get data
    $mapData = Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType);
    $metaData = Engine_Api::_()->fields()->getFieldsMeta($this->_fieldType);
    $optionData = Engine_Api::_()->fields()->getFieldsOptions($this->_fieldType);
    // Flush cache
    $mapData->getTable()->flushCache();
    $metaData->getTable()->flushCache();
    $optionData->getTable()->flushCache();
  }

  public function typeEditAction() {

    $option = Engine_Api::_()->fields()->getOption($this->_getParam('option_id'), $this->_fieldType);
    $field = Engine_Api::_()->fields()->getField($option->field_id, $this->_fieldType);
    // Validate input
    if ($field->type !== 'profile_type') {
      throw new Exception('invalid input');
    }
    // Create form
    $this->view->form = $form = new Fields_Form_Admin_Type();
    $form->save->setLabel('Edit Profile Type');
    $form->removeElement('duplicate');
    // Check method/data
    if (!$this->getRequest()->isPost()) {
      $form->populate($option->toArray());
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    Engine_Api::_()->fields()->editOption($this->_fieldType, $option, array(
        'label' => $form->getValue('label'),
    ));
    $this->view->option = $option->toArray();
    $this->view->form = null;
  }
  public function typeDeleteAction() {
    $option_id = $this->_getParam('option_id');
    if (!empty($option_id)) {
      // Engine_Api::_()->getDbtable('categories', 'seseventreview')->update(array('profile_type' => 0), array('profile_type = ?' => $option_id));
    }
    parent::typeDeleteAction();
  }

}
