<?php
/*
Plugin Name: Latch Authentication
Plugin URI:
Description: Use latch to secure your users
Version: 1.0.0
Author: _CONEJO
Author URI:
Short Name: latch
*/

    require_once dirname(__FILE__) . '/ModelLatch.php';
    require_once dirname(__FILE__) . '/lib/Latch.php';
    require_once dirname(__FILE__) . '/lib/LatchResponse.php';
    require_once dirname(__FILE__) . '/lib/Error.php';

    function latch_install() {
        ModelLatch::newInstance()->createTable();
        osc_set_preference('version', '100', 'latch');
        osc_set_preference('appId', '', 'latch');
        osc_set_preference('appSecret', '', 'latch');
    }

    function latch_uninstall() {
        ModelLatch::newInstance()->dropTable();
        osc_delete_preference('version', 'latch');
        osc_delete_preference('appId', 'latch');
        osc_delete_preference('appSecret', 'latch');
    }

    function latch_admin_menu() {
        osc_add_admin_submenu_divider('plugins', 'Latch plugin', 'latch_divider', 'administrator');
        osc_add_admin_submenu_page('plugins', __('Configure Latch plugin', 'latch'), osc_route_admin_url('latch-admin-conf'), 'latch_conf', 'administrator');
    }
    osc_add_hook('admin_menu_init', 'latch_admin_menu');

    function latch_form() {
        include_once dirname(__FILE__) . '/views/form.php';
    }
    osc_add_hook('user_register_form', 'latch_form');
    osc_add_hook('user_profile_form', 'latch_form');

    function latch_pair($userId) {
        if (Params::getParam('latch_code') != '') {
            $api = new Latch(osc_get_preference('appId', 'latch'), osc_get_preference('appSecret', 'latch'));
            $pairResponse = $api->pair(Params::getParam('latch_code'));
            $error = $pairResponse->getError();
            $data = $pairResponse->getData();
            if ($error == null && $data != null && isset($data->accountId)) {
                ModelLatch::newInstance()->pair($userId, $data->accountId);
            } else {
                osc_add_flash_error_message($error->getMessage());
                osc_redirect_to(osc_user_profile_url());
            }
        }
    }
    osc_add_hook('user_register_completed', 'latch_pair');
    osc_add_hook('user_edit_completed', 'latch_pair');

    function latch_login($userId = null) {
        if($userId==null) { //COMPATIBLE WITH OLD VERSIONS
            if(osc_validate_email(Params::getParam('email'))) {
                $user = User::newInstance()->findByEmail(Params::getParam('email'));
            }
            if ( empty($user) ) {
                $user = User::newInstance()->findByUsername(Params::getParam('email'));
            }
            $userId = $user['pk_i_id'];
        }
        $account = ModelLatch::newInstance()->findByPrimaryKey($userId);
        if(isset($account['s_account_id'])) {
            $api = new Latch(osc_get_preference('appId', 'latch'), osc_get_preference('appSecret', 'latch'));
            $statusResponse = $api->status($account['s_account_id']);
            $error = $statusResponse->getError();
            $data = $statusResponse->getData();
            if ($error != null) {
                osc_add_flash_error_message($error->getMessage());
                osc_redirect_to(osc_user_login_url());
            } else if($data == null || !isset($data->operations) || !isset($data->operations->{osc_get_preference('appId', 'latch')}) || !isset($data->operations->{osc_get_preference('appId', 'latch')}->status)) {
                osc_add_flash_error_message(__('Unexpected error', 'latch'));
                osc_redirect_to(osc_user_login_url());
            } else if($data->operations->{osc_get_preference('appId', 'latch')}->status == 'off') {
                osc_add_flash_error_message(__('Your Latch account is disabled for this service, please enable it', 'latch'));
                osc_redirect_to(osc_user_login_url());
            }
        }
    }
    osc_add_hook('before_login', 'latch_login');


    osc_add_route('latch-unpair', 'latch/unpair', 'latch/unpair', osc_plugin_folder(__FILE__).'views/unpair.php');
    osc_add_route('latch-admin-conf', 'latch/admin/conf', 'latch/admin/conf', osc_plugin_folder(__FILE__).'views/admin/conf.php');

    osc_register_plugin(osc_plugin_path(__FILE__), 'latch_install');


?>
