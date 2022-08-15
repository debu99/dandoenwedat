<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesbasic_tags sesbasic_clearfix sesbasic_bxs">
  <?php foreach($this->tagCloudData as $valueTags){ 
  if($valueTags['text'] == '' && empty($valueTags['text']))
  continue;
  ?>
  <a href="<?php echo $this->url(array('module' =>'sesevent','controller' => 'index', 'action' => 'browse'),'sesevent_general',true).'?tag_id='.$valueTags['tag_id'].'&tag_name='.$valueTags['text'];?>">    <b><?php echo $valueTags['text'] ?></b><sup><?php echo $valueTags['itemCount']; ?></sup></a>
  <?php } ?>
</div>