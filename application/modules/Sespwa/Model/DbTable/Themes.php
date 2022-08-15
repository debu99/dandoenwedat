<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Themes.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Model_DbTable_Themes extends Engine_Db_Table
{

  /**
   * Deletes all temporary files in the Scaffold cache
   *
   * @example self::clearScaffoldCache();
   * @return void
   */
  public static function clearScaffoldCache()
  {
    try {
      Engine_Package_Utilities::fsRmdirRecursive(APPLICATION_PATH . '/temporary/scaffold', false, array('index.html'));
    } catch( Exception $e ) {

    }
  }
}
