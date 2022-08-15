<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_EventProfileStatusController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Don't render this if subject not set
        if (!Engine_Api::_()->core()->hasSubject('sesevent_event')) {
            return $this->setNoRender();
        }

        // Get subject
        $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        //event end time as per timezone
        $currentTime = time();
        if (strtotime($subject->starttime) > $currentTime) {
            $status = 'notStarted';
        } else if (strtotime($subject->endtime) < $currentTime) {
            $status = 'expire';
        } else {
            $status = 'onGoing';
        }
        $this->view->status = $status;
    }
}
