<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Manage.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Filter_Manage extends Engine_Form {

  public function init() {
    $this->clearDecorators()
            ->addDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'dl')),
                'Form',
            ))
            ->setMethod('get')
            ->setAttrib('class', 'filters');

    $this->addElement('Text', 'search_text', array(
        'label' => 'Search:',
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
        ),
        'onchange' => '$(this).getParent("form").submit();',
    ));

    $this->addElement('Select', 'view', array(
        'label' => 'View:',
        'multiOptions' => array(
            '' => 'All My Events',
            '2' => 'Only Events I Lead',
        ),
        'decorators' => array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt', 'placement' => 'PREPEND'))
        ),
        'onchange' => '$(this).getParent("form").submit();',
    ));
  }

}
