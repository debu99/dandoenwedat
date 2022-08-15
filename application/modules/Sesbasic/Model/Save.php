<?php

class Sesbasic_Model_Save extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;

  public function getOwner($type = null) {

    $poster = $this->getPoster();
    if (null === $type && $type !== $poster->getType()) {
      return $poster->getOwner($type);
    }
    return $poster;
  }

  public function getPoster() {
    return Engine_Api::_()->getItem($this->poster_type, $this->poster_id);
  }

  public function __toString() {
    return $this->getPoster()->__toString();
  }

}
