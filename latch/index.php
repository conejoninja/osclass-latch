<?php
/*
Plugin Name: Latch Authentication
Plugin URI: latch
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
        osc_set_preference('version', '100', 'latch', 'INTEGER');
        osc_set_preference('appId', '', 'latch');
        osc_set_preference('appSecret', '', 'latch');
        osc_set_preference('usersEnabled', 1, 'latch', 'BOOLEAN');
    }

    function latch_uninstall() {
        ModelLatch::newInstance()->dropTable();
        osc_delete_preference('version', 'latch');
        osc_delete_preference('appId', 'latch');
        osc_delete_preference('appSecret', 'latch');
        osc_delete_preference('usersEnabled', 'latch');
    }

    function latch_admin_menu() {
        osc_add_admin_submenu_divider('plugins', 'Latch plugin', 'latch_divider', 'administrator');
        osc_add_admin_submenu_page('plugins', __('Configure Latch plugin', 'latch'), osc_route_admin_url('latch-admin-conf'), 'latch_conf', 'administrator');
    }
    osc_add_hook('admin_menu_init', 'latch_admin_menu');

    function latch_form() {
        include_once dirname(__FILE__) . '/views/form.php';
    }

    function latch_admin_form() {
        include_once dirname(__FILE__) . '/views/admin/form.php';
    }
    osc_add_hook('admin_profile_form', 'latch_admin_form');

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

    function latch_admin_pair($adminId) {
        if (Params::getParam('latch_code') != '') {
            $api = new Latch(osc_get_preference('appId', 'latch'), osc_get_preference('appSecret', 'latch'));
            $pairResponse = $api->pair(Params::getParam('latch_code'));
            $error = $pairResponse->getError();
            $data = $pairResponse->getData();
            if ($error == null && $data != null && isset($data->accountId)) {
                ModelLatch::newInstance()->pair($adminId, $data->accountId, 1);
                osc_add_flash_ok_message(__('Latch paired correctly'), 'admin');
            } else {
                osc_add_flash_error_message($error->getMessage(), 'admin');
                osc_redirect_to(osc_admin_base_url(true) . '?page=admins&action=edit&id=' . osc_logged_admin_id());
            }
        }
    }
    if(osc_version()<=351) {
        function latch_admin_pair_init() {
            if(Params::getParam('page')=='admins' && Params::getParam('action')=='edit_post') {
                if(osc_is_admin_user_logged_in()) {
                    latch_admin_pair(osc_logged_admin_id());
                }
            }
        }
        osc_add_hook('init_admin', 'latch_admin_pair_init');
    } else {
        osc_add_hook('admin_edit_completed', 'latch_admin_pair');
    }

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
        $account = ModelLatch::newInstance()->findByUser($userId);
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

    function latch_login_admin($admin) {
        $account = ModelLatch::newInstance()->findByUser($admin['pk_i_id'], 1);
        if(isset($account['s_account_id'])) {
            $api = new Latch(osc_get_preference('appId', 'latch'), osc_get_preference('appSecret', 'latch'));
            $statusResponse = $api->status($account['s_account_id']);
            $error = $statusResponse->getError();
            $data = $statusResponse->getData();
            if ($error != null) {
                latch_logout_admin();
                osc_add_flash_error_message($error->getMessage(), 'admin');
                osc_redirect_to(osc_admin_base_url(true) . '?page=login');
            } else if($data == null || !isset($data->operations) || !isset($data->operations->{osc_get_preference('appId', 'latch')}) || !isset($data->operations->{osc_get_preference('appId', 'latch')}->status)) {
                latch_logout_admin();
                osc_add_flash_error_message(__('Unexpected error', 'latch'), 'admin');
                osc_redirect_to(osc_admin_base_url(true) . '?page=login');
            } else if($data->operations->{osc_get_preference('appId', 'latch')}->status == 'off') {
                latch_logout_admin();
                osc_add_flash_error_message(__('Your Latch account is disabled for this service, please enable it', 'latch'), 'admin');
                osc_redirect_to(osc_admin_base_url(true) . '?page=login');
            }
        }
    }
    osc_add_hook('login_admin', 'latch_login_admin');

    function latch_logout_admin() {
        //destroying session
        Session::newInstance()->_drop('adminId');
        Session::newInstance()->_drop('adminUserName');
        Session::newInstance()->_drop('adminName');
        Session::newInstance()->_drop('adminEmail');
        Session::newInstance()->_drop('adminLocale');

        Cookie::newInstance()->pop('oc_adminId');
        Cookie::newInstance()->pop('oc_adminSecret');
        Cookie::newInstance()->pop('oc_adminLocale');
        Cookie::newInstance()->set();
    }

    function latch_users_enabled() {
        return (osc_get_preference('usersEnabled', 'latch')=='1');
    }

    osc_add_route('latch-admin-unpair', 'latch/admin/unpair', 'latch/admin/unpair', osc_plugin_folder(__FILE__).'views/admin/unpair.php');
    osc_add_route('latch-admin-conf', 'latch/admin/conf', 'latch/admin/conf', osc_plugin_folder(__FILE__).'views/admin/conf.php');

    if(latch_users_enabled()) {
        osc_add_route('latch-unpair', 'latch/unpair', 'latch/unpair', osc_plugin_folder(__FILE__).'views/unpair.php');
        osc_add_hook('user_register_form', 'latch_form');
        osc_add_hook('user_profile_form', 'latch_form');
        osc_add_hook('user_register_completed', 'latch_pair');
        osc_add_hook('user_edit_completed', 'latch_pair');
        osc_add_hook('before_login', 'latch_login');
    }

    osc_register_plugin(osc_plugin_path(__FILE__), 'latch_install');
    osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", 'latch_uninstall');


?>
