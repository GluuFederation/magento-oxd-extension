
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<div id="loading" style="display: none"></div>
<style>
    #loading {
        position: absolute; width: 100%; height: 100%; background: url('http://nation-news.ru/assets/images/ajax-loading.gif') no-repeat center center;
    }
</style>

<?php

class GluuOxd_Gluufolder_Block_GluuOxOpenidConfig extends Mage_Core_Block_Template{
    private $getAuthorizationUrl = "GluuOxd_Gluufolder/getAuthorizationUrl";
    private $getTokensByCode = "GluuOxd_Gluufolder/getTokensByCode";
    private $getUserInfo = "GluuOxd_Gluufolder/getUserInfo";
    private $logout = "GluuOxd_Gluufolder/logout";
    public function logout_validation()
    {
        if(isset($_SESSION['session_in_op'])){
            if(time()<(int)$_SESSION['session_in_op']) {
                $gluu_oxd_id                = $this->select_query('gluu/oxd/gluu_oxd_id');
                $gluu_config                = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
                $gluu_provider              = $this->select_query('gluu/oxd/gluu_provider');
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
                $obj = json_decode($json);

                if (!empty($obj->end_session_endpoint) or $gluu_provider == 'https://accounts.google.com') {
                    if (!empty($_SESSION['user_oxd_id_token'])) {
                        if ($gluu_oxd_id && $_SESSION['user_oxd_id_token'] && $_SESSION['session_in_op']) {
                            $logout = $this->getLogout();
                            $logout->setRequestOxdId($gluu_oxd_id);
                            $logout->setRequestIdToken($_SESSION['user_oxd_id_token']);
                            $logout->setRequestPostLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                            $logout->setRequestSessionState($_SESSION['session_state']);
                            $logout->setRequestState($_COOKIE['state']);
                            $logout->request();
                            unset($_SESSION['user_oxd_access_token']);
                            unset($_SESSION['user_oxd_id_token']);
                            unset($_SESSION['session_state']);
                            unset($_SESSION['state']);
                            unset($_SESSION['session_in_op']);
                            header("Location: " . $logout->getResponseObject()->data->uri);
                            exit;
                        }
                    }
                } else {
                    unset($_SESSION['user_oxd_access_token']);
                    unset($_SESSION['user_oxd_id_token']);
                    unset($_SESSION['session_state']);
                    unset($_SESSION['state']);
                    unset($_SESSION['session_in_op']);
                }
            }
        }
        $gluu_custom_logout                = $this->select_query('gluu/oxd/gluu_custom_logout');
        if(!empty($gluu_custom_logout)){
            header("Location: $gluu_custom_logout");
            exit;
        }else{
            header("Location: " . Mage::getBaseUrl().'customer/account/logout/');
            exit;
        }
    }
    function gluu_is_port_working(){
        $config_option                = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
        $connection = @fsockopen('127.0.0.1', $config_option['gluu_oxd_port']);

        if (is_resource($connection))
        {
            fclose($connection);
            return true;
        }

        else
        {
            return false;
        }
    }

    public function getGetAuthorizationUrl()
    {
        return Mage::helper($this->getAuthorizationUrl);
    }
    public function getGetTokensByCode()
    {
        return Mage::helper($this->getTokensByCode);
    }
    public function getGetUserInfo()
    {
        return Mage::helper($this->getUserInfo);
    }
    public function getLogout()
    {
        return Mage::helper($this->logout);
    }
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
    public function getConfig($config,$id=""){
        $user = Mage::helper('GluuOxd_Gluufolder');
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
    private function redirect($url){
        $redirect = Mage::helper("adminhtml")->getUrl($url);
        Mage::app()->getResponse()->setRedirect($redirect);
    }
    public function isEnabled(){
        $customer = Mage::helper('GluuOxd_Gluufolder');
        $admin = Mage::getSingleton('admin/session')->getUser();
        $id = $admin->getUserId();
        if($customer->getConfig('isEnabled',$id)==1){
            return 'checked';
        }
        else{
            return '';
        }
    }
    public function getadminurl($value){
        return Mage::helper("adminhtml")->getUrl($value);
    }
    public function gluuOxd_geturl($value){
        return Mage::getUrl($value,array('_secure'=>true));
    }
    public function getcurrentUrl(){
        return Mage::getBaseUrl();
    }
    public function getCurrentUser(){
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getEmail();
        }
        return;
    }
    public function showEmail(){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $customer = Mage::helper('GluuOxd_Gluufolder');
        $id = $admin->getUserId();
        return $customer->showEmail($id);
    }
    public function isCustomerEnabled(){
        $customer = Mage::helper('GluuOxd_Gluufolder');
        if($customer->getConfig('isCustomerEnabled','')==1){
            return 'checked';
        }
        else{
            return '';
        }
    }
    public function getConfigForAdmin($config){
        $user = Mage::helper('GluuOxd_Gluufolder');
        $model = Mage::getModel("admin/user");
        $userid = $model->getCollection()->getFirstItem()->getId();
        return $user->getConfig($config,$userid);
    }
    public function getSession(){
        if( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
            $session = Mage::getSingleton('customer/session');
        }else{
            $session = Mage::getSingleton('admin/session');
        }
        return $session;
    }
    public function getOpenIdAdminUrl(){
        return Mage::helper("adminhtml")->getUrl("*/index/index");
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
    public function gluuoxd_openid_login_validate(){
        if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getOxdSocialLogin' ) !== false ) {
            echo '<script type="application/javascript">
                     jQuery("body div").hide();
                     jQuery("#loading").show();
                  </script>';
            $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
            $oxd_id = Mage::getStoreConfig ('gluu/oxd/gluu_oxd_id');
            $get_tokens_by_code = $this->getGetTokensByCode();
            $get_tokens_by_code->setRequestOxdId($oxd_id);
            $get_tokens_by_code->setRequestCode($_REQUEST['code']);
            $get_tokens_by_code->setRequestState($_REQUEST['state']);
            $get_tokens_by_code->request();
            $get_tokens_by_code_array = array();

            if(!empty($get_tokens_by_code->getResponseObject()->data->id_token_claims))
            {
                $get_tokens_by_code_array = $get_tokens_by_code->getResponseObject()->data->id_token_claims;
            }else{
                echo "<script type='application/javascript'>
					alert('Missing claims : Please talk to your organizational system administrator or try again.');
					location.href='".$this->getBaseUrl()."';
				 </script>";
                exit;
            }
            $get_user_info = $this->getGetUserInfo();
            $get_user_info->setRequestOxdId($oxd_id);
            $get_user_info->setRequestAccessToken($get_tokens_by_code->getResponseAccessToken());
            $get_user_info->request();
            $get_user_info_array = $get_user_info->getResponseObject()->data->claims;
            $_SESSION['session_in_op'] = $get_tokens_by_code->getResponseIdTokenClaims()->exp[0];
            $_SESSION['user_oxd_id_token'] = $get_tokens_by_code->getResponseIdToken();
            $_SESSION['user_oxd_access_token'] = $get_tokens_by_code->getResponseAccessToken();
            $_SESSION['session_state'] = $_REQUEST['session_state'];
            $_SESSION['state'] = $_REQUEST['state'];
            $get_user_info_array = $get_user_info->getResponseObject()->data->claims;

            $reg_first_name = '';
            $reg_user_name = '';
            $reg_last_name = '';
            $reg_email = '';
            $reg_middle_name = '';
            $reg_country = '';
            $reg_city = '';
            $reg_region = '';
            $reg_gender = '';
            $reg_postal_code = '';
            $reg_fax = '';
            $reg_home_phone_number = '';
            $reg_phone_mobile_number = '';
            $reg_street_address = '';
            $reg_street_address_2 = '';
            $reg_birthdate = '';
            $reg_user_permission = '';
            if (!empty($get_user_info_array->email[0])) {
                $reg_email = $get_user_info_array->email[0];
            }
            elseif (!empty($get_tokens_by_code_array->email[0])) {
                $reg_email = $get_tokens_by_code_array->email[0];
            }
            else{
                echo "<script type='application/javascript'>
					alert('Missing claim : (email). Please talk to your organizational system administrator.');
					location.href='".$this->getBaseUrl()."';
				 </script>";
                exit;
            }
            if($get_user_info_array->given_name[0]){
                $reg_first_name = $get_user_info_array->given_name[0];
            }
            elseif($get_tokens_by_code_array->given_name[0]){
                $reg_first_name = $get_tokens_by_code_array->given_name[0];
            }
            if($get_user_info_array->family_name[0]){
                $reg_last_name = $get_user_info_array->family_name[0];
            }
            elseif($get_tokens_by_code_array->family_name[0]){
                $reg_last_name = $get_tokens_by_code_array->family_name[0];
            }
            if($get_user_info_array->middle_name[0]){
                $reg_middle_name = $get_user_info_array->middle_name[0];
            }
            elseif($get_tokens_by_code_array->middle_name[0]){
                $reg_middle_name = $get_tokens_by_code_array->middle_name[0];
            }
            if($get_user_info_array->email[0]){
                $reg_email = $get_user_info_array->email[0];
            }
            elseif($get_tokens_by_code_array->email[0]){
                $reg_email = $get_tokens_by_code_array->email[0];
            }
            if($get_user_info_array->country[0]){
                $reg_country = $get_user_info_array->country[0];
            }
            elseif($get_tokens_by_code_array->country[0]){
                $reg_country = $get_tokens_by_code_array->country[0];
            }
            if($get_user_info_array->gender[0]){
                if($get_user_info_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }
            }
            elseif($get_tokens_by_code_array->gender[0]){
                if($get_tokens_by_code_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }
            }
            if($get_user_info_array->locality[0]){
                $reg_city = $get_user_info_array->locality[0];
            }
            elseif($get_tokens_by_code_array->locality[0]){
                $reg_city = $get_tokens_by_code_array->locality[0];
            }
            if($get_user_info_array->postal_code[0]){
                $reg_postal_code = $get_user_info_array->postal_code[0];
            }
            elseif($get_tokens_by_code_array->postal_code[0]){
                $reg_postal_code = $get_tokens_by_code_array->postal_code[0];
            }
            if($get_user_info_array->phone_number[0]){
                $reg_home_phone_number = $get_user_info_array->phone_number[0];
            }
            elseif($get_tokens_by_code_array->phone_number[0]){
                $reg_home_phone_number = $get_tokens_by_code_array->phone_number[0];
            }
            if($get_user_info_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_user_info_array->phone_mobile_number[0];
            }
            elseif($get_tokens_by_code_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_tokens_by_code_array->phone_mobile_number[0];
            }
            if($get_user_info_array->picture[0]){
                $reg_avatar = $get_user_info_array->picture[0];
            }
            elseif($get_tokens_by_code_array->picture[0]){
                $reg_avatar = $get_tokens_by_code_array->picture[0];
            }
            if($get_user_info_array->street_address[0]){
                $reg_street_address = $get_user_info_array->street_address[0];
            }
            elseif($get_tokens_by_code_array->street_address[0]){
                $reg_street_address = $get_tokens_by_code_array->street_address[0];
            }
            if($get_user_info_array->birthdate[0]){
                $reg_birthdate = $get_user_info_array->birthdate[0];
            }
            elseif($get_tokens_by_code_array->birthdate[0]){
                $reg_birthdate = $get_tokens_by_code_array->birthdate[0];
            }
            if($get_user_info_array->region[0]){
                $reg_region = $get_user_info_array->region[0];
            }
            elseif($get_tokens_by_code_array->region[0]){
                $reg_region = $get_tokens_by_code_array->region[0];
            }
            $username = '';
            if (!empty($get_user_info_array->user_name[0])) {
                $username = $get_user_info_array->user_name[0];
            }
            else {
                $email_split = explode("@", $reg_email);
                $username = $email_split[0];
            }
            if(!empty($get_user_info_array->permission[0])){
                $world = str_replace("[","",$get_user_info_array->permission[0]);
                $reg_user_permission = str_replace("]","",$world);
            }
            elseif(!empty($get_tokens_by_code_array->permission[0])){
                $world = str_replace("[","",$get_user_info_array->permission[0]);
                $reg_user_permission = str_replace("]","",$world);
            }
	        $bool = false;
	        $gluu_new_roles              = json_decode(select_query('gluu/oxd/gluu_new_role'));
	        $gluu_users_can_register    = select_query('gluu/oxd/gluu_users_can_register');
	        $gluu_user_role    = select_query('gluu/oxd/gluu_user_role');
	        if($gluu_users_can_register == 2 and !empty($gluu_new_roles)){
                foreach ($gluu_new_roles as $gluu_new_role) {
                    if (strstr($reg_user_permission, $gluu_new_role)) {
                        $bool = true;
                    }
                }
                if(!$bool){
                    echo "<script>
                            alert('You are not authorized for an account on this application. If you think this is an error, please contact your OpenID Connect Provider (OP) admin.');
                            location.href='".$this->getBaseUrl()."';
                          </script>";
                    exit;
                }
	        }
	        
            if( $reg_email ) {
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($reg_email);
                if($customer->getId()>=1){
                    
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
	                if($gluu_users_can_register == 3){
                        echo "<script>
                                    alert('You are not authorized for an account on this application. If you think this is an error, please contact your OpenID Connect Provider (OP) admin.');
                                    location.href='".$this->getBaseUrl()."';
                              </script>";
                        exit;
	                }
                    $websiteId = Mage::app()->getWebsite()->getId();
                    $store = Mage::app()->getStore();
                    $password = md5(Mage::helper('core')->getRandomString($length = 7));
                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId($websiteId)
                        ->setGroupId($gluu_user_role)
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

            }
        }
    }
    public function gluuoxd_openid_login_validate_admin(){
        if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getOxdAdminLogin' ) !== false ) {

            $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
            $oxd_id = Mage::getStoreConfig ('gluu/oxd/gluu_oxd_id_admin');
            $get_tokens_by_code = $this->getGetTokensByCode();
            $get_tokens_by_code->setRequestOxdId($oxd_id);
            $get_tokens_by_code->setRequestCode($_REQUEST['code']);
            $get_tokens_by_code->setRequestState($_REQUEST['state']);
            $get_tokens_by_code->request();
            $get_tokens_by_code_array = array();
            if(!empty($get_tokens_by_code->getResponseObject()->data->id_token_claims))
            {
                $get_tokens_by_code_array = $get_tokens_by_code->getResponseObject()->data->id_token_claims;
            }else{
                echo "<script type='application/javascript'>
					alert('Missing claims : Please talk to your organizational system administrator or try again.');
					location.href='".$this->getBaseUrl()."/admin';
				 </script>";
                exit;
            }
            $get_user_info = $this->getGetUserInfo();
            $get_user_info->setRequestOxdId($oxd_id);
            $get_user_info->setRequestAccessToken($get_tokens_by_code->getResponseAccessToken());
            $get_user_info->request();
            $get_user_info_array = $get_user_info->getResponseObject()->data->claims;
            $_SESSION['admin_session_in_op'] = $get_tokens_by_code->getResponseIdTokenClaims()->exp[0];
            $_SESSION['admin_user_oxd_id_token'] = $get_tokens_by_code->getResponseIdToken();
            $_SESSION['admin_user_oxd_access_token'] = $get_tokens_by_code->getResponseAccessToken();
            $_SESSION['admin_session_state'] = $_REQUEST['session_state'];
            $_SESSION['admin_state'] = $_REQUEST['state'];

            $get_user_info_array = $get_user_info->getResponseObject()->data->claims;
            $reg_first_name = '';
            $reg_user_name = '';
            $reg_last_name = '';
            $reg_email = '';
            $reg_middle_name = '';
            $reg_country = '';
            $reg_city = '';
            $reg_region = '';
            $reg_gender = '';
            $reg_postal_code = '';
            $reg_fax = '';
            $reg_home_phone_number = '';
            $reg_phone_mobile_number = '';
            $reg_street_address = '';
            $reg_street_address_2 = '';
            $reg_birthdate = '';
            $reg_user_permission = '';
            if (!empty($get_user_info_array->email[0])) {
                $reg_email = $get_user_info_array->email[0];
            }
            elseif (!empty($get_tokens_by_code_array->email[0])) {
                $reg_email = $get_tokens_by_code_array->email[0];
            }
            else{
                echo "<script type='application/javascript'>
					alert('Missing claim : (email). Please talk to your organizational system administrator.');
					location.href='".$this->getBaseUrl()."';
				 </script>";
                exit;
            }
            if($get_user_info_array->given_name[0]){
                $reg_first_name = $get_user_info_array->given_name[0];
            }
            elseif($get_tokens_by_code_array->given_name[0]){
                $reg_first_name = $get_tokens_by_code_array->given_name[0];
            }
            if($get_user_info_array->family_name[0]){
                $reg_last_name = $get_user_info_array->family_name[0];
            }
            elseif($get_tokens_by_code_array->family_name[0]){
                $reg_last_name = $get_tokens_by_code_array->family_name[0];
            }
            if($get_user_info_array->middle_name[0]){
                $reg_middle_name = $get_user_info_array->middle_name[0];
            }
            elseif($get_tokens_by_code_array->middle_name[0]){
                $reg_middle_name = $get_tokens_by_code_array->middle_name[0];
            }
            if($get_user_info_array->email[0]){
                $reg_email = $get_user_info_array->email[0];
            }
            elseif($get_tokens_by_code_array->email[0]){
                $reg_email = $get_tokens_by_code_array->email[0];
            }
            if($get_user_info_array->country[0]){
                $reg_country = $get_user_info_array->country[0];
            }
            elseif($get_tokens_by_code_array->country[0]){
                $reg_country = $get_tokens_by_code_array->country[0];
            }
            if($get_user_info_array->gender[0]){
                if($get_user_info_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }
            }
            elseif($get_tokens_by_code_array->gender[0]){
                if($get_tokens_by_code_array->gender[0] == 'male'){
                    $reg_gender = '1';
                }else{
                    $reg_gender = '2';
                }
            }
            if($get_user_info_array->locality[0]){
                $reg_city = $get_user_info_array->locality[0];
            }
            elseif($get_tokens_by_code_array->locality[0]){
                $reg_city = $get_tokens_by_code_array->locality[0];
            }
            if($get_user_info_array->postal_code[0]){
                $reg_postal_code = $get_user_info_array->postal_code[0];
            }
            elseif($get_tokens_by_code_array->postal_code[0]){
                $reg_postal_code = $get_tokens_by_code_array->postal_code[0];
            }
            if($get_user_info_array->phone_number[0]){
                $reg_home_phone_number = $get_user_info_array->phone_number[0];
            }
            elseif($get_tokens_by_code_array->phone_number[0]){
                $reg_home_phone_number = $get_tokens_by_code_array->phone_number[0];
            }
            if($get_user_info_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_user_info_array->phone_mobile_number[0];
            }
            elseif($get_tokens_by_code_array->phone_mobile_number[0]){
                $reg_phone_mobile_number = $get_tokens_by_code_array->phone_mobile_number[0];
            }
            if($get_user_info_array->picture[0]){
                $reg_avatar = $get_user_info_array->picture[0];
            }
            elseif($get_tokens_by_code_array->picture[0]){
                $reg_avatar = $get_tokens_by_code_array->picture[0];
            }
            if($get_user_info_array->street_address[0]){
                $reg_street_address = $get_user_info_array->street_address[0];
            }
            elseif($get_tokens_by_code_array->street_address[0]){
                $reg_street_address = $get_tokens_by_code_array->street_address[0];
            }
            if($get_user_info_array->birthdate[0]){
                $reg_birthdate = $get_user_info_array->birthdate[0];
            }
            elseif($get_tokens_by_code_array->birthdate[0]){
                $reg_birthdate = $get_tokens_by_code_array->birthdate[0];
            }
            if($get_user_info_array->region[0]){
                $reg_region = $get_user_info_array->region[0];
            }
            elseif($get_tokens_by_code_array->region[0]){
                $reg_region = $get_tokens_by_code_array->region[0];
            }
            $username = '';
            if (!empty($get_user_info_array->user_name[0])) {
                $username = $get_user_info_array->user_name[0];
            }
            else {
                $email_split = explode("@", $reg_email);
                $username = $email_split[0];
            }
            if(!empty($get_user_info_array->permission[0])){
                $world = str_replace("[","",$get_user_info_array->permission[0]);
                $reg_user_permission = str_replace("]","",$world);
            }
            elseif(!empty($get_tokens_by_code_array->permission[0])){
                $world = str_replace("[","",$get_user_info_array->permission[0]);
                $reg_user_permission = str_replace("]","",$world);
            }

            $user_name= Mage::getModel('admin/user')->getCollection()->addFieldToFilter('email',$reg_email)->getFirstItem()->getUsername();
            $user = Mage::getModel('admin/user')->loadByUsername($user_name);
            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }
	        $bool = false;
	        $gluu_new_roles              = json_decode(select_query('gluu/oxd/gluu_new_role'));
	        $gluu_users_can_register    = select_query('gluu/oxd/gluu_users_can_register');
	        if($gluu_users_can_register == 2 and !empty($gluu_new_roles)){
		        foreach ($gluu_new_roles as $gluu_new_role) {
			        if (strstr($reg_user_permission, $gluu_new_role)) {
				        $bool = true;
			        }
		        }
		        if(!$bool){
			        echo "<script>
                            alert('You are not authorized for an account on this application. If you think this is an error, please contact your OpenID Connect Provider (OP) admin.');
                            location.href='".$this->getBaseUrl()."';
                          </script>";
			        exit;
		        }
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
                echo "<script type='application/javascript'>
					alert('User does not exist in our system. Please check your Email ID.');
					location.href='".Mage::helper("adminhtml")->getUrl("*")."';
				 </script>";
                exit;
            }
        }
    }

    public function getIconImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image.'.png';
    }
    public function gluuoxd_get_auth_url(){
        $gluu_oxd_id                = $this->select_query('gluu/oxd/gluu_oxd_id');
        $gluu_config                = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
        $gluu_auth_type             = $this->select_query('gluu/oxd/gluu_auth_type');

        $get_authorization_url = $this->getGetAuthorizationUrl();
        $get_authorization_url->setRequestOxdId($gluu_oxd_id);
        $get_authorization_url->setRequestScope($gluu_config['config_scopes']);
        if($gluu_auth_type != "default"){
            $get_authorization_url->setRequestAcrValues([$gluu_auth_type]);
        }else{
            $get_authorization_url->setRequestAcrValues(null);
        }
        $get_authorization_url->request();
        return $get_authorization_url->getResponseAuthorizationUrl();
    }
    public function gluuoxd_get_auth_url_admin(){
        $gluu_oxd_id                = $this->select_query('gluu/oxd/gluu_oxd_id_admin');
        $gluu_config                = json_decode($this->select_query('gluu/oxd/gluu_config'),true);
        $gluu_auth_type             = $this->select_query('gluu/oxd/gluu_auth_type');

        $get_authorization_url = $this->getGetAuthorizationUrl();
        $get_authorization_url->setRequestOxdId($gluu_oxd_id);
        $get_authorization_url->setRequestScope($gluu_config['config_scopes']);
        if($gluu_auth_type != "default"){
            $get_authorization_url->setRequestAcrValues([$gluu_auth_type]);
        }else{
            $get_authorization_url->setRequestAcrValues(null);
        }
        $get_authorization_url->request();
        return $get_authorization_url->getResponseAuthorizationUrl();
    }
    public function select_query($action){
        $result = Mage::getStoreConfig($action);
        return $result;
    }
}