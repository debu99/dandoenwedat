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
class Sesevent_Widget_EventInfoController extends Engine_Content_Widget_Abstract
{

    public function indexAction()
    {
        // Get subject and check auth
        $viewer = Engine_Api::_()->user()->getViewer();

        $isLoggedIn = $viewer->getIdentity() === 0 ? false : true;
        $this->view->isLoggedIn = $isLoggedIn;

        $subject = Engine_Api::_()->core()->getSubject('sesevent_event');
        if (!$subject) {
            return $this->setNoRender();
        }
        $this->view->subject = $subject;
        $this->view->eventTags = $subject->tags()->getTagMaps();

        $isAttending = $subject->membership()->getRow($viewer)->rsvp === 2;
        $this->view->isAttending = $isAttending;
        $this->view->event_title = $subject->title;
        $this->view->age_from = $subject->age_category_from;
        $this->view->age_to = $subject->age_category_to;
        $this->view->max_participants = $subject->max_participants;
        $this->view->min_participants = $subject->min_participants;
        $attending = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id' => $subject->getIdentity(), 'type' => 'attending'))->getTotalItemCount();
        $this->view->available_spots = max($subject->max_participants - $attending, 0);

        $this->view->location = $subject->location;
        $this->view->venue = strlen($subject->venue_name) > 0 ? $subject->venue_name : false;
        $category = Engine_Api::_()->getItem('sesevent_category', $subject->category_id);
        $catIcon = Engine_Api::_()->storage()->get($category->cat_icon);
        if ($catIcon) {
            $this->view->catIcon = $catIcon->getPhotoUrl('thumb.icon');
        }

        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');

        if ($subject->is_additional_costs) {
            $locale = Zend_Registry::get('Zend_Translate')->getLocale();
            $this->view->additional_costs = true;
            $this->view->additional_costs_amount = Zend_Locale_Format::toNumber($subject->additional_costs_amount,
                array('locale' => $locale,
                    'precision' => 2)
            );
            $this->view->additional_costs_amount_currency = $curArr[$subject->additional_costs_amount_currency];
            $this->view->additional_costs_description = $subject->additional_costs_description;
        }

        if ($subject->gender_destribution === "50/50") {
            $this->view->fiftyfifty = true;
            $this->view->male_available = max(ceil($subject->max_participants * 0.5) - $subject->male_count, 0);
            $this->view->female_available = max(ceil($subject->max_participants * 0.5) - $subject->female_count, 0);
        }
        $this->view->meeting_point = $subject->meeting_point ? $subject->meeting_point : false;
        $this->view->meeting_time = $subject->meeting_time ? $subject->meeting_time : false;
        $this->view->tel_host = $subject->tel_host ? $subject->tel_host : false;

        $this->view->eventHasTicket = $eventHasTicket = count(Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $subject->getIdentity()))) > 0;

        $this->view->eventOngoing = strtotime($subject->endtime) > strtotime('now');
        if ($subject->gender_destribution === "50/50" ||
            $subject->gender_destribution === "Ladies only" ||
            $subject->gender_destribution === "Men only"
        ) {
            $this->view->gender_destribution = $subject->gender_destribution;
        } else {
            $this->view->gender_destribution = false;
        }
        

    }

}
