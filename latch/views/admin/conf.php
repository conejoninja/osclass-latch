<?php if ( ! defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

    if(Params::getParam('plugin_action')=='done') {
        osc_set_preference('appId', Params::getParam("appId"), 'latch', 'STRING');
        osc_set_preference('appSecret', Params::getParam("appSecret"), 'latch', 'STRING');

        // HACK : This will make possible use of the flash messages ;)
        ob_get_clean();
        osc_add_flash_ok_message(__('Congratulations, the plugin is now configured', 'latch'), 'admin');
        osc_redirect_to(osc_route_admin_url('latch-admin-conf'));
    }
?>

<div id="general-setting">
    <div id="general-settings">
        <h2 class="render-title"><?php _e('Latch settings', 'ckr'); ?></h2>
        <ul id="error_list"></ul>
        <form name="payment_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="route" value="latch-admin-conf" />
            <input type="hidden" name="plugin_action" value="done" />
            <fieldset>
                <div class="form-horizontal">
                    <div class="form-row">
                        <div class="form-label"><?php _e('Latch', 'latch'); ?></div>
                        <div class="form-controls"><a href="https://latch.elevenpaths.com/www/"><?php _e('Access your developer area at latch.elevenpaths.com to get your appId and appSecret', 'latch'); ?></a></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('AppID', 'latch'); ?></div>
                        <div class="form-controls"><input type="text" class="xlarge" name="appId" value="<?php echo osc_get_preference('appId', 'latch'); ?>" /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('AppSecret', 'latch'); ?></div>
                        <div class="form-controls"><input type="text" class="xlarge" name="appSecret" value="<?php echo osc_get_preference('appSecret', 'latch'); ?>" /></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-actions">
                        <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>