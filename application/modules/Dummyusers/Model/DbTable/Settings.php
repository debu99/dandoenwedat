<?php
    class Dummyusers_Model_DbTable_Settings extends Engine_Db_Table {

        public function getSettings(){
            $tableName = $this->info('name');
            $select = $this->select()
                    ->from($tableName)
                    ->limit(1);
            $row = $this->fetchRow($select);
            return $row;
        }

        public function setSettings($enabled, $amount){
            $this->update(
                array(
                    "amount_dummy_users" => $amount,
                    "is_enabled" => $enabled
                ),
                array('id =?' => 1)
            );
        }
    }