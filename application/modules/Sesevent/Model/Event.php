<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Event.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Event extends Core_Model_Item_Abstract
{
    protected $_owner_type = 'user';
    public function membership()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('membership', 'sesevent'));
    }
    public function getRichContent($view = false, $params = array())
    {
        $eventEmbedded = '';
        if (!$view) {
            $desc = strip_tags($this->description);
            $desc = "<div class='sesevent_feed_desc'>" . (Engine_String::strlen($desc) > 255 ? Engine_String::substr($desc, 0, 255) . '...' : $desc) . "</div>";
            $view = Zend_Registry::get('Zend_View');
            $view->event = $this;
            $eventEmbedded = $view->render('application/modules/Sesevent/views/scripts/_feedEvent.tpl');
        }

        return $eventEmbedded;
    }

    public function eventIsFull($user)
    {
        $genderUser = $user->getGender()['label'];
        if ($this->getAttendingCount() >= $this->max_participants && $this->max_participants !== null) {
            return true;
        }

        if (
            $this->gender_destribution === "50/50" &&
            $genderUser === "Male" &&
            $this->male_count >= 0.5 * $this->max_participants
        ) {
            return true;
        } else if (
            $this->gender_destribution === "50/50" &&
            $genderUser === "Female" &&
            $this->female_count >= 0.5 * $this->max_participants
        ) {
            return true;
        }

        return false;
    }

    public function _postInsert()
    {
        parent::_postInsert();
        // Create auth stuff
        $context = Engine_Api::_()->authorization()->context;
        $context->setAllowed($this, 'everyone', 'view', true);
        $context->setAllowed($this, 'registered', 'comment', true);
        $viewer = Engine_Api::_()->user()->getViewer();
    }
    public function setPhoto($photo)
    {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
            $name = basename($file);
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
            $name = basename($photo['name']);
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
            $name = basename($file);
        } else {
            throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
        }

        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_id' => $this->getIdentity(),
            'parent_type' => 'sesevent_event',
        );
        // Save
        $storage = Engine_Api::_()->storage();
        // Resize image (main)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(720, 720)
            ->write($path . '/m_' . $name)
            ->destroy();
        // Resize image (profile)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(500, 500)
            ->write($path . '/p_' . $name)
            ->destroy();
        // Resize image (normal)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(200, 200)
            ->write($path . '/in_' . $name)
            ->destroy();
        // Resize image (icon)
        $image = Engine_Image::factory();
        $image->open($file);
        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;
        $image->resample($x, $y, $size, $size, 48, 48)
            ->write($path . '/is_' . $name)
            ->destroy();
        // Store
        $iMain = $storage->create($path . '/m_' . $name, $params);
        $iProfile = $storage->create($path . '/p_' . $name, $params);
        $iIconNormal = $storage->create($path . '/in_' . $name, $params);
        $iSquare = $storage->create($path . '/is_' . $name, $params);
        $iMain->bridge($iProfile, 'thumb.profile');
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iMain->bridge($iSquare, 'thumb.icon');
        // Remove temp files
        @unlink($path . '/p_' . $name);
        @unlink($path . '/m_' . $name);
        @unlink($path . '/in_' . $name);
        @unlink($path . '/is_' . $name);
        // Update row
        $this->modified_date = date('Y-m-d H:i:s');
        $this->photo_id = $iMain->file_id;
        $this->save();
        // Add to album
        $viewer = Engine_Api::_()->user()->getViewer();
        $photoTable = Engine_Api::_()->getItemTable('sesevent_photo');
        $eventAlbum = $this->getSingletonAlbum();
        $eventAlbum->title = Zend_Registry::get('Zend_Translate')->_('Untitled');
        $eventAlbum->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $eventAlbum->save();
        $photoItem = $photoTable->createRow();
        $photoItem->setFromArray(array(
            'event_id' => $this->getIdentity(),
            'album_id' => $eventAlbum->getIdentity(),
            'user_id' => $viewer->getIdentity(),
            'file_id' => $iMain->getIdentity(),
            'collection_id' => $eventAlbum->getIdentity(),
            'user_id' => $viewer->getIdentity(),
        ));
        $photoItem->save();
        return $this;
    }

    public function setTicketLogo($photo, $type = 'ticket')
    {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
            $name = $photo->getFileName();
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
            $name = $photo['name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
            $name = $photo;
        } else {
            throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
        }
        $name = basename($name);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_id' => $this->getIdentity(),
            'parent_type' => 'sesevent_event',
        );

        // Save
        $storage = Engine_Api::_()->storage();
        // Resize image (main)
        copy($file, $path . '/m_' . $name);
        // Store
        $iMain = $storage->create($path . '/m_' . $name, $params);
        // Remove temp files
        @unlink($path . '/m_' . $name);
        // Update row
        if ($type == 'ticket') {
            $this->ticket_logo = $iMain->file_id;
        } else {
            $this->background_photo_id = $iMain->file_id;
        }

        $this->save();
        return $this;
    }
    public function setCoverPhoto($photo)
    {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
            $fileName = $file;
        } else if ($photo instanceof Storage_Model_File) {
            $file = $photo->temporary();
            $fileName = $photo->name;
        } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
            $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
            $file = $tmpRow->temporary();
            $fileName = $tmpRow->name;
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
            $fileName = $photo['name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
            $fileName = $photo;
            $unlink = false;
        } else {
            throw new User_Model_Exception('invalid argument passed to setPhoto');
        }
        $name = basename($file);
        $extension = ltrim(strrchr($fileName, '.'), '.');
        $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');

        if (!$fileName) {
            $fileName = $file;
        }
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_type' => $this->getType(),
            'parent_id' => $this->getIdentity(),
            'user_id' => $this->user_id,
            'name' => $fileName,
        );
        // Resize image (main)
        $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(1400, 1400)
            ->write($mainPath)
            ->destroy();

        // Store
        try {
            $iMain = $filesTable->createFile($mainPath, $params);
        } catch (Exception $e) {
            @unlink($file);
            // Remove temp files
            @unlink($mainPath);

            // Throw
            if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
                throw new Sesevent_Model_Exception($e->getMessage(), $e->getCode());
            } else {
                throw $e;
            }
        }
        if (!isset($unlink)) {
            @unlink($file);
        }

        // Remove temp files
        @unlink($mainPath);

        // Update row
        $this->cover_photo = $iMain->file_id;
        $this->save();
        // Delete the old file?
        if (!empty($tmpRow)) {
            $tmpRow->delete();
        }
        return $this;

    }
    public function setCover($photo)
    {
        if ($photo instanceof Zend_Form_Element_File) {
            $file = $photo->getFileName();
        } else if (is_array($photo) && !empty($photo['tmp_name'])) {
            $file = $photo['tmp_name'];
        } else if (is_string($photo) && file_exists($photo)) {
            $file = $photo;
        } else {
            throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
        }
        $name = basename($file);
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_id' => $this->getIdentity(),
            'parent_type' => 'sesevent_event',
        );

        // Save
        $storage = Engine_Api::_()->storage();
        // Resize image (main)
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(1400, 1400)
            ->write($path . '/m_' . $name)
            ->destroy();
        // Store
        $iMain = $storage->create($path . '/m_' . $name, $params);
        // Remove temp files
        @unlink($path . '/m_' . $name);
        // Update row
        $this->modified_date = date('Y-m-d H:i:s');
        $this->cover_photo = $iMain->file_id;
        $this->save();
        return $this;
    }
    public function getDescription($length = 255)
    {
        // @todo decide how we want to handle multibyte string functions
        $tmpBody = strip_tags($this->description);
        return (Engine_String::strlen($tmpBody) > $length ? Engine_String::substr($tmpBody, 0, $length) . '...' : $tmpBody);
    }
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Gets an absolute URL to the page to view this item
     *
     * @return string
     */
    public function getHref($params = array())
    {
        $params = array_merge(array(
            'route' => 'sesevent_profile',
            'reset' => true,
            'id' => $this->custom_url,
        ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        if (!empty($_SESSION["removeSiteHeaderFooter"])) {
            $params['event_id'] = $this->event_id;
        }
        return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble($params, $route, $reset);
    }

    protected function _delete()
    {
        if ($this->_disableHooks) {
            return;
        }

        // Delete all memberships
        $this->membership()->removeAllMembers();

        // Delete all albums
        $albumTable = Engine_Api::_()->getItemTable('sesevent_album');
        $albumSelect = $albumTable->select()->where('event_id = ?', $this->getIdentity());
        foreach ($albumTable->fetchAll($albumSelect) as $eventAlbum) {
            $photoTable = Engine_Api::_()->getDbtable('albums', 'sesevent');
            $photoSelect = $photoTable->select()->where('album_id = ?', $eventAlbum->getIdentity());
            foreach ($photoTable->fetchAll($photoSelect) as $photo) {
                $photo->delete();
            }
            $eventAlbum->delete();
        }

        // Delete all topics
        $topicTable = Engine_Api::_()->getItemTable('sesevent_topic');
        $topicSelect = $topicTable->select()->where('event_id = ?', $this->getIdentity());
        foreach ($topicTable->fetchAll($topicSelect) as $eventTopic) {
            $eventTopic->delete();
        }
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->query("DELETE FROM engine4_sesevent_favourites WHERE resource_type = 'sesevent_event' && resource_id = " . $this->getIdentity());
        $db->query("DELETE FROM engine4_sesevent_hosts WHERE owner_id = " . $this->getIdentity());

        parent::_delete();
    }
    public function totaltickets()
    {
        $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent');
        $ticketName = $ticket->info('name');
        return $ticket->select()
            ->from($ticketName, new Zend_Db_Expr('COUNT(*)'))
            ->where('event_id =?', $this->event_id)
            ->limit(1)
            ->query()
            ->fetchColumn();
    }

    public function getSingletonAlbum()
    {
        $table = Engine_Api::_()->getItemTable('sesevent_album');
        $select = $table->select()
            ->where('event_id = ?', $this->getIdentity())
            ->order('album_id ASC')
            ->limit(1);

        $album = $table->fetchRow($select);

        if (null === $album) {
            $album = $table->createRow();
            $album->setFromArray(array(
                'event_id' => $this->getIdentity(),
            ));
            $album->save();
        }

        return $album;
    }

    public function categoryName()
    {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'sesevent');
        return $categoryTable->select()
            ->from($categoryTable, 'title')
            ->where('category_id = ?', $this->category_id)
            ->limit(1)
            ->query()
            ->fetchColumn();
    }

    public function getAttendingCount()
    {
        return $this->membership()->getMemberCount(true, array('rsvp' => 2));
    }

    /**
     * Gets a proxy object for the tags handler
     *
     * @return Engine_ProxyObject
     * */
    public function tags()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
    }

    public function increaseGenderCount($user)
    {
        $genderUser = $user->getGender()['label'];

        if (isset($this->female_count) && $genderUser == "Female") {
            $this->female_count++;
        }

        if (isset($this->male_count) && $genderUser == "Male") {
            $this->male_count++;
        }

        if (isset($this->other_count) && $genderUser == "Other") {
            $this->other_count++;
        }
        $this->save();
    }

    public function decreaseGenderCount($user)
    {
        $genderUser = $user->getGender()['label'];

        if (isset($this->female_count) && $genderUser == "Female") {
            $this->female_count--;
        }

        if (isset($this->male_count) && $genderUser == "Male") {
            $this->male_count--;
        }

        if (isset($this->other_count) && $genderUser == "Other") {
            $this->other_count--;
        }
        $this->save();
    }
    public function getMaybeCount()
    {
        return $this->membership()->getMemberCount(true, array('rsvp' => 1));
    }

    public function getNotAttendingCount()
    {
        return $this->membership()->getMemberCount(true, array('rsvp' => 0));
    }

    public function getAwaitingReplyCount()
    {
        return $this->membership()->getMemberCount(false, array('rsvp' => 3));
    }
/**
 * Gets a proxy object for the comment handler
 *
 * @return Engine_ProxyObject
 * */
    public function comments()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
    }
    /**
     * Gets a proxy object for the like handler
     *
     * @return Engine_ProxyObject
     * */
    public function likes()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
    }
    public function getCoverPhotoUrl()
    {
        $photo_id = $this->cover_photo;
        if ($photo_id) {
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->cover_photo);
            if ($file) {
                return $file->map();
            }

        }
        $defaultPhoto = Engine_Api::_()->authorization()->getPermission(Engine_Api::_()->getItem('user', $this->user_id), 'sesevent_event', 'event_cover');
        if (!$defaultPhoto) {
            $defaultPhoto = 'application/modules/Sesevent/externals/images/event-cover.jpg';
        }
        return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);
    }

    public function getPhotoUrl($type = null)
    {
        $photo_id = $this->photo_id;
        if ($photo_id) {
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, $type);
            if ($file) {
                return $file->map();
            } else {
                $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, 'thumb.profile');
                if ($file) {
                    return $file->map();
                }

            }
        }
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $defaultPhoto = Engine_Api::_()->sesevent()->getFileUrl($settings->getSetting('sesevent_event_default_photo', 'application/modules/Sesevent/externals/images/nophoto_event_thumb_profile.png'));
        return $defaultPhoto;
    }

    public function getAgeCategoriesFromInterval()
    {
        return $this->getIntervalToAgeCategories(array(
            'from' => $this['age_category_from'],
            'to' => $this['age_category_to'],
        ));
    }

    public static function getIntervalToAgeCategories($interval)
    {
        if ($interval == null) {
            return null;
        }

        $categories = array(
            '18' => '18-28',
            '29' => '29-39',
            '40' => '40-50',
            '51' => '51-61',
            '62' => '62-72',
            '73' => '73-88',
        );
        $filteredCategories = $categories;
        foreach ($categories as $key => $value) {
            if ((int) $key < $interval['from'] || (int) $key > $interval['to']) {
                unset($filteredCategories[$key]);
            }

        }
        return $filteredCategories;
    }

    public static function getAgeCategoriesToInterval($categories)
    {
        $viewer = Engine_Api::_()->user()->getViewer();

        $standardRange = 10;
        $from = 99;
        $to = 0;

        foreach ($categories as $category) {
            if ((int) $category <= $from) {
                $from = $category;
            }

            if ((int) $category + $standardRange >= $to) {
                $to = $category + $standardRange;
            }

            if ((int) $category === 73) {
                $to = 88;
            }
            // last category is an exception and has a range of 15
        };

        if ($viewer->isAdmin()) {
            return array(
                'from' => $from,
                'to' => $to,
            );
        } else {
            return Sesevent_Model_Event::addUsersAgeCategoryToInterval(array(
                'from' => $from,
                'to' => $to,
            ));
        }
    }

    public static function addUsersAgeCategoryToInterval($interval)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $age = $viewer->getAgeCategory();

        if ($age < $interval['from']) {
            $interval['from'] = $age;
        }

        if ($age + 10 > $interval['to']) {
            $interval['to'] = $age + 10;
        }

        if ($age == 73) {
            $interval['to'] = 88;
        }

        return $interval;
    }

    public function nieuwOrLastMinute(): int
    {
        $now = time();
        $creationDate = $this->getCreationDate();
        $startTime = $this->starttime;
        if (0 <= (strtotime($startTime) - $now) && (strtotime($startTime) - $now) <= 3 * 24 * 60 * 60){
            return 2;
        } elseif (($now - strtotime($creationDate)) <= 7 * 24 * 60 * 60){
            return 1;
        }
        return 0;
    }
    
    public function getTime($style, $format = 'Y-m-d H:i:s') {
        if (!in_array($style, ['starttime', 'endtime'])) {
            return false;
        }
        
        $offset = $this->get_timezone_offset($this->timezone);
        $time = strtotime($this->$style) - $offset;
        return date($format, $time);
    }
    
    public function get_timezone_offset($remote_tz, $origin_tz = null) {
        if($origin_tz === null) {
            if(!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }
}
