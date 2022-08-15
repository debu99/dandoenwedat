
        <div class="sesevent_cover_info">

            <?php if(in_array('title',$this->show_criterias)){ ?>
           <?php if($this->actionA != 'buy'){ ?>
            <h2 class="sesevent_cover_title"><?php echo $this->subject->getTitle(); ?></h2>
          <?php }else{ ?>
          	<h2 class="sesevent_cover_title"><?php echo $this->htmlLink($this->subject->getHref(), $this->subject->getTitle(), array('class' => ''));  ?></h2>
          <?php } ?>
          <?php } ?>


           <?php if(in_array('startEndDate',$this->show_criterias)){ ?>
            <div class="sesevent_cover_date clear sesbasic_clearfix sesevent_cover_time">
              <i title="<?php echo $this->translate("Start & End Date"); ?>" class="far fa-calendar-alt"></i>
              <?php $dateinfoParams['starttime'] = true; ?>
              <?php $dateinfoParams['endtime']  =  true; ?>
              <?php $dateinfoParams['timezone']  =  true; ?>
              <?php echo $this->eventStartEndDates($this->subject,$dateinfoParams); ?>
            </div>
          <?php } ?>

    </div>

            <?php if(in_array('addtocalender',$this->show_criterias)){ ?>
              <div><?php echo $this->content()->renderWidget('sesevent.add-to-calendar',array('options'=>$this->show_calander)); ?></div>
            <?php } ?>

            <?php if(in_array('advShare',$this->show_criterias)){ ?>
                <div><?php echo $this->content()->renderWidget('sesevent.advance-share',array('options'=>$this->show_calander)); ?></div> 
            <?php } ?>