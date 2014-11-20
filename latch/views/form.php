<div class="control-group">
    <label class="control-label" for="password-2"><?php _e('Latch pair code', 'latch') ; ?></label>
    <div class="controls">
        <?php $account = ModelLatch::newInstance()->findByUser(osc_logged_user_id());
        if(isset($account['s_account_id'])) {?>
            <a href="<?php echo osc_route_url('latch-unpair'); ?>"><?php _e('Already paired. UNPAIR Latch?'); ?></a>
        <?php } else { ?>
            <input type="text" id="latch_code" name="latch_code" />
            <a href="https://latch.elevenpaths.com/www/faq.html" >What is Latch?</a>
        <?php }; ?>
    </div>
</div>