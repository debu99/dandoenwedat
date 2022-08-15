<span class="sesevent-join-leave">
<?php
    if (!$this->isLoggedIn) {
        echo '<a href="login"><button>Login to Join</button></a>';
    } else {
        if ($this->showLadiesOnly) {
            echo '<div type="button" class="ladies-only sesevent_event_status sesbasic_clearfix open">' . $this->translate('Ladies Only') . '</div>';
        } else if ($this->showMenOnly) {
            echo '<div type="button" class="men-only sesevent_event_status sesbasic_clearfix open">' . $this->translate('Men Only') . '</div>';
        } else if ($this->isLoggedIn && !$this->isFull && !$this->isAttending && !$this->alreadyGoing) {
            echo '<button class="sesevent-join">' . $this->translate('Join Event') . '</button>';
        } else if ($this->isFull && !$this->isOnWaitingList && !$this->isAttending) {
            echo '<a href="/events/member/waitinglist/event_id/' . $this->subject()->event_id . '" class="buttonlink smoothbox menu_sesevent_profile sesevent_profile_member" style="" target="">
                                <button>' . $this->translate('Join Waiting List') . '</button>
                        </a>';
        } else if ($this->isAttending) {
            echo '<a href="/events/member/leave/event_id/' . $this->subject()->event_id . '" class="buttonlink smoothbox menu_sesevent_profile sesevent_profile_member" style="" target="">
                            <button>' . $this->translate('Leave Event') . '</button>
                        </a>';
        } else if ($this->alreadyGoing) {
            echo '<a href="' . $this->alreadyGoing . '" class="buttonlink menu_sesevent_profile sesevent_profile_member" style="" target="">
                            <button>' . $this->translate('Already Going Somewhere') . '</button>
                        </a>';
        }

        if ($this->isOnWaitingList) {
            echo '<a href="/events/member/leave-waiting-list/event_id/' . $this->subject()->event_id . '" class="buttonlink smoothbox menu_sesevent_profile sesevent_profile_member" style="" target="">
                            <button>' . $this->translate('Leave Waiting List') . '</button>
                        </a>';
        }

    }
?>
    <div class="loading">
        <div id="TB_overlay" style="position: absolute; top: 0px; left: 0px; visibility: visible; opacity: 0.6; height: 1777px; width: 100vw;"></div>
        <div id="TB_load" style="display: block; visibility: visible;"><img src="externals/smoothbox/loading.gif"></div>
    </div>
<span>

<style>
    .sesevent_event_status.sesbasic_clearfix.open.men-only {
        background-color: #03598F;
        border: 1px solid #03598F;
        text-transform: uppercase;
    }
    .sesevent_event_status.sesbasic_clearfix.open.ladies-only {
        background-color: #FE4497;
        border: 1px solid #FE4497;
        text-transform: uppercase;
    }

    .sesevent-join-leave .loading {
        display: none;
    }
    .loading #TB_load {
        left: 50%;
        top:50%;
        transform: translate(-50%, -50%);
    }
    .sesevent-join-leave button,  .sesevent-join-leave .buttonlink {
        width: 100%;
        margin: 2px;
    }
    .sesevent-join {
        animation: shake 1.25s;
        animation-iteration-count: 1;
        animation-delay: 1s;
    }

    @keyframes shake {
        10%,90% {transform: translate3d(-1px, 0, 0);}
        20%, 80% {transform: translate3d(2px, 0, 0);}
        30%,50%, 70% { transform: translate3d(-4px, 0, 0);}
        40%,60% {transform: translate3d(4px, 0, 0);}
    }
</style>

<script type="text/javascript">
    en4.core.runonce.add(function(){
    sesJqueryObject(document).on('click','.sesevent-join',function(e){
        e.preventDefault();
        jQuery(".loading").toggle();
        new Request.JSON({
            url: '<?php echo $this->url(array('module' => 'events', 'controller' => 'member', 'action' => 'join-widget', 'event_id' => $this->subject()->event_id), 'default', true); ?>',
            method: 'post',
            data : {
            format: 'json',
            'event_id': <?php echo $this->subject()->event_id ?>,
            'rsvp': 2,
            },
            onComplete: function(responseJSON, responseText){
                debugger
                if (responseJSON.error) {
                    alert(responseJSON.error);
                }else {
                    location.reload();
                }
            }
        }).send();
    });
});
</script>