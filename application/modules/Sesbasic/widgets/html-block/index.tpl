<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesbasic_html_block <?php echo $this->class;?>" style="<?php if(!empty($this->height)):?>height:<?php echo $this->height;?>px;<?php endif;?><?php if(!empty($this->width)):?>width:<?php echo $this->width;?>px;<?php endif;?>">
  <?php echo $this->content;?>
</div>

