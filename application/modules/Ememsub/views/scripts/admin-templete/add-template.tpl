<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: add-template.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/styles/admin/styles.css'); ?>
<div class="settings ememsub_popup_form">
  <?php echo $this->form->render($this) ?>
</div>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jscolor/jscolor.js');
?>
