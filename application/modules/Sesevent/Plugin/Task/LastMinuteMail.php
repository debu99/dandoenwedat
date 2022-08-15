<?php

/**
 * Class Sesevent_Plugin_Task_LastMinuteMail
 */
class Sesevent_Plugin_Task_LastMinuteMail extends Core_Plugin_Task_Abstract
{
    /**
     * @return Core_Plugin_Job_Abstract|void
     */
    public function execute()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $eventTable = Engine_Api::_()->getItemTable('event');

        //send email remind before 24h
        $select = $eventTable->select()
            ->where('starttime <= ? ', date('Y-m-d h:m:s', time() + 24 * 60 * 60))
            ->where('starttime > ? ', date('Y-m-d h:m:s', time()))
            ->where('is_approved = 1')
            ->where('is_send_before_24h = 0');
        $events = $eventTable->fetchAll($select);
        if (count($events) > 0){
            foreach ($events as $event){
                $owner = Engine_Api::_()->user()->getUser($event->user_id);
                $members = $event->membership()->getMembership(array("event_id" => $event->getIdentity()));
                //send for owner
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                    $owner,
                    $owner,
                    $event,
                    'sesevent_organizer_remind',
                    array(
                        'queue' => true,
                        'object_date' => $event->getTime('starttime', 'j M'),
                        'object_time' => $event->getTime('starttime', 'H:i')
                    )
                );
                //send for member
                foreach ($members as $member) {
                    if ($member->user_id != $event->user_id) {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
                            Engine_Api::_()->user()->getUser($member->user_id),
                            Engine_Api::_()->user()->getUser($member->user_id),
                            $event,
                            'sesevent_joined_remind',
                            array(
                                'queue' => true,
                                'object_date' => $event->getTime('starttime', 'j M'),
                                'object_time' => $event->getTime('starttime', 'H:i')
                            )
                        );
                    }
                }
                $event->is_send_before_24h = 1;
                $event->save();
            }
        }
    }
}