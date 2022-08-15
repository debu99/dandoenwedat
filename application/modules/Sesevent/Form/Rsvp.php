<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Rsvp.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Rsvp extends Engine_Form {

  public function init() {

    $this
            ->setMethod('POST')
            ->setAction($_SERVER['REQUEST_URI']);

    $this->addElement('Radio', 'rsvp', array(
        'multiOptions' => array(
            2 => 'Attending',
            1 => 'Maybe Attending',
            0 => 'Not Attending',
        ),
    ));
  }

}
