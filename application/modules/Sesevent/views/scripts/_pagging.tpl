<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _pagging.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php if ($this->pageCount && $this->pageCount > 1):$numPages = $this->pageCount;?>
 <?php 
 if(isset($this->identityWidget)){
 	$idFunction = 'paggingNumber'.$this->identityWidget;
 }else
 	$idFunction = 'paggingNumber';  
 ?>
<div id="ses_pagging_<?php echo $this->identityWidget?>">
  <div class="sesbasic_loading_cont_overlay" style="display:none" id="sesbasic_loading_cont_overlay_<?php echo $this->identityWidget?>"></div>
  <div class="sesbasic_paging clear sesbasic_clearfix sesbasic_bxs" id="ses_pagging">
    <ul>
      <li style="display:none;"><span><?php echo $this->current; ?> of <?php echo $this->pageCount; ?></span></li>
      <?php if (isset($this->previous)): ?>
	<li><a href="javascript:;" onclick="<?php echo $idFunction ?>('<?php echo $this->previous; ?>')"  title="<?php echo $this->previous; ?>"> <?php echo $this->translate('Previous') ?></a></li>
      <?php else: ?>
	<li class="sesbasic_paging_disabled"><span><?php echo $this->translate('Previous') ?></span></li>
      <?php endif; ?>
      <?php 
      $pagingEcho = '';
      $selectPageNum = $this->current;
      for($runPage=1;$runPage<=$numPages;$runPage++){
	  if($selectPageNum == $runPage){
	    $pagingEcho .= "<li class=\"sesbasic_paging_current_page\"><a href=\"javascript:;\">".$runPage."</a></li>";
	  }else{
	    $pagingEcho .= "<li class=\"pages\"><a href=\"javascript:;\" onclick=\"".$idFunction."(".$runPage.")\">".$runPage."</a></li>";
	  }
	  if($runPage < ($selectPageNum - 3) && ($runPage+3) < $numPages){
	    $pagingEcho .= "<li class=\"sesbasic_paging_disabled\"><span>[&sdot;&sdot;&sdot;]</span></li>";
	    $runPage = $selectPageNum - 3;
	  }
	  if($runPage > ($selectPageNum + 2) && ($runPage+2) < $numPages){
	    $pagingEcho .= "<li class=\"sesbasic_paging_disabled\"><span>[&sdot;&sdot;&sdot;]</span></li>";
	    $runPage = $numPages-1;
	  }
      }
	echo $pagingEcho;
      ?>
      <?php if (isset($this->next)): ?>
	<li><a href="javascript:;" onclick="<?php echo $idFunction ?>('<?php echo $this->next; ?>')"  title="<?php echo $this->next; ?>"> <?php echo $this->translate('Next') ?> </a> </li>
      <?php else: ?>
	<li class="sesbasic_paging_disabled"><span><?php echo $this->translate('Next') ?></span></li>
      <?php endif; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>