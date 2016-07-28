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
     * Administrator logout action
     */
    public function logout_validation()
    {

        if($_SESSION['state']){
            $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
            $oxd_id = Mage::getStoreConfig ( 'gluu/oxd/oxd_id' );
            $logout = $this->getLogout();
            $logout->setRequestOxdId($oxd_id);
            $logout->setRequestIdToken($_SESSION['user_oxd_id_token']);
            $logout->setRequestPostLogoutRedirectUri($config_option['logout_redirect_uri']);
            $logout->setRequestSessionState($_SESSION['session_state']);
            $logout->setRequestState($_SESSION['state']);
            $logout->request();
            header("Location: ".$logout->getResponseObject()->data->uri);
            exit;
            //echo "<a href='".$logout->getResponseObject()->data->uri."'>Logout from all sites.</a>";

        }

    }

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

    /*
     * getting admmin config
     * return @data
     */
    public function getConfigForAdmin($config){
        $user = Mage::helper('GluuOxd_Openid');
        $model = Mage::getModel("admin/user");
        $userid = $model->getCollection()->getFirstItem()->getId();
        return $user->getConfig($config,$userid);
    }

    /*
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
            $get_tokens_by_code_array = $get_tokens_by_code->getResponseObject()->data->id_token_claims;

            $_SESSION['user_oxd_id_token']  = $get_tokens_by_code->getResponseIdToken();
            $_SESSION['user_oxd_access_token']  = $get_tokens_by_code->getResponseAccessToken();
            $_SESSION['session_state'] = $_REQUEST['session_state'];
            $_SESSION['state'] = $_REQUEST['state'];

            $get_user_info = $this->getGetUserInfo();
            $get_user_info->setRequestOxdId($oxd_id);
            $get_user_info->setRequestAccessToken($_SESSION['user_oxd_access_token']);
            $get_user_info->request();
            $get_user_info_array = $get_user_info->getResponseObject()->data->claims;

            $reg_first_name = '';
            $reg_last_name = '';
            $reg_middle_name = '';
            $reg_email = '';
            $reg_country = '';
            $reg_city = '';
            $reg_region = '';
            $reg_gender = '';
            $reg_postal_code = '';
            $reg_fax = '';
            $reg_home_phone_number = '';
            $reg_phone_mobile_number = '';
            $reg_avatar = '';
            $reg_street_address = '';
            $reg_birthdate = '';
            if($get_user_info_array->given_name[0]){
                $reg_first_name = $get_user_info_array->given_name[0];
            }elseif($get_tokens_by_code_array->given_name[0]){
                $reg_first_name = $get_tokens_by_code_array->given_name[0];
            }
            if($get_user_info_array->family_name[0]){
                $reg_last_name = $get_user_info_array->family_name[0];
            }elseif($get_tokens_by_code_array->family_name[0]){
                $reg_last_name = $get_tokens_by_code_array->family_name[0];
            }
            if($get_user_info_array->middle_name[0]){
                $reg_middle_name = $get_user_info_array->middle_name[0];
            }elseif($get_tokens_by_code_array->middle_name[0]){
                $reg_middle_name = $get_tokens_by_code_array->middle_name[0];
            }
            if($get_user_info_array->email[0]){
                $reg_email = $get_user_info_array->email[0];
            }elseif($get_tokens_by_code_array->email[0]){
                $reg_email = $get_tokens_by_code_array->email[0];
            }
            if($get_user_info_array->country[0]){
                $reg_country = $get_user_info_array->country[0];
            }elseif($get_tokens_by_code_array->country[0]){
                $reg_country = $get_tokens_by_code_array->country[0];
            }
            if($get_user_info_array->gender[0]){
                if($get_user_info_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }

            }elseif($get_tokens_by_code_array->gender[0]){
                if($get_tokens_by_code_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }
            }
            if($get_user_info_array->locality[0]){
                $reg_city = $get_user_info_array->locality[0];
            }elseif($get_tokens_by_code_array->locality[0]){
                $reg_city = $get_tokens_by_code_array->locality[0];
            }
            if($get_user_info_array->postal_code[0]){
                $reg_postal_code = $get_user_info_array->postal_code[0];
            }elseif($get_tokens_by_code_array->postal_code[0]){
                $reg_postal_code = $get_tokens_by_code_array->postal_code[0];
            }
            if($get_user_info_array->phone_number[0]){
                $reg_home_phone_number = $get_user_info_array->phone_number[0];
            }elseif($get_tokens_by_code_array->phone_number[0]){
                $reg_home_phone_number = $get_tokens_by_code_array->phone_number[0];
            }
            if($get_user_info_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_user_info_array->phone_mobile_number[0];
            }elseif($get_tokens_by_code_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_tokens_by_code_array->phone_mobile_number[0];
            }
            if($get_user_info_array->picture[0]){
                $reg_avatar = $get_user_info_array->picture[0];
            }elseif($get_tokens_by_code_array->picture[0]){
                $reg_avatar = $get_tokens_by_code_array->picture[0];
            }
            if($get_user_info_array->street_address[0]){
                $reg_street_address = $get_user_info_array->street_address[0];
            }elseif($get_tokens_by_code_array->street_address[0]){
                $reg_street_address = $get_tokens_by_code_array->street_address[0];
            }
            if($get_user_info_array->birthdate[0]){
                $reg_birthdate = $get_user_info_array->birthdate[0];
            }elseif($get_tokens_by_code_array->birthdate[0]){
                $reg_birthdate = $get_tokens_by_code_array->birthdate[0];
            }
            if($get_user_info_array->region[0]){
                $reg_region = $get_user_info_array->region[0];
            }elseif($get_tokens_by_code_array->region[0]){
                $reg_region = $get_tokens_by_code_array->region[0];
            }
            if( $reg_email ) {
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($reg_email);
                if($customer->getId()>1){
                    $customer->setFirstname($reg_first_name);
                    $customer->setLastname ($reg_last_name);
                    $customer->setMiddleName($reg_middle_name);
                    $customer->setGender($reg_gender);
                    $customer->setDob($reg_birthdate);

                    $customer->save();
                    $dataShipping = array(
                        'firstname'  => $reg_first_name,
                        'lastname'   => $reg_last_name,
                        'street'     => array($reg_street_address),
                        'region'     => $reg_region,
                        'city'       => $reg_city,
                        'postcode'   => $reg_postal_code,
                        'country_id' => $reg_country,
                        'telephone'  => $reg_phone_mobile_number.' '. $reg_home_phone_number,
                    );
                    $customerAddress = Mage::getModel('customer/address');

                    if ($defaultShippingId = $customer->getDefaultShipping()){
                        $customerAddress->load($defaultShippingId);
                    } else {
                        $customerAddress->setCustomerId($customer->getId())->setIsDefaultShipping('1')->setSaveInAddressBook('1');

                        $customer->addAddress($customerAddress);
                    }
                    $customerAddress->addData($dataShipping)->save();
                    $session = Mage::getSingleton("customer/session");
                    $session->loginById($customer->getId());
                    $session->setCustomerAsLoggedIn($customer);
                    header("Refresh:0");
                }
                else{
                    $websiteId = Mage::app()->getWebsite()->getId();
                    $store = Mage::app()->getStore();
                    $password = md5(Mage::helper('core')->getRandomString($length = 7));
                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId($websiteId)
                            ->setStore($store)
                            ->setFirstname($reg_first_name)
                            ->setLastname($reg_last_name)
                            ->setMiddleName($reg_middle_name)
                            ->setDob($reg_birthdate)
                            ->setGender($reg_gender)
                            ->setEmail($reg_email)
                            ->setPassword($password);
                    try{
                        $customer->save();
                        $address = Mage::getModel("customer/address");
                        $address->setCustomerId($customer->getId())
                                ->setFirstname($customer->getFirstname())
                                ->setMiddleName($reg_middle_name)
                                ->setLastname($customer->getLastname())
                                ->setCountryId($reg_country)
                                ->setPostcode($reg_postal_code)
                                ->setFax($reg_postal_code)
                                ->setCity($reg_city)
                                ->setRegion($reg_region)
                                ->setTelephone($reg_phone_mobile_number.' '. $reg_home_phone_number)
                                ->setStreet($reg_street_address)
                                ->setIsDefaultBilling('1')
                                ->setIsDefaultShipping('1')
                                ->setSaveInAddressBook('1');
                        $address->save();
                        $session = Mage::getSingleton("customer/session");
                        $session->loginById($customer->getId());
                        $session->setCustomerAsLoggedIn($customer);
                        header("Refresh:0");
                    }
                    catch (Exception $e) {
                        Zend_Debug::dump($e->getMessage());
                    }
                }

            }else{
                echo '<p style="color: red">Sorry, but gluu server cannot find email address!</p>';
            }
        }
        if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'userGluuLogin' ) !== false ) {
            $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
            $oxd_id = Mage::getStoreConfig ( 'gluu/oxd/oxd_id' );
            $get_authorization_url = $this->getGetAuthorizationUrl();
            $get_authorization_url->setRequestOxdId($oxd_id);
            $get_authorization_url->setRequestAcrValues([$_REQUEST['app_name']]);
            $get_authorization_url->request();

            if($get_authorization_url->getResponseAuthorizationUrl()){
                header("Location: ".$get_authorization_url->getResponseAuthorizationUrl());
                exit;
            }else{
                echo '<p style="color: red">Sorry, but oxd server is not switched on!</p>';
            }
        }
    }
}