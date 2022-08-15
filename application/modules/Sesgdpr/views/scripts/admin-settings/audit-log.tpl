<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: audit-log.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesgdpr/views/scripts/dismiss_message.tpl';?>


<h3><?php echo $this->translate('Audit Log') ?></h3>
<p><?php echo $this->translate('This page lists audit log for the user selected by you. Use the search box to find the user for which you want to see the audit log for various consents on your website.'); ?></p>
<br />

<br />
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />

<?php if($this->paginator->getTotalItemCount()){ ?>
<textarea><?php 
    $table = Engine_Api::_()->getDbTable('users','user');
    $counter = 0;
    foreach($this->paginator as $item){ 
         if($item->email && !$counter){
            $date = $table->select()->from($table->info('name'),'creation_date')->where('email =?',$item->email)->limit(1)->query()
          ->fetchColumn();
echo date("Y-m-d H:i:s",strtotime($date)).' User registered on site.&#013;&#010;';
         }
         echo date('Y-m-d H:i:s',strtotime($item->creation_date)).' '.$item->description.'&#013;&#010;';
         
         $counter++;
   } ?>
</textarea>
<?php }else{ ?>
<ul class="form-notices">
    <li>
      Please enter email to view.    </li>
  </ul>
<?php } ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
     