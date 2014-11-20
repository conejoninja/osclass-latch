<?php if(Params::getParam('action')=='edit' && (!Params::existParam('id') || (Params::existParam('id') && osc_logged_admin_id()==Params::getParam('id')))) { ?>
    <div class="form-row">
        <div class="form-label"><?php _e('Latch pair code', 'latch') ; ?></div>
        <?php $account = ModelLatch::newInstance()->findByUser(osc_logged_admin_id(), 1);
        if(isset($account['s_account_id'])) {?>
            <div class="form-controls"><a href="<?php echo osc_route_admin_url('latch-admin-unpair'); ?>"><?php _e('Already paired. UNPAIR Latch?'); ?></a></div>
        <?php } else { ?>
            <div class="form-controls">
                <input type="text" id="latch_code" name="latch_code" />
                <a href="https://latch.elevenpaths.com/www/faq.html"><?php _e('What is Latch?', 'latch'); ?></a>
            </div>
        <?php }; ?>
    </div>
<?php }; ?>