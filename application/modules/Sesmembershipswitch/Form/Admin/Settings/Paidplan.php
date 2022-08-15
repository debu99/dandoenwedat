<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Paidplan.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesmembershipswitch_Form_Admin_Settings_Paidplan extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Paid Plan Settings');
            
		
   $table = Engine_Api::_()->getDbTable('packages','payment');
    $select = $table->select()->where('enabled =?',1)->where('price !=?',0);
    $result = $table->fetchAll($select);
    
    $subscriptions = array();
    foreach($result as $subscription){
      $subscriptions[$subscription->getIdentity()] = $subscription->getTitle();  
    }
    
    $description = 'Here, you can configure settings for switching from Paid Membership Plans on your website.';
    
    if(!count($subscriptions))
      $description = $description."<br><br> <span class=\"sesmembershipswitch_tip\">Currently you do not have any Paid plan. Please create a plan first to configure the settings below</span>";

    $this->setDescription($description);
    $this->loadDefaultDecorators();
	$this->getDecorator('Description')->setOption('escape', false);
    
      $this->addElement('Select', 'current_plan_id', array(
          'label' => 'Choose Subscribed Plan',
          'description' => 'Choose the plan from below which is subscribed by members on your website. After the subscription period of this plan, member level of the members will be changed. (Note: Membership plan will not be modified, so that the current plan will be restored whenever next payment is made.)',
          'allowEmpty'=>false,
          'required'=>true,
          'onchange'=> 'changePlan(this.value);',
          'multiOptions' => $subscriptions,
      ));
      
      // Element: level_id
    $multiOptions = array('' => '');
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if( $level->type == 'public' || $level->type == 'admin' || $level->type == 'moderator' ) {
        continue;
      }
      $multiOptions[$level->getIdentity()] = $level->getTitle();
    }
        
      $this->addElement('Select', 'change_plan_id', array(
          'label' => 'Member Level To Be Changed',
          'description' => 'Choose from below the Member Level which will be changed for members when their current subscribed plan expires. You should choose a lower Member Level from the Member Level which is associated with the plan chosen from above setting.',
          'allowEmpty'=>false,
          'required'=>true,
          'multiOptions' => $multiOptions,
      ));
      
      $this->addElement('Text', 'switch', array(
          'label' => 'Days Limit for Member Level Switching',
          'description' => 'Enter number of days after which Member Level of members in the above chosen plan will change. This time will be calculated after the normal expiration of the plan. If you want the plan to be immediately changed on expiration, then enter \'0\' below.',
          'validators' => array(
              array('Int', true),
              new Engine_Validate_AtLeast(0),
          ),
          'value'=>5,
      ));
			$this->addElement('Hidden','number',array('order'=>997,'value'=>1));
			$this->addElement('Hidden','type',array('order'=>998,'value'=>'days'));
            
      $this->addElement('dummy', 'notification_html', array(
          'decorators' => array(array('ViewScript', array(
                      'viewScript' => 'application/modules/Sesmembershipswitch/views/scripts/_notification.tpl',
                      'class' => 'form element',
                  )))
        ));
      if(count($subscriptions)){
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Settings',
          'type' => 'submit',
          'ignore' => true
      ));  
      }
  }
}