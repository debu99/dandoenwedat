<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

  <script type="text/javascript">
    var name = '<?php echo $this->name ?>';
    setTimeout(function() {
      parent.location.reload();
      parent.Smoothbox.close();
      
    }, 500);
  </script>
