<?php

class Sesbasic_Model_DbTable_Usergateways extends Engine_Db_Table
{
  protected $_rowClass = 'Sesbasic_Model_Usergateway';

  protected $_serializedColumns = array('config');

  protected $_cryptedColumns = array('config');

  static private $_cryptKey;

  public function getEnabledGatewayCount()
  {
    return $this->select()
      ->from($this, new Zend_Db_Expr('COUNT(*)'))
      ->where('enabled = ?', 1)
      ->query()
      ->fetchColumn();
  }

    public function getUserGateway($params = array()) {
        $select = $this->select()->from($this->info('name'));
        if(!isset($params['enabled']))
            $select->where('enabled =?','1');
        if(isset($params['user_id']))
            $select->where('user_id =?',$params['user_id']);
        if(isset($params['gateway_type']))
            $select->where('gateway_type =?',$params['gateway_type']);
        if(isset($params['fetchAll']))
                return $this->fetchAll($select);
        return $this->fetchRow($select);
    }

  public function update(array $data, $where)
  {
    // Serialize
    $data = $this->_serializeColumns($data);

    // Encrypt each column
    foreach( $this->_cryptedColumns as $col ) {
      if( !empty($data[$col]) ) {
        $data[$col] = self::_encrypt($data[$col]);
      }
    }

    return parent::update($data, $where);
  }

  protected function _fetch(Zend_Db_Table_Select $select)
  {
    $rows = parent::_fetch($select);

    foreach( $rows as $index => $data ) {
      // Decrypt each column
      foreach( $this->_cryptedColumns as $col ) {
        if( !empty($rows[$index][$col]) ) {
          $rows[$index][$col] = self::_decrypt($rows[$index][$col]);
        }
      }
      // Unserialize
      $rows[$index] = $this->_unserializeColumns($rows[$index]);
    }

    return $rows;
  }

  // Crypt Utility
  static private function _encrypt($data)
  {
    if( !extension_loaded('mcrypt') ) {
      return $data;
    }

    $key = self::_getCryptKey();
    $cryptData = mcrypt_encrypt(MCRYPT_DES, $key, $data, MCRYPT_MODE_ECB);

    return $cryptData;
  }

  static private function _decrypt($data)
  {
    if( !extension_loaded('mcrypt') ) {
      return $data;
    }

    $key = self::_getCryptKey();
    $cryptData = mcrypt_decrypt(MCRYPT_DES, $key, $data, MCRYPT_MODE_ECB);
    $cryptData = rtrim($cryptData, "\0");

    return $cryptData;
  }

  static private function _getCryptKey()
  {
    if( null === self::$_cryptKey ) {
      $key = Engine_Api::_()->getApi('settings', 'core')->core_secret
        . '^'
        . Engine_Api::_()->getApi('settings', 'core')->payment_secret;
      self::$_cryptKey  = substr(md5($key, true), 0, 8);
    }

    return self::$_cryptKey;
  }
}
