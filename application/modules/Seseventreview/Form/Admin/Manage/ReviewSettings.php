<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ReviewSettings.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Form_Admin_Manage_ReviewSettings extends Engine_Form {

  public function init() {

    $this->setTitle('Manage Reviews & Ratings Settings')
            ->setDescription('Here, you can configure settings for  reviews for events on your website.');

    $this->addElement('Radio', 'seseventreview_allow_review', array(
        'label' => 'Allow Reviews',
        'description' => 'Do you want to allow users to give reviews on this events on your website? (Users will be also be able to rate events, if you choose “Yes” for this setting.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
		'onchange' => "allowReview(this.value)",
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1),
    ));

    $this->addElement('Radio', 'seseventreview_allow_owner', array(
        'label' => 'Allow Reviews on Own Events',
        'description' => 'Do you want to allow users to give reviews on own events on your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.owner', 1),
    ));

    $this->addElement('Radio', 'seseventreview_show_pros', array(
        'label' => 'Allow Pros in Reviews',
        'description' => 'Do you want to allow users to enter Pros in their reviews?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.pros', 1),
    ));

    $this->addElement('Radio', 'seseventreview_show_cons', array(
        'label' => 'Allow Cons in Reviews',
        'description' => 'Do you want to allow users to enter Cons in their reviews?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.cons', 1),
    ));

   /* $this->addElement('Radio', 'seseventreview_review_title', array(
        'label' => 'Allow Review Title',
        'description' => 'Do you want to allow users to enter title on review on your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.title', 1),
    ));
*/
    $this->addElement('Radio', 'seseventreview_review_summary', array(
        'label' => 'Allow Description in Reviews',
        'description' => 'Do you want to allow users to enter description in their reviews?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onchange' => "showEditor(this.value)",
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.summary', 1),
    ));

    $this->addElement('Radio', 'seseventreview_show_tinymce', array(
        'label' => 'Enable WYSIWYG Editor for Description',
        'description' => 'Do you want to enable WYSIWYG Editor for description for reviews?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.tinymce', 1),
    ));

    $this->addElement('Radio', 'seseventreview_show_recommended', array(
        'label' => 'Allow Recommended Option',
        'description' => 'Do you want to allow users to choose to recommend the events in their reviews?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.recommended', 1),
    ));

    $this->addElement('Radio', 'seseventreview_allow_share', array(
        'label' => 'Allow Share Option',
        'description' => 'Do you want to allow users to share reviews on your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.share', 1),
    ));

    $this->addElement('Radio', 'seseventreview_show_report', array(
        'label' => 'Allow Report Option',
        'description' => 'Do you want to allow users to report reviews on your website?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.report', 1),
    ));
		$settings = Engine_Api::_()->getApi('settings', 'core');
		/* text for rating starts */
    $this->addElement('Text', "sesevent_rating_stars_one", array(
        'label' => 'Mouseover Text for First Star',
        'description' => "Enter the text that you want to display when users mouse over on first rating star.",
        'value' => $settings->getSetting('sesevent.rating.stars.one', 'terrible'),
    ));
    $this->addElement('Text', "sesevent_rating_stars_two", array(
        'label' => 'Mouseover Text for Second Star',
        'description' => "Enter the text that you want to display when users mouse over on second rating star.",
        'value' => $settings->getSetting('sesevent.rating.stars.second', 'poor'),
    ));
    $this->addElement('Text', "sesevent_rating_stars_three", array(
        'label' => 'Mouseover Text for Third Star',
        'description' => "Enter the text that you want to display when users mouse over on third rating star.",
        'value' => $settings->getSetting('sesevent.rating.stars.three', 'average'),
    ));
    $this->addElement('Text', "sesevent_rating_stars_four", array(
        'label' => 'Mouse over rating text on fourth star',
        'description' => "Enter the text that you want to display when users mouse over on fourth rating star.",
        'value' => $settings->getSetting('sesevent.rating.stars.four', 'very good'),
    ));
    $this->addElement('Text', "sesevent_rating_stars_five", array(
        'label' => 'Mouseover Text for Fifth Star',
        'description' => "Enter the text that you want to display when users mouse over on fifth rating star.",
        'value' => $settings->getSetting('sesevent.rating.stars.five', 'excellent'),
    ));
		
    $this->addElement('Button', 'execute', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));

  }

}
