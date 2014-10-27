<label for="latch"><?php _e('Latch pair code', 'latch') ; ?></label>
<?php
$account = ModelLatch::newInstance()->findByPrimaryKey(osc_logged_user_id());
if(isset($account['s_account_id'])) {?>
    <a href="<?php echo osc_route_url('latch-unpair'); ?>"><?php _e('Already paired. UNPAIR Latch?'); ?></a>
    <br />
<?php } else { ?>
    <input type="text" id="latch_code" name="latch_code" />
    <a href="#">What is Latch?</a>
    <br />
<?php }; ?>