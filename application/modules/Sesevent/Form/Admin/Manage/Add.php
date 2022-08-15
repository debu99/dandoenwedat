<?php
class Sesevent_Form_Admin_Manage_Add extends Engine_Form {
  public function init() {
    $this->setTitle('Integrate New Plugin')
            ->setDescription('Here, you can configure the required details for the plugin to be integrated.');
    $integrateothermoduleId = Zend_Controller_Front::getInstance()->getRequest()->getParam('integrateothermodule_id', 0);
    if (!$integrateothermoduleId) {
      $integrateothermoduleItem = array();
      $integrateothermoduleArray = array();
      $integrateothermoduleArray[] = '';
      
      $notincludepluginarray = array('chat', 'poll', 'forum', 'sesadvancedactivity', 'sesadvancedcomment', 'sesfeedbg', 'sesfeelingactivity', 'sesfeedgif', 'sesbasic', 'sesadvsitenotification', 'sesariana', 'sesbrowserpush', 'seschristmas', 'sescleanwide', 'sescontentcoverphoto', 'sescontestjoinfees', 'sescontestjurymember', 'sescontestpackage', 'sesdemouser', 'seselegant', 'sesemailverification', 'sesemoji', 'seserror', 'seseventticket', 'sesexpose', 'sesfaq', 'sesfbstyle', 'sesfooter', 'seshtmlbackground', 'seslangtranslator', 'sesletteravatar', 'sesmediaimporter', 'sesmember', 'sesmembershorturl', 'sesmetatag', 'sesmultipleform', 'sespagebuilder', 'sespoke', 'sesprofilelock', 'sespymk', 'sessiteiframe', 'sessociallogin', 'sessocialshare', 'sessoundcloudsearch', 'sesspectromedia', 'sesteam', 'sestour', 'sestweet', 'sesusercoverphoto', 'seseventimporter');
      
			//get all enabled modules
      $coreTable = Engine_Api::_()->getDbTable('modules', 'core');
      $select = $coreTable->select()
              ->from($coreTable->info('name'), array('name', 'title'))
              ->where('enabled =?', 1)
              ->where('name NOT IN (?)', $notincludepluginarray)
              ->where('type =?', 'extra');
      $resultsArray = $select->query()->fetchAll();
      if (!empty($resultsArray)) {
        foreach ($resultsArray as $result) {
          $integrateothermoduleArray[$result['name']] = $result['title'];
        }
      }
      if (!empty($integrateothermoduleArray)) {
        $this->addElement('Select', 'module_name', array(
            'label' => 'Choose Plugin',
            'description' => 'Below, you can choose the plugin to be integrated.',
            'allowEmpty' => false,
            'onchange' => 'changemodule(this.value)',
            'multiOptions' => $integrateothermoduleArray,
        ));
      } else {
        $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("Here are no module to configure with our plugin lightbox.") . "</span></div>";
        $this->addElement('Dummy', 'module', array(
            'description' => $description,
        ));
        $this->module->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      }
      $module = Zend_Controller_Front::getInstance()->getRequest()->getParam('module_name', null);
      if (!empty($module)) {
        $this->module_name->setValue($module);
				//get manifest item for given module
        $integrateothermodule = Engine_Api::_()->sesevent()->getPluginItem($module);
        if (empty($integrateothermodule))
          $this->addElement('Dummy', 'dummy_title', array(
              'description' => 'No item type define for this plugin.',
          ));
      }
    }
		$param = false;
    if (@$integrateothermoduleId)
      $param = true;
    elseif (@$integrateothermodule)
      $param = true;
    if ($param) {
      if (!$integrateothermoduleId) {
        $this->addElement('Select', 'content_type', array(
            'label' => 'Item Type of Plugin',
            'description' => 'Select the item type of the above chosen plugin which is defined in its manifest.php file. [This item type is the parent to which events are associated. For example for groups in SocialEngine Groups plugin, simply choose "groups".]',
            'multiOptions' => $integrateothermodule,
        ));
        
        $this->addElement('Text', 'content_url', array(
          'label' => 'Item Type URL for Browse Events Page',
          'description' => 'Enter the Item Type URL for the Browse Events Page. This is the URL slug which will come after your site url and before the search word browse-events like: "http://www.yourwebsite.com/ITEM_TYPE_URL_SLUG/browse-events". For example for SE - Groups Plugin, enter "groups", so that the URL will be "http://www.yourwebsite.com/groups/browse-events".',
          'required' => true,
          'allowEmpty' => false,
        ));

      }
      $this->addElement('Checkbox', 'enabled', array(
          'description' => 'Enable This Plugin?',
          'label' => 'Yes, enable this plugin now.',
          'value' => 1,
      ));
      $this->addElement('Button', 'execute', array(
          'label' => 'Add Plugin',
          'type' => 'submit',
          'ignore' => true,
          'decorators' => array('ViewHelper'),
      ));
      $this->addElement('Cancel', 'cancel', array(
          'label' => 'Cancel',
          'prependText' => ' or ',
          'ignore' => true,
          'link' => true,
          'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
          'decorators' => array('ViewHelper'),
      ));
    }
  }
}
