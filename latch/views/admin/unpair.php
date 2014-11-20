<?php
ob_get_clean();

$account = ModelLatch::newInstance()->findByUser(osc_logged_admin_id(), 1);
if(isset($account['s_account_id'])) {
    $api = new Latch(osc_get_preference('appId', 'latch'), osc_get_preference('appSecret', 'latch'));
    $pairResponse = $api->unpair($account['s_account_id']);
    $error = $pairResponse->getError();
    $data = $pairResponse->getData();
    if ($error == null) {
        ModelLatch::newInstance()->unpair(osc_logged_admin_id(), 1);
        osc_add_flash_ok_message(__('Successfully unpaired from Latch', 'latch'), 'admin');
    } else {
        osc_add_flash_error_message($error->getMessage(), 'admin');
    }
}
osc_redirect_to(osc_admin_base_url(true) . '?page=admins&action=edit&id=' . osc_logged_admin_id());
