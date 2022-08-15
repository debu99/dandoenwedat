<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Dpoemails.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Dpoemails extends Engine_Form {

  public function init() {
      
      $this->addElement('Textarea', 'dpoemails', array(
					'label' => 'DPO Emails',
          'description' => 'You will receive all the data protection requests on your email entered from the admin panel >> General Settings of your website. But, if you also want to send the emails directly to your DPO also, then enter the email below. If you want to enter more than 1 email, then make them comma separated.',
           'value' => '',
			));
      
			// Add submit button
			$this->addElement('Button', 'submit', array(
					'label' => 'Save Emails',
					'type' => 'submit',
					'ignore' => true
			));    
  }
}