<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

if(false !== strpos($url,'logoutfromall')) {
    if(!empty($_REQUEST['state'])){
        $gluu_custom_logout                = Mage::getStoreConfig('gluu/oxd/gluu_custom_logout');
        if(!empty($gluu_custom_logout)){
            header("Location: $gluu_custom_logout");
            exit;
        }else{
            header("Location: " . Mage::getBaseUrl().'admin');
            exit;
        }
    }
}

class GluuOxd_Gluufolder_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    private $dataHelper = "GluuOxd_Gluufolder";
    private $oxdRegisterSiteHelper = "GluuOxd_Gluufolder/registerSite";
    private $oxdUpdateSiteRegistrationHelper = "GluuOxd_Gluufolder/updateSiteRegistration";
    /**
     * @return string
     */
    public function getOxdRegisterSiteHelper()
    {
        return Mage::helper($this->oxdRegisterSiteHelper);
    }
    public function getBaseUrl()
    {
        // output: /myproject/index.php
        $currentPath = $_SERVER['PHP_SELF'];

        // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
        $pathInfo = pathinfo($currentPath);

        // output: localhost
        $hostName = $_SERVER['HTTP_HOST'];

        // output: http://
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        // return: http://localhost/myproject/
        return $protocol.$hostName.$pathInfo['dirname']."/";
    }
    /**
     * getting icone image link
     */
    public function getIconImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image.'.png';
    }
    /**
     * Administrator logout action
     */
    public function logoutAction()
    {
        if(isset($_SESSION['admin_session_in_op'])){
            if(time()<(int)$_SESSION['admin_session_in_op']) {
                $gluu_oxd_id                = Mage::getStoreConfig('gluu/oxd/gluu_oxd_id_admin');
                $gluu_config                = json_decode(Mage::getStoreConfig('gluu/oxd/gluu_config'),true);
                $gluu_provider              = Mage::getStoreConfig('gluu/oxd/gluu_provider');
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                $obj = json_decode($json);

                if (!empty($obj->end_session_endpoint) or $gluu_provider == 'https://accounts.google.com') {
                    if (!empty($_SESSION['admin_user_oxd_id_token'])) {
                        if ($gluu_oxd_id && $_SESSION['admin_user_oxd_id_token'] && $_SESSION['admin_session_in_op']) {
                            $logout = Mage::helper("GluuOxd_Gluufolder/logout");
                            $logout->setRequestOxdId($gluu_oxd_id);
                            $logout->setRequestIdToken($_SESSION['admin_user_oxd_id_token']);
                            $logout->setRequestPostLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                            $logout->setRequestSessionState($_SESSION['admin_session_state']);
                            $logout->setRequestState($_SESSION['admin_state']);
                            $logout->request();
                            unset($_SESSION['admin_user_oxd_access_token']);
                            unset($_SESSION['admin_user_oxd_id_token']);
                            unset($_SESSION['admin_session_state']);
                            unset($_SESSION['admin_state']);
                            unset($_SESSION['admin_session_in_op']);
                            header("Location: " . $logout->getResponseObject()->data->uri);
                            exit;
                        }
                    }
                } else {
                    unset($_SESSION['admin_user_oxd_access_token']);
                    unset($_SESSION['admin_user_oxd_id_token']);
                    unset($_SESSION['admin_session_state']);
                    unset($_SESSION['admin_state']);
                    unset($_SESSION['admin_session_in_op']);
                }
            }
        }
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        $adminSession->addSuccess(Mage::helper('adminhtml')->__('You have logged out.'));

        $gluu_custom_logout                = $this->select_query('gluu/oxd/gluu_custom_logout');
        if(!empty($gluu_custom_logout)){
            header("Location: $gluu_custom_logout");
            exit;
        }else{
            header("Location: " . Mage::getBaseUrl().'admin');
            exit;
        }
        
    }
    /**
     * @return string
     */
    public function getOxdUpdateSiteRegistrationHelper()
    {
        return Mage::helper($this->oxdUpdateSiteRegistrationHelper);
    }
    /**
     * @return gluuOxd admin index page
     */
    public function indexAction(){

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('core/template'));
        $this->renderLayout();
    }
    /**
     * @return gluuOxd admin index page
     */
    public function ajaxconfigAction(){
        if( isset( $_REQUEST['form_key_value'] ) and strpos( $_REQUEST['form_key_value'], 'openid_config_page' ) !== false ) {
            $params = $_REQUEST;
            if(!empty($params['scope']) && isset($params['scope'])){
                $gluu_config =   json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                $gluu_config['config_scopes'] = $params['scope'];
                $gluu_config = json_encode($gluu_config);
                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
                return true;
            }
        }
    }
    /**
     * @return gluuOxd admin index page
     */
    public function logoutfromallAction(){
        session_start();
        unset($_SESSION['admin_user_oxd_access_token']);
        unset($_SESSION['admin_user_oxd_id_token']);
        unset($_SESSION['admin_session_state']);
        unset($_SESSION['admin_state']);
        unset($_SESSION['admin_session_in_op']);
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        $adminSession->addSuccess(Mage::helper('adminhtml')->__('You have logged out.'));

        header("Location: " . Mage::getBaseUrl().'admin/index/logout');
    }
    /**
     * @return gluuOxd admin index page
     */
    public function ajaxopenidAction(){
        if( isset( $_POST['form_key_scope_delete'] ) and strpos( $_POST['form_key_scope_delete'], 'form_key_scope_delete' ) !== false ) {
            $get_scopes =   json_decode($this->select_query('gluu/oxd/gluu_scopes'),true);
            $up_cust_sc =  array();
            foreach($get_scopes as $custom_scop){
                if($custom_scop !=$_POST['delete_scope']){
                    array_push($up_cust_sc,$custom_scop);
                }
            }
            $get_scopes = json_encode($up_cust_sc);
            $get_scopes = $this->update_query('gluu/oxd/gluu_scopes', $get_scopes);


            $gluu_config =   json_decode($this->select_query('gluu/oxd/gluu_config'),true);
            $up_cust_scope =  array();
            foreach($gluu_config['config_scopes'] as $custom_scop){
                if($custom_scop !=$_POST['delete_scope']){
                    array_push($up_cust_scope,$custom_scop);
                }
            }
            $gluu_config['config_scopes'] = $up_cust_scope;
            $gluu_config = json_encode($gluu_config);
            $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
            return true;
        }
        else if (isset($_POST['form_key_scope']) and strpos( $_POST['form_key_scope'], 'oxd_openid_config_new_scope' ) !== false) {
            if (!empty($_POST['new_value_scope']) && isset($_POST['new_value_scope'])) {

                $get_scopes =   json_decode($this->select_query('gluu/oxd/gluu_scopes'),true);
                if($_POST['new_value_scope'] && !in_array($_POST['new_value_scope'],$get_scopes)){
                    array_push($get_scopes, $_POST['new_value_scope']);
                }
                $get_scopes = json_encode($get_scopes);
                $this->update_query('gluu/oxd/gluu_scopes', $get_scopes);
                return true;
            }

        }

    }
    /**
     * @return admin generalEdit page
     */
    public function generalAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('core/template'));
        $this->renderLayout();
    }
    /**
     * @return admin generalEdit page
     */
    public function openidconfigpageAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('core/template'));
        $this->renderLayout();
    }
    /**
     * @return getting session
     */
    private function getSession(){
        return  Mage::getSingleton('admin/session');
    }
    /**
     * redirecting function
     */
    private function redirect($url){
        $redirect = Mage::helper("adminhtml")->getUrl($url);
        Mage::app()->getResponse()->setRedirect($redirect);
    }
    /**
     * saving configs in database
     */
    private function saveConfig($url,$value,$id){
        $data = array($url=>$value);
        $model = Mage::getModel('admin/user')->load($id)->addData($data);
        try {
            $model->setId($id)->save();
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'gluuoxd_openid_error.log', true);
        }
    }
    public function getDataHelper(){
        return Mage::helper($this->dataHelper);
    }
    /**
     * saving and registration data geting oxd_id
     */
    public function generalFunctionAction(){
        $datahelper = $this->getDataHelper();

        if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key_value'], 'general_register_page' ) !== false ) {

            if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
                $datahelper->displayMessage('OpenID Connect requires https. This plugin will not work if your website uses http only.',"ERROR");
                $this->redirect("*/*/index");
                return;
            }
            if($_POST['gluu_user_role']){
                $this->update_query('gluu/oxd/gluu_user_role', trim($_POST['gluu_user_role']));
            }
            if($_POST['gluu_users_can_register']==1){
                $this->update_query('gluu/oxd/gluu_users_can_register', $_POST['gluu_users_can_register']);
                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                }
            }
            if($_POST['gluu_users_can_register']==2){
                $this->update_query('gluu/oxd/gluu_users_can_register', 2);
                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                    $datahelper->displayMessage('Please enter a role to use for automatic registration or choose one of the other enrollment options.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
            }
            if($_POST['gluu_users_can_register']==3){
                $this->update_query('gluu/oxd/gluu_users_can_register', 3);

                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                }
            }
            if (empty($_POST['gluu_oxd_port'])) {
                $datahelper->displayMessage('All the fields are required. Please enter valid entries.',"ERROR");
                $this->redirect("*/*/index");
                return;
            }
            else if (intval($_POST['gluu_oxd_port']) > 65535 && intval($_POST['gluu_oxd_port']) < 0) {
                $datahelper->displayMessage('Enter your oxd host port (Min. number 1, Max. number 65535)',"ERROR");
                $this->redirect("*/*/index");
                return;
            }
            else if  (!empty($_POST['gluu_provider'])) {
                if (filter_var($_POST['gluu_provider'], FILTER_VALIDATE_URL) === false) {
                    $datahelper->displayMessage('Please enter valid OpenID Provider URI.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
            }
            if  (!empty($_POST['gluu_custom_logout'])) {
                if (filter_var($_POST['gluu_custom_logout'], FILTER_VALIDATE_URL) === false) {
                    $datahelper->displayMessage('Please enter valid Custom URI.',"ERROR");
                }else{
                    $this->update_query('gluu/oxd/gluu_custom_logout', trim($_POST['gluu_custom_logout']));
                }
            }
            else{
                $this->update_query('gluu/oxd/gluu_custom_logout', '');
            }
            if (isset($_POST['gluu_provider']) and !empty($_POST['gluu_provider'])) {
                $gluu_provider = trim($_POST['gluu_provider']);
                $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                $obj = json_decode($json);
                if(!empty($obj->userinfo_endpoint)){

                    if(empty($obj->registration_endpoint)){
                        $datahelper->displayMessage('Please enter your client_id and client_secret.',"SUCCESS");
                        $gluu_config = json_encode(array(
                            "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                            "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                            "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                            "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                            "config_scopes" => ["openid","profile","email"],
                            "gluu_client_id" => "",
                            "gluu_client_secret" => "",
                            "config_acr" => []
                        ));
                        if($_POST['gluu_users_can_register']==2){
                            $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                            array_push($config['config_scopes'],'permission');
                            $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                        }
                        $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
                        if(isset($_POST['gluu_client_id']) and !empty($_POST['gluu_client_id']) and
                            isset($_POST['gluu_client_secret']) and !empty($_POST['gluu_client_secret'])){
                            $gluu_config = json_encode(array(
                                "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                                "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                                "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                                "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                                "config_scopes" => ["openid","profile","email"],
                                "gluu_client_id" => $_POST['gluu_client_id'],
                                "gluu_client_secret" => $_POST['gluu_client_secret'],
                                "config_acr" => []
                            ));
                            $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
                            if($_POST['gluu_users_can_register']==2){
                                $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                                array_push($config['config_scopes'],'permission');
                                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                            }
                            $register_site = $this->getOxdRegisterSiteHelper();
                            $register_site->setRequestOpHost($gluu_provider);
                            $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                            $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                            $register_site->setRequestContacts([$gluu_config['admin_email']]);
                            $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                            $get_scopes = json_encode($obj->scopes_supported);
                            if(!empty($obj->acr_values_supported)){
                                $get_acr = json_encode($obj->acr_values_supported);
                                $get_acr = $this->update_query('gluu/oxd/gluu_acr', $get_acr);
                                $register_site->setRequestAcrValues($gluu_config['config_acr']);
                            }
                            else{
                                $register_site->setRequestAcrValues($gluu_config['config_acr']);
                            }
                            if(!empty($obj->scopes_supported)){
                                $get_scopes = json_encode($obj->scopes_supported);
                                $get_scopes = $this->update_query('gluu/oxd/gluu_scopes', $get_scopes);
                                $register_site->setRequestScope($obj->scopes_supported);
                            }
                            else{
                                $register_site->setRequestScope($gluu_config['config_scopes']);
                            }
                            $register_site->setRequestClientId($_POST['gluu_client_id']);
                            $register_site->setRequestClientSecret($_POST['gluu_client_secret']);
                            $status = $register_site->request();
                            if ($status['message'] == 'invalid_op_host') {
                                $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                                $this->redirect("*/*/index");
                                return;
                            }
                            if (!$status['status']) {
                                $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                                $this->redirect("*/*/index");
                                return;
                            }
                            if ($status['message'] == 'internal_error') {
                                $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                                $this->redirect("*/*/index");
                                return;
                            }
                            $gluu_oxd_id = $register_site->getResponseOxdId();
                            if ($gluu_oxd_id) {
                                $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                                $gluu_provider = $register_site->getResponseOpHost();
                                $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                                /*  for admin login*/
                                    $register_site_admin = $this->getOxdRegisterSiteHelper();
                                    $register_site_admin->setRequestOpHost($gluu_provider);
                                    $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                                    $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                    $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                                    $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                    $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                                    $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                                    $register_site_admin->setRequestClientId(trim($_POST['gluu_client_id']));
                                    $register_site_admin->setRequestClientSecret(trim($_POST['gluu_client_secret']));
                                    $register_site_admin->request();
                                    $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());
                                /*admin part end*/
                                $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                                $this->redirect("*/*/index");
                                return;
                            } else {
                                $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                                $this->redirect("*/*/index");
                                return;
                            }
                        }
                        else{
                            $_SESSION['openid_error'] = 'Error505.';
                            $this->redirect("*/*/index");
                            return;
                        }
                    }
                    else{

                        $gluu_config = json_encode(array(
                            "gluu_oxd_port" =>trim($_POST['gluu_oxd_port']),
                            "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                            "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                            "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                            "config_scopes" => ["openid","profile","email"],
                            "gluu_client_id" => "",
                            "gluu_client_secret" => "",
                            "config_acr" => []
                        ));
                        $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
                        if(trim($_POST['gluu_users_can_register'])==2){
                            $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                            array_push($config['config_scopes'],'permission');
                            $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                        }
                        $register_site = $this->getOxdRegisterSiteHelper();
                        $register_site->setRequestOpHost($gluu_provider);
                        $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                        $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                        $register_site->setRequestContacts([$gluu_config['admin_email']]);
                        $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                        $get_scopes = json_encode($obj->scopes_supported);
                        if(!empty($obj->acr_values_supported)){
                            $get_acr = json_encode($obj->acr_values_supported);
                            $get_acr = json_decode($this->update_query('gluu/oxd/gluu_acr', $get_acr));
                            $register_site->setRequestAcrValues($gluu_config['config_acr']);
                        }
                        else{
                            $register_site->setRequestAcrValues($gluu_config['config_acr']);
                        }
                        if(!empty($obj->scopes_supported)){
                            $get_scopes = json_encode($obj->scopes_supported);
                            $get_scopes = json_decode($this->update_query('gluu/oxd/gluu_scopes', $get_scopes));
                            $register_site->setRequestScope($obj->scopes_supported);
                        }
                        else{
                            $register_site->setRequestScope($gluu_config['config_scopes']);
                        }
                        $status = $register_site->request();
                        //var_dump($status);exit;
                        if ($status['message'] == 'invalid_op_host') {
                            $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        if (!$status['status']) {
                            $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        if ($status['message'] == 'internal_error') {
                            $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        $gluu_oxd_id = $register_site->getResponseOxdId();
                        if ($gluu_oxd_id) {
                            $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                            $gluu_provider = $register_site->getResponseOpHost();
                            $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                            /*  for admin login*/
                                $register_site_admin = $this->getOxdRegisterSiteHelper();
                                $register_site_admin->setRequestOpHost($gluu_provider);
                                $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                                $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                                $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                                $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                                $register_site_admin->request();
                                $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());
                            /*admin part end*/
                            $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                            $this->redirect("*/*/index");
                            return;
                        }
                        else {
                            $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                    }
                }
                else{
                    $datahelper->displayMessage('Please enter correct URI of the OpenID Provider.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }

            }
            else{
                $gluu_config = json_encode(array(
                    "gluu_oxd_port" =>trim($_POST['gluu_oxd_port']),
                    "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                    "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                    "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                    "config_scopes" => ["openid","profile","email"],
                    "gluu_client_id" => "",
                    "gluu_client_secret" => "",
                    "config_acr" => []
                ));
                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
                if(trim($_POST['gluu_users_can_register'])==2){
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }
                $register_site = $this->getOxdRegisterSiteHelper();
                $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                $register_site->setRequestContacts([$gluu_config['admin_email']]);
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
                $register_site->setRequestScope($gluu_config['config_scopes']);
                $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                $status = $register_site->request();

                if ($status['message'] == 'invalid_op_host') {
                    $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                if (!$status['status']) {
                    $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                if ($status['message'] == 'internal_error') {
                    $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                $gluu_oxd_id = $register_site->getResponseOxdId();
                if ($gluu_oxd_id) {
                    $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                    $gluu_provider = $register_site->getResponseOpHost();
                    $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    );
                    $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                    $obj = json_decode($json);
                    $register_site = $this->getOxdRegisterSiteHelper();
                    $register_site->setRequestOpHost($gluu_provider);
                    $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                    $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                    $register_site->setRequestContacts([$gluu_config['admin_email']]);
                    $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);

                    $get_scopes = json_encode($obj->scopes_supported);
                    if(!empty($obj->acr_values_supported)){
                        $get_acr = json_encode($obj->acr_values_supported);
                        $get_acr = $this->update_query('gluu/oxd/gluu_acr', $get_acr);
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    else{
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    if(!empty($obj->scopes_supported)){
                        $get_scopes = json_encode($obj->scopes_supported);
                        $get_scopes = $this->update_query('gluu/oxd/gluu_scopes', $get_scopes);
                        $register_site->setRequestScope($obj->scopes_supported);
                    }
                    else{
                        $register_site->setRequestScope($gluu_config['config_scopes']);
                    }
                    $status = $register_site->request();
                    if ($status['message'] == 'invalid_op_host') {
                        $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    if (!$status['status']) {
                        $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    if ($status['message'] == 'internal_error') {
                        $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    $gluu_oxd_id = $register_site->getResponseOxdId();
                    if ($gluu_oxd_id) {
                        $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                        /*  for admin login*/
                        $register_site_admin = $this->getOxdRegisterSiteHelper();
                        $register_site_admin->setRequestOpHost($gluu_provider);
                        $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                        $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                        $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                        $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                        $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                        $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                        $register_site_admin->request();
                        $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());
                        /*admin part end*/
                        $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                        $this->redirect("*/*/index");
                        return;
                    }
                    else {
                        $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                }
                else {
                    $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
            }
        }
        else if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key_value'], 'general_oxd_id_reset' )  !== false and !empty($_REQUEST['resetButton'])) {

            unset($_SESSION['openid_error']);

            $datahelper->displayMessage('Configurations deleted Successfully.',"SUCCESS");
            $this->resetConfigAction();
        }
        else if (isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key_value'], 'general_oxd_edit' ) !== false) {
            if(trim($_POST['gluu_user_role'])){
                $this->update_query('gluu/oxd/gluu_user_role', trim($_POST['gluu_user_role']));
            }
            if(trim($_POST['gluu_users_can_register'])==1){
                $this->update_query('gluu/oxd/gluu_users_can_register', trim($_POST['gluu_users_can_register']));
                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                }
            }
            if($_POST['gluu_users_can_register']==2){
                $this->update_query('gluu/oxd/gluu_users_can_register', 2);
                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                    $datahelper->displayMessage('Please enter a role to use for automatic registration or choose one of the other enrollment options.',"ERROR");
                    $this->redirect("*/*/general");
                    return;
                }
            }
            if($_POST['gluu_users_can_register']==3){
                $this->update_query('gluu/oxd/gluu_users_can_register', 3);

                if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }else{
                    $this->update_query('gluu/oxd/gluu_new_role', json_encode(null));
                }
            }
            $get_scopes = json_encode(array("openid", "profile","email"));
            $get_scopes = $this->update_query('gluu/oxd/get_scopes', $get_scopes);

            $gluu_acr = json_encode(array("none"));
            $gluu_acr = $this->update_query('gluu/oxd/gluu_acr', $gluu_acr);

            if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
                $datahelper->displayMessage('OpenID Connect requires https. This plugin will not work if your website uses http only.',"ERROR");
                $this->redirect("*/*/indexEdit");
                return;
            }
            if (empty(trim($_POST['gluu_oxd_port']))) {
                $datahelper->displayMessage('All the fields are required. Please enter valid entries.',"ERROR");
                $this->redirect("*/*/indexEdit");
                return;
            }
            else if (intval($_POST['gluu_oxd_port']) > 65535 && intval($_POST['oxd_port']) < 0) {
                $datahelper->displayMessage('Enter your oxd host port (Min. number 0, Max. number 65535).',"ERROR");
                $this->redirect("*/*/indexEdit");
                return;
            }
            if  (!empty(trim($_POST['gluu_custom_logout']))) {
                if (filter_var(trim($_POST['gluu_custom_logout']), FILTER_VALIDATE_URL) === false) {
                    $datahelper->displayMessage('Please enter valid Custom URI.',"ERROR");
                }else{
                    $this->update_query('gluu/oxd/gluu_custom_logout', trim($_POST['gluu_custom_logout']));
                }
            }else{
                $this->update_query('gluu/oxd/gluu_custom_logout', '');
            }
            $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', '');
            $gluu_config = array(
                "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                "config_scopes" => ["openid","profile","email"],
                "gluu_client_id" => "",
                "gluu_client_secret" => "",
                "config_acr" => []
            );

            $gluu_config = $this->update_query('gluu/oxd/gluu_config', json_encode($gluu_config));
            if($_POST['gluu_users_can_register']==2){
                $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                array_push($config['config_scopes'],'permission');
                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
            }
            $gluu_provider         = $this->select_query('gluu/oxd/gluu_provider');
            if (!empty($gluu_provider)) {
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                $obj = json_decode($json);
                if(!empty($obj->userinfo_endpoint)){
                    if(empty($obj->registration_endpoint)){
                        if(isset($_POST['gluu_client_id']) and !empty($_POST['gluu_client_id']) and
                            isset($_POST['gluu_client_secret']) and !empty($_POST['gluu_client_secret']) and !$obj->registration_endpoint){
                            $gluu_config = array(
                                "gluu_oxd_port" => trim($_POST['gluu_oxd_port']),
                                "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                                "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                                "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                                "gluu_client_id" => trim($_POST['gluu_client_id']),
                                "gluu_client_secret" => trim($_POST['gluu_client_secret']),
                                "config_scopes" => ["openid", "profile","email"],
                                "config_acr" => []
                            );
                            $gluu_config1 = $this->update_query('gluu/oxd/gluu_config', json_encode($gluu_config));
                            if($_POST['gluu_users_can_register']==2){
                                $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                                array_push($config['config_scopes'],'permission');
                                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                            }
                            $register_site = $this->getOxdRegisterSiteHelper();
                            $register_site->setRequestOpHost($gluu_provider);
                            $register_site->setRequestAcrValues($gluu_config['config_acr']);
                            $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                            $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                            $register_site->setRequestContacts([$GLOBALS['current_user']->email1]);
                            $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                            if(!empty($obj->acr_values_supported)){
                                $get_acr = json_encode($obj->acr_values_supported);
                                $gluu_config = $this->update_query('gluu/oxd/gluu_acr', $gluu_acr);
                            }
                            if(!empty($obj->scopes_supported)){
                                $get_scopes = json_encode($obj->scopes_supported);
                                $gluu_config = $this->update_query('gluu/oxd/get_scopes', $get_scopes);
                                $register_site->setRequestScope($obj->scopes_supported);
                            }else{
                                $register_site->setRequestScope($gluu_config['config_scopes']);
                            }
                            $register_site->setRequestClientId(trim($_POST['gluu_client_id']));
                            $register_site->setRequestClientSecret(trim($_POST['gluu_client_secret']));
                            $status = $register_site->request();
                            if ($status['message'] == 'invalid_op_host') {
                                $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                                $this->redirect("*/*/indexEdit");
                                return;
                            }
                            if (!$status['status']) {
                                $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                                $this->redirect("*/*/indexEdit");
                                return;
                            }
                            if ($status['message'] == 'internal_error') {
                                $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                                $this->redirect("*/*/indexEdit");
                                return;
                            }
                            $gluu_oxd_id = $register_site->getResponseOxdId();
                            if ($gluu_oxd_id) {
                                $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                                $gluu_provider = $register_site->getResponseOpHost();
                                $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                                /*  for admin login*/
                                    /*$register_site_admin = $this->getOxdRegisterSiteHelper();
                                    $register_site_admin->setRequestOpHost($gluu_provider);
                                    $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                                    $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                    $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                                    $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                    $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                                    $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                                    $register_site_admin->setRequestClientId(trim($_POST['gluu_client_id']));
                                    $register_site_admin->setRequestClientSecret(trim($_POST['gluu_client_secret']));
                                    $register_site_admin->request();
                                    $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());*/
                                /*admin part end*/
                                $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                                $this->redirect("*/*/index");
                                return;
                            } else {
                                $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                                $this->redirect("*/*/index");
                                return;
                            }
                        }
                        else{
                            $_SESSION['openid_error_edit'] = 'Error506';
                            $this->redirect("*/*/indexEdit");
                            return;
                        }
                    }
                    else{
                        $gluu_config = array(
                            "gluu_oxd_port" =>trim($_POST['gluu_oxd_port']),
                            "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                            "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                            "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                            "config_scopes" => ["openid","profile","email"],
                            "gluu_client_id" => "",
                            "gluu_client_secret" => "",
                            "config_acr" => []
                        );
                        $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($gluu_config)),true);
                        if($_POST['gluu_users_can_register']==2){
                            $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                            array_push($config['config_scopes'],'permission');
                            $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                        }
                        $register_site = $this->getOxdRegisterSiteHelper();
                        $register_site->setRequestOpHost($gluu_provider);
                        $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                        $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                        $register_site->setRequestContacts([$gluu_config['admin_email']]);
                        $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                        $get_scopes = json_encode($obj->scopes_supported);
                        if(!empty($obj->acr_values_supported)){
                            $get_acr = json_encode($obj->acr_values_supported);
                            $get_acr = json_decode($this->update_query('gluu/oxd/gluu_acr', $get_acr));
                            $register_site->setRequestAcrValues($gluu_config['config_acr']);
                        }
                        else{
                            $register_site->setRequestAcrValues($gluu_config['config_acr']);
                        }
                        if(!empty($obj->scopes_supported)){
                            $get_scopes = json_encode($obj->scopes_supported);
                            $get_scopes = json_decode($this->update_query('gluu/oxd/gluu_scopes', $get_scopes));
                            $register_site->setRequestScope($obj->scopes_supported);
                        }
                        else{
                            $register_site->setRequestScope($gluu_config['config_scopes']);
                        }
                        $status = $register_site->request();
                        //var_dump($status);exit;
                        if ($status['message'] == 'invalid_op_host') {
                            $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        if (!$status['status']) {
                            $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        if ($status['message'] == 'internal_error') {
                            $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                        $gluu_oxd_id = $register_site->getResponseOxdId();
                        if ($gluu_oxd_id) {
                            $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                            $gluu_provider = $register_site->getResponseOpHost();
                            $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                            /*  for admin login*/
                                $register_site_admin = $this->getOxdRegisterSiteHelper();
                                $register_site_admin->setRequestOpHost($gluu_provider);
                                $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                                $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                                $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                                $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                                $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                                $register_site_admin->request();
                                $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());
                            /*admin part end*/
                            $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                            $this->redirect("*/*/index");
                            return;
                        }
                        else {
                            $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                            $this->redirect("*/*/index");
                            return;
                        }
                    }
                }
                else{
                    $datahelper->displayMessage('Please enter correct URI of the OpenID Provider.',"ERROR");
                    $this->redirect("*/*/indexEdit");
                    return;
                }
            }
            else{
                $gluu_config = array(
                    "gluu_oxd_port" =>trim($_POST['gluu_oxd_port']),
                    "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                    "authorization_redirect_uri" => Mage::getBaseUrl().'customer/account/login?option=getOxdSocialLogin',
                    "post_logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                    "config_scopes" => ["openid","profile","email"],
                    "gluu_client_id" => "",
                    "gluu_client_secret" => "",
                    "config_acr" => []
                );
                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($gluu_config)),true);
                if($_POST['gluu_users_can_register']==2){
                    $config = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', json_encode($config)),true);
                }
                $register_site = $this->getOxdRegisterSiteHelper();
                $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                $register_site->setRequestContacts([$gluu_config['admin_email']]);
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
                $register_site->setRequestScope($gluu_config['config_scopes']);
                $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                $status = $register_site->request();

                if ($status['message'] == 'invalid_op_host') {
                    $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                if (!$status['status']) {
                    $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                if ($status['message'] == 'internal_error') {
                    $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
                $gluu_oxd_id = $register_site->getResponseOxdId();
                if ($gluu_oxd_id) {
                    $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                    $gluu_provider = $register_site->getResponseOpHost();
                    $gluu_provider1 = $this->update_query('gluu/oxd/gluu_provider', $gluu_provider);
                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    );
                    $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                    $obj = json_decode($json);
                    $register_site = $this->getOxdRegisterSiteHelper();
                    $register_site->setRequestOpHost($gluu_provider);
                    $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                    $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                    $register_site->setRequestContacts([$gluu_config['admin_email']]);
                    $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);

                    $get_scopes = json_encode($obj->scopes_supported);
                    if(!empty($obj->acr_values_supported)){
                        $get_acr = json_encode($obj->acr_values_supported);
                        $get_acr = $this->update_query('gluu/oxd/gluu_acr', $get_acr);
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    else{
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    if(!empty($obj->scopes_supported)){
                        $get_scopes = json_encode($obj->scopes_supported);
                        $get_scopes = $this->update_query('gluu/oxd/gluu_scopes', $get_scopes);
                        $register_site->setRequestScope($obj->scopes_supported);
                    }
                    else{
                        $register_site->setRequestScope($gluu_config['config_scopes']);
                    }
                    $status = $register_site->request();
                    if ($status['message'] == 'invalid_op_host') {
                        $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    if (!$status['status']) {
                        $datahelper->displayMessage('Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    if ($status['message'] == 'internal_error') {
                        $datahelper->displayMessage('ERROR: '.$status['error_message'],"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                    $gluu_oxd_id = $register_site->getResponseOxdId();
                    if ($gluu_oxd_id) {
                        $gluu_oxd_id = $this->update_query('gluu/oxd/gluu_oxd_id', $gluu_oxd_id);
                        /*  for admin login*/
                            $register_site_admin = $this->getOxdRegisterSiteHelper();
                            $register_site_admin->setRequestOpHost($gluu_provider);
                            $register_site_admin->setRequestAuthorizationRedirectUri(Mage::getBaseUrl().'admin?option=getOxdAdminLogin');
                            $register_site_admin->setRequestLogoutRedirectUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                            $register_site_admin->setRequestContacts([$gluu_config['admin_email']]);
                            $register_site_admin->setRequestClientLogoutUri(Mage::getBaseUrl().'gluufolder/adminhtml_index/logoutfromall');
                            $register_site_admin->setRequestAcrValues($gluu_config['config_acr']);
                            $register_site_admin->setRequestScope($gluu_config['config_scopes']);
                            $register_site_admin->request();
                            $gluu_oxd_id_admin = $this->update_query('gluu/oxd/gluu_oxd_id_admin', $register_site_admin->getResponseOxdId());
                        /*admin part end*/
                        $datahelper->displayMessage('Your settings are saved successfully.',"SUCCESS");
                        $this->redirect("*/*/index");
                        return;
                    }
                    else {
                        $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                        $this->redirect("*/*/index");
                        return;
                    }
                }
                else {
                    $datahelper->displayMessage('ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json.',"ERROR");
                    $this->redirect("*/*/index");
                    return;
                }
            }
        }
        else if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key_value'], 'openid_config_page' ) !== false ) {
            $params = $_REQUEST;
            $message_success = '';

            if($_POST['send_user_type']){
                $gluu_auth_type = trim($_POST['send_user_type']);
                $gluu_auth_type = $this->update_query('gluu/oxd/gluu_auth_type', $gluu_auth_type);
            }else{
                $gluu_auth_type = $this->update_query('gluu/oxd/gluu_auth_type', 'default');
            }
            $gluu_send_user_check = trim($_POST['send_user_check']);
            $gluu_send_user_check = $this->update_query('gluu/oxd/gluu_send_user_check', $gluu_send_user_check);

            $gluu_send_admin_check = trim($_POST['send_admin_check']);
            $gluu_send_admin_check = $this->update_query('gluu/oxd/gluu_send_admin_check', $gluu_send_admin_check);
            if(!empty($params['scope']) && isset($params['scope'])){
                $gluu_config =   json_decode($this->select_query("gluu/oxd/gluu_config"),true);
                $gluu_config['config_scopes'] = $params['scope'];
                $gluu_config = json_encode($gluu_config);
                $gluu_config = json_decode($this->update_query('gluu/oxd/gluu_config', $gluu_config),true);
            }
            if(!empty($params['scope_name']) && isset($params['scope_name'])){
                $get_scopes =   json_decode($this->select_query('gluu/oxd/gluu_scopes'),true);
                foreach($params['scope_name'] as $scope){
                    if($scope && !in_array($scope,$get_scopes)){
                        array_push($get_scopes, $scope);
                    }
                }
                $get_scopes = json_encode($get_scopes);
                $get_scopes = json_decode($this->update_query('gluu/oxd/gluu_scopes', $get_scopes),true);
            }
            $gluu_acr              = json_decode($this->select_query('gluu/oxd/gluu_acr'),true);

            if(!empty($params['acr_name']) && isset($params['acr_name'])){
                $get_acr =   json_decode($this->select_query('gluu/oxd/gluu_acr'),true);
                foreach($params['acr_name'] as $scope){
                    if($scope && !in_array($scope,$get_acr)){
                        array_push($get_acr, $scope);
                    }
                }
                $get_acr = json_encode($get_acr);
                $get_acr = json_decode($this->update_query('gluu/oxd/gluu_acr', $get_acr),true);
            }
            $gluu_config =   json_decode($this->select_query('gluu/oxd/gluu_config'),true);
            $gluu_oxd_id =   $this->select_query('gluu/oxd/gluu_oxd_id');
            $update_site_registration = $this->getOxdUpdateSiteRegistrationHelper();
            $update_site_registration->setRequestOxdId($gluu_oxd_id);
            $update_site_registration->setRequestAcrValues($gluu_config['acr_values']);
            $update_site_registration->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
            $update_site_registration->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
            $update_site_registration->setRequestContacts([$gluu_config['admin_email']]);
            $update_site_registration->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
            $update_site_registration->setRequestScope($gluu_config['config_scopes']);
            $status = $update_site_registration->request();
            $new_oxd_id = $update_site_registration->getResponseOxdId();
            if($new_oxd_id){
                $get_scopes = $this->update_query('gluu/oxd/gluu_oxd_id', $new_oxd_id);
            }

            $datahelper->displayMessage('Your OpenID connect configuration has been saved.',"SUCCESS");
            $this->redirect("*/*/openidconfigpage");
            return;
        }
    }
    public function resetConfigAction(){
        $setup = new Mage_Core_Model_Config();
        unset($_SESSION['openid_error']);
        unset($_SESSION['admin_user_oxd_access_token']);
        unset($_SESSION['admin_user_oxd_id_token']);
        unset($_SESSION['admin_session_state']);
        unset($_SESSION['admin_state']);
        unset($_SESSION['admin_session_in_op']);
        $setup->deleteConfig('gluu/oxd/gluu_oxd_id');
        $setup->deleteConfig('gluu/oxd/gluu_scopes');
        $setup->deleteConfig('gluu/oxd/gluu_config');
        $setup->deleteConfig('gluu/oxd/gluu_acr');
        $setup->deleteConfig('gluu/oxd/gluu_auth_type');
        $setup->deleteConfig('gluu/oxd/gluu_send_user_check');
        $setup->deleteConfig('gluu/oxd/gluu_send_admin_check');
        $setup->deleteConfig('gluu/oxd/gluu_provider');
        $setup->deleteConfig('gluu/oxd/gluu_user_role');
        $setup->deleteConfig('gluu/oxd/gluu_custom_logout');
        $setup->deleteConfig('gluu/oxd/gluu_new_role');
        $setup->deleteConfig('gluu/oxd/gluu_users_can_register');
        $this->redirect("*/*/index");
    }
    /**
     * checking $_POST data
     */
    public function empty_or_null( $value ) {
        if( ! isset( $value ) || empty( $value ) ) {
            return true;
        }
        return false;
    }
    /*
     * getting ID from session
    */
    private function getId(){
        return $this->getSession()->getUser()->getUserId();
    }
    function select_query($action){
        $result = Mage::getStoreConfig($action);
        return $result;
    }
    function insert_query($action, $value){
        $storeConfig = new Mage_Core_Model_Config();
        $storeConfig ->saveConfig($action,$value, 'default', 0);
        $result = Mage::getStoreConfig($action);
        return $result;
    }
    function update_query($action, $value){
        $storeConfig = new Mage_Core_Model_Config();
        $storeConfig ->saveConfig($action,$value, 'default', 0);
        $result = Mage::getStoreConfig($action);
        return $result;
    }
}