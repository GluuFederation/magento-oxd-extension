<?php
 /*
   * Created by PhpStorm.
   * User: Vlad Karapetyan
  */
class GluuOxd_Openid_Block_GluuOxOpenidConfig extends Mage_Core_Block_Template{

    private $getAuthorizationUrl = "GluuOxd_Openid/getAuthorizationUrl";
    private $getTokensByCode = "GluuOxd_Openid/getTokensByCode";
    private $getUserInfo = "GluuOxd_Openid/getUserInfo";
    private $logout = "GluuOxd_Openid/logout";

    /**
     * @return string
     */
    public function getGetAuthorizationUrl()
    {
        return Mage::helper($this->getAuthorizationUrl);
    }

    /**
     * @return string
     */
    public function getGetTokensByCode()
    {
        return Mage::helper($this->getTokensByCode);
    }

    /**
     * @return string
     */
    public function getGetUserInfo()
    {
        return Mage::helper($this->getUserInfo);
    }

    /**
     * @return string
     */
    public function getLogout()
    {
        return Mage::helper($this->logout);
    }

    /**
     * saving config in database
     */
    public function saveConfig($url,$value){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $id = $admin->getUserId();
        $data = array($url=>$value);
        $model = Mage::getModel('admin/user')->load($id)->addData($data);
        try {
            $model->setId($id)->save();
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'gluuoxd_error.log', true);
        }
    }

    /**
     * get config from database
     */
    public function getConfig($config,$id=""){
        $user = Mage::helper('GluuOxd_Openid');
        if( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
            $admin = Mage::getSingleton('admin/session')->getUser();
            $id = $admin->getUserId();
            return $user->getConfig($config,$id);
        }
        else{
            $id = Mage::getSingleton('customer/session')->getCustomer()->getId();
            return $user->getConfig($config,$id);
        }
    }

    /**
     * getting image link
    */
    public function getImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/'.$image.'.png';
    }

    /**
     * getting icone image link
     */
    public function getIconImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image.'.png';
    }

    /**
     * getting host url
     */
    public function getHostURl(){
        return  Mage::helper('GluuOxd_Openid')->getHostURl();
    }

    /**
     * getting openId config from database
     */
    public function getOpendIdConfig($data){
        return Mage::helper('GluuOxd_Openid')->getConfig($data);
    }

    /**
     * redirect url
     */
    private function redirect($url){
        $redirect = Mage::helper("adminhtml")->getUrl($url);
        Mage::app()->getResponse()->setRedirect($redirect);
    }

    /**
     * checking enabled
     * return @string
     */
    public function isEnabled(){
        $customer = Mage::helper('GluuOxd_Openid');
        $admin = Mage::getSingleton('admin/session')->getUser();
        $id = $admin->getUserId();
        if($customer->getConfig('isEnabled',$id)==1){
            return 'checked';
        }
        else{
            return '';
        }
    }

    /**
     * getting admin url
     * return @string
     */
    public function getadminurl($value){
        return Mage::helper("adminhtml")->getUrl($value);
    }

    /**
     * getting oxd url
     * return @string
     */
    public function gluuOxd_geturl($value){
        return Mage::getUrl($value,array('_secure'=>true));
    }

    /**
     * getting current url
     * return @string
     */
    public function getcurrentUrl(){
        return Mage::getBaseUrl();
    }

    /**
     * getting current user
     * return @string
     */
    public function getCurrentUser(){
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getEmail();
        }
        return;
    }

    /**
     * showing email
     * return @string
     */
    public function showEmail(){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $customer = Mage::helper('GluuOxd_Openid');
        $id = $admin->getUserId();
        return $customer->showEmail($id);
    }

    /**
     * checking is customer enabled
     * return @string
     */
    public function isCustomerEnabled(){
        $customer = Mage::helper('GluuOxd_Openid');
        if($customer->getConfig('isCustomerEnabled','')==1){
            return 'checked';
        }
        else{
            return '';
        }
    }

    /**
     * getting admmin config
     * return @data
     */
    public function getConfigForAdmin($config){
        $user = Mage::helper('GluuOxd_Openid');
        $model = Mage::getModel("admin/user");
        $userid = $model->getCollection()->getFirstItem()->getId();
        return $user->getConfig($config,$userid);
    }

    /**
     * getting session
     * return @data
     */
    public function getSession(){
        if( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
            $session = Mage::getSingleton('customer/session');
        }else{
            $session = Mage::getSingleton('admin/session');
        }
        return $session;
    }

    /**
     * getting OpenId admin url
     * return @data
     */
    public function getOpenIdAdminUrl(){
        return Mage::helper("adminhtml")->getUrl("*/index/index");
    }

    /**
     * getting login page validateing
     * return @string
     */
    public function gluuoxd_openid_login_validate(){

        if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getOxdSocialLogin' ) !== false ) {

            $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
            $oxd_id = Mage::getStoreConfig ( 'gluu/oxd/oxd_id' );
            $get_tokens_by_code = $this->getGetTokensByCode();
            $get_tokens_by_code->setRequestOxdId($oxd_id);
            $get_tokens_by_code->setRequestCode($_REQUEST['code']);
            $get_tokens_by_code->setRequestState($_REQUEST['state']);
            $get_tokens_by_code->setRequestScopes($config_option["scope"]);
            $get_tokens_by_code->request();

            $array_data = $get_tokens_by_code->getResponseObject();
            $_SESSION['user_oxd_id_token']  = $get_tokens_by_code->getResponseIdToken();
            $_SESSION['user_oxd_access_token']  = $get_tokens_by_code->getResponseAccessToken();

            $get_user_info = $this->getGetUserInfo();
            $get_user_info->setRequestOxdId($oxd_id);
            $get_user_info->setRequestAccessToken($_SESSION['user_oxd_access_token']);
            $get_user_info->request();
            $user_email = '';
            //var_dump($get_user_info->getResponseObject());exit;
            if($get_user_info->getResponseEmail() ) {
                $user_email = $get_user_info->getResponseEmail();
            }else{
                if($array_data->id_token_claims->email){
                    $user_email = $array_data->id_token_claims->email;
                }
            }

            $user_name = '';
            $user_picture = $get_user_info->getResponsePicture();
            $first_name = '';
            $last_name = '';
            $user_full_name = '';
            if($get_user_info->getResponseGivenName() && $get_user_info->getResponseFamilyName()){
                $user_full_name = $get_user_info->getResponseGivenName().' '.$get_user_info->getResponseFamilyName();
                $first_name = $get_user_info->getResponseGivenName();
                $last_name = $get_user_info->getResponseFamilyName();
            }elseif($array_data->id_token_claims->family_name && $array_data->id_token_claims->given_name){
                $first_name = $array_data->id_token_claims->given_name;
                $last_name = $array_data->id_token_claims->family_name;
                if($array_data->id_token_claims->name){
                    $user_full_name = $array_data->id_token_claims->name;
                }else{
                    $user_full_name = $array_data->id_token_claims->family_name.' '.$array_data->id_token_claims->given_name;
                }

            }

            if($get_user_info->getResponsePreferredUsername()){
                $user_name = $get_user_info->getResponsePreferredUsername();
            }
            elseif(strcmp($user_name, $user_full_name)){
                $email_split = explode("@", $user_email);
                $user_name = $email_split[0];
            } else {
                $user_name = $user_name;
            }

            if( $user_email ) {

                $user_name= Mage::getModel('admin/user')->getCollection()->addFieldToFilter('email',$user_email)->getFirstItem()->getUsername();

                $user = Mage::getModel('admin/user')->loadByUsername($user_name);
                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }

                $session = Mage::getSingleton('admin/session');
                $session->setIsFirstVisit(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

                Mage::dispatchEvent('admin_session_user_login_success',array('user'=>$user));

                if ($session->isLoggedIn()) {
                    $redirectUrl = Mage::getSingleton('adminhtml/url')->getUrl(Mage::getModel('admin/user')->getStartupPageUrl(), array('_current' => false));
                    header('Location: ' . $redirectUrl);
                    exit;
                }else{
                    $datahelper = Mage::helper("GluuOxd_Openid"); //GluuOxd_Openid_Helper_Data
                    $datahelper->displayMessage('User does not exist in our system. Please check your Email ID.',"ERROR");
                    $this->redirect("*/index/index");
                }

            }
        }
        if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'userGluuLogin' ) !== false ) {

            $oxd_id = Mage::getStoreConfig ( 'gluu/oxd/oxd_id' );
            $get_authorization_url = $this->getGetAuthorizationUrl();

            $get_authorization_url->setRequestOxdId($oxd_id);
            $get_authorization_url->setRequestAcrValues([$_REQUEST['app_name']]);
            $get_authorization_url->request();
            if($get_authorization_url->getResponseAuthorizationUrl()){
                header("Location: ".$get_authorization_url->getResponseAuthorizationUrl());
                exit;
            }else{
                echo '<p style="color: red">Sorry, but oxd server is not swatched on!</p>';
            }

        }
    }
}