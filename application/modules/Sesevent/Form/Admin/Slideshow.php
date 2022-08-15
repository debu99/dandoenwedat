<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Slideshow.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Slideshow extends Engine_Form
{
  public function init()
  {
		$headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
		
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$script = "var hashSign = '#';";
		$view->headScript()->appendScript($script);
		$this->addElement(
                    'MultiCheckbox',
                    'infoshow',
                    array(
                        'label' => 'Choose from below the details that you want to show in this widget.',
                        'multiOptions' => array(
                            'searchForVenue' => 'Search by Country',
                            'findVenue' => 'Find Events Near You Button',
														'getStarted' => 'Get Started Button',
                        ),
                    )                
		);
		 $this->addElement('Text', "sfvtextcolor", array(
        'label' => '"Search by Country" Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "sfvbtncolor", array(
        'label' => '"Search by Country" Button Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
			 $this->addElement('Text', "fvbtextcolor", array(
        'label' => '"Find Events Near You Button" Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "fvbbtncolor", array(
        'label' => '"Find Venue For Me Button" Button Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
		
	 $this->addElement('Text', "gsttextcolor", array(
        'label' => '"Get Started" Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "gstbgcolor", array(
        'label' => '"Get Started" Background Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
		
		$this->addElement(
			'Select',
			'getStartedLink',
			array(
					'label' => 'Open get started in popup(setting work if you choose "Get Started Button" from above setting)',
					'multiOptions' => array(
									'1' => 'Yes',
									'0' => 'No',
							),
							'value' => 1,
			)
		);
							$this->addElement(
								'Text',
								'percentageWidth',
								array(
										'label' => 'Main Photo Width in percentage',
										'value' => '90',
										'validators' => array(
												array('Int', true),
												array('GreaterThan', true, array(50)),
                     )
								)
							);
							
							
							$this->addElement(
								'Text',
								'titleS',
								array(
										'label' => 'Slide Title',
										'value' => '',
								)
							);
							$this->addElement('Text', "titlecolor", array(
									'label' => '"Title" Text Color',
									'value' => '#fff',
									'class' => 'SEScolor',
							));
							$this->addElement(
								'Textarea',
								'descriptionS',
								array(
										'label' => 'Slide Description',
										'value' => '',
								)
							);
							$this->addElement('Text', "descriptioncolor", array(
									'label' => '"Description" Text Color',
									'value' => '#fff',
									'class' => 'SEScolor',
							));
							$this->addElement(
								'Text',
								'margin_top',
								array(
										'label' => 'Margin top(px)',
										'value' => '',
								)
							);
							$this->addElement(
								'Text',
								'height',
								array(
										'label' => 'Height of slideshow(px)',
										'value' => '480',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
								)
							);
							$this->addElement(
								'Text',
								'animationSpeed',
								array(
										'label' => 'Animation Speed',
										'value' => '3000',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
								)
							);
							$this->addElement(
								'Select',
								'navigation',
								array(
										'label' => 'Show Navigation Button',
										'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No',
                        ),
                        'value' => 1,
								)
							);
							$this->addElement(
                    'Select',
                    'isfullwidth',
                    array(
                        'label' => 'Want to show category carousel in full width?',
												'multiOptions'=>array(
												1=>'Yes,want to show this widget in full width.',
												0=>'No,don\'t want to show this widget in full width.'
												),
                        'value' => 1,
                    )
                );
	}
}
?>