<?php
class Sesevent_Widget_ProfileJoinLeaveController extends Engine_Content_Widget_Abstract
{

    public function indexAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $isLoggedIn = $viewer->getIdentity() === 0 ? false : true;
        $this->view->isLoggedIn = $isLoggedIn;
        if (!$isLoggedIn) {
            return;
        }

        $genderUser = $viewer->getGender()['label'];

        $event = Engine_Api::_()->core()->getSubject('sesevent_event');
        $this->view->isAttending = $event->membership()->getRow($viewer)->rsvp === 2;
        $results = Engine_Api::_()->getDbTable('events', 'sesevent')->getEventSelect(array(
            'manageorder' => 'joinedEvents',
            'fetchAll' => 0,
            'date_range_from' => $event->starttime,
            'date_range_to' => $event->endtime,
        ));

        if ($results->count() > 0) {
            $this->view->alreadyGoing = $results[0]->getHref();
        } else {
            $this->view->alreadyGoing = false;
        }

        $this->view->isFull = $event->eventIsFull($viewer);
        $this->view->isOnWaitingList = $event->membership()->getRow($viewer)->rsvp === 5;
        $this->view->userIsInAgeRange = $viewer->userIsInAgeRange($event);

        $tickets = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $event->getIdentity()));
        $eventHasTickets = count($tickets) > 0;
        if ($eventHasTickets) {
            $ticketSaleOver = strtotime($tickets[0]->endtime) < strtotime('now');
            $ticketSaleNotStarted = strtotime($tickets[0]->starttime) > strtotime('now');
        }

        $this->view->showLadiesOnly = $genderUser === "Male" && $event->gender_destribution == "Ladies only";
        $this->view->showMenOnly = $genderUser === "Female" && $event->gender_destribution == "Men only";

        $currentTime = time();
        if (strtotime($event->starttime) > $currentTime) {
            $status = 'notStarted';
        } else if (strtotime($event->endtime) < $currentTime) {
            $status = 'expire';
        } else {
            $status = 'onGoing';
        }

        $isHost = $this->view->isLoggedIn && $event->user_id === $viewer->user_id;
        if ($isHost || $eventHasTickets || !$this->view->userIsInAgeRange || $status != "notStarted") {
            $this->setNoRender();
        }
    }

}
