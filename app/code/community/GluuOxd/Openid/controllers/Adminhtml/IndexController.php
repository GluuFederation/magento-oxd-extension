<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Openid_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    private $dataHelper = "GluuOxd_Openid";
    private $oxdRegisterSite = "GluuOxd_Openid/registerSite";

    /**
     * @return string
     */
    public function getOxdRegisterSiteHelper()
    {
        return Mage::helper($this->oxdRegisterSiteHelper);
    }

    /**
     * @return string
     */
    public function getDataHelper(){
        return Mage::helper($this->dataHelper);
    }

    /**
     * @return gluuOxd admin index page
     */
    public function indexAction(){
        $storeConfig = new Mage_Core_Model_Config();
        if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' )))){

            $config_option = array(
                "oxd_host_ip" => '127.0.0.1',
                "oxd_host_port" =>8099,
                "admin_email" => Mage::getSingleton('admin/session')->getUser()->getEmail(),
                "authorization_redirect_uri" => Mage::helper('customer')->getLoginUrl().'?option=getOxdSocialLogin',
                "logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                "scope" => ["openid","profile","email","address","mobile_phone","phone"],
                "application_type" => "web",
                "redirect_uris" => [ Mage::helper('customer')->getLoginUrl().'?option=getOxdSocialLogin' ],
                "acr_values" => [],
            );
            if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' )))){
                $storeConfig ->saveConfig('gluu/oxd/oxd_config',serialize($config_option), 'default', 0);
            }
        }
        if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_scops' )))){
            $storeConfig ->saveConfig('gluu/oxd/oxd_openid_scops',serialize(array("openid","profile","email","address","mobile_phone","phone")), 'default', 0);
        }
        if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' )))){
            $storeConfig ->saveConfig('gluu/oxd/oxd_openid_custom_scripts',
                serialize(array(
                    array('name'=>'Google','image'=>$this->getAddedImage('google.png'),'value'=>'gplus'),
                    array('name'=>'Basic','image'=>$this->getAddedImage('basic.png'),'value'=>'basic'),
                    array('name'=>'Duo','image'=>$this->getAddedImage('duo.png'),'value'=>'duo'),
                    array('name'=>'U2F token','image'=>$this->getAddedImage('u2f.png'),'value'=>'u2f')
                )), 'default', 0);
        }
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
            Mage::log($e->getMessage(), null, 'gluuoxd_error.log', true);
        }
    }

    /**
     * saving and registration data geting oxd_id
     */
    public function generalFunctionAction(){
        $storeConfig = new Mage_Core_Model_Config();
        $params = $this->getRequest()->getParams();
        $datahelper = $this->getDataHelper();
        $email = $params['loginemail'];
        $oxd_port = $params['oxd_port'];
        $illegal = "#$%^*()+=[]';,/{}|:<>?~";
        $illegal = $illegal . '"';
        if( $this->empty_or_null( $email )  ||  $this->empty_or_null( $oxd_port ) ) {
            $datahelper->displayMessage('All the fields are required. Please enter valid entries.',"ERROR");
            $this->redirect("*/*/index");
            return;
        }
        if( $oxd_port  > 65535 && $oxd_port  < 0){
            $datahelper->displayMessage('Enter your oxd host port (Min. number 0, Max. number 65535).',"ERROR");
            $this->redirect("*/*/index");
            return;
        } else if(strpbrk($email,$illegal)) {
            $datahelper->displayMessage('Please match the format of Email. No special characters are allowed.',"ERROR");
            $this->redirect("*/*/index");
            return;
        }
        $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
        $config_option['oxd_host_port'] = $params['oxd_port'];
        $config_option['admin_email'] = $email;
        $storeConfig ->saveConfig('gluu/oxd/oxd_config',serialize($config_option), 'default', 0);
        $jsonString = file_get_contents(Mage::getBaseDir('skin').'/adminhtml/default/default/GluuOxd_Openid/oxd-server/conf/oxd-conf.json');
        $data = json_decode($jsonString, true);
        $data['op_host'] = $params['gluu_server_host'];
        $data['port'] = $params['oxd_port'];
        $newJsonString = json_encode($data);
        file_put_contents(Mage::getBaseDir('skin').'/adminhtml/default/default/GluuOxd_Openid/oxd-server/conf/oxd-conf.json', $newJsonString);
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if(!exec('netstat -aon |find/i "listening" |find "'.$oxd_port.'"')){
                $startDir = Mage::getBaseDir('skin').'/adminhtml/default/default/GluuOxd_Openid/oxd-server/bin';
                chdir($startDir);
                $fileName = 'oxd-start.bat';
                exec($fileName);
            }else{
                $datahelper->displayMessage('Port is using, please use port which is not using.',"ERROR");
                $this->redirect("*/*/index");
                return;
            }
        } else {
            if(!exec('netstat -tulpn | grep :'.$oxd_port)){
                $startDir = Mage::getBaseDir('skin').'/adminhtml/default/default/GluuOxd_Openid/oxd-server/bin';
                chdir($startDir);
                $fileName = './oxd-start.sh';
                exec($fileName);
            }else{
                $datahelper->displayMessage('Port is using, please use port which is not using.',"ERROR");
                $this->redirect("*/*/index");
                return;
            }
        }


        $registerSite = $this->getOxdRegisterSiteHelper();
        $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
        $registerSite->setRequestAcrValues($config_option['acr_values']);
        $registerSite->setRequestAuthorizationRedirectUri($config_option['authorization_redirect_uri']);
        $registerSite->setRequestRedirectUris($config_option['redirect_uris']);
        $registerSite->setRequestLogoutRedirectUri($config_option['logout_redirect_uri']);
        $registerSite->setRequestContacts([$config_option['admin_email']]);
        $registerSite->setRequestApplicationType('web');
        $status = $registerSite->request();

        if(!$status['status']){
            $datahelper->displayMessage($status['message'],"ERROR");
            $this->redirect("*/*/index");
            return;
        }
        if($registerSite->getResponseOxdId()){
            $storeConfig ->saveConfig('gluu/oxd/oxd_id',$registerSite->getResponseOxdId(), 'default', 0);
            $datahelper->displayMessage('Site registered Successful. You can configure Gluu and Social Login now.',"SUCCESS");
            $this->redirect("*/*/index");
            return;
        }else{
            $datahelper->displayMessage('Invalid Credentials',"ERROR");
            $this->redirect("*/*/index");
            return;
        }

    }

    /**
     * adding multiple custom scripts  and multiple scopes
     */
    public function scopeCustomScriptAction(){
        $params = $this->getRequest()->getParams();
        $storeConfig = new Mage_Core_Model_Config();
        $datahelper = $this->getDataHelper();
        $message = '';
        if(isset($params['count_scripts'])){
            $error_array = array();
            $error = true;
            for($i=1; $i<=$params['count_scripts']; $i++){
                if(isset($params['name_in_site_'.$i]) && !empty($params['name_in_site_'.$i]) && isset($params['name_in_gluu_'.$i]) && !empty($params['name_in_gluu_'.$i]) && isset($_FILES['images_'.$i]) && !empty($_FILES['images_'.$i])){
                    $custom_scripts = unserialize(Mage::getStoreConfig('gluu/oxd/oxd_openid_custom_scripts'));
                    foreach($custom_scripts as $custom_script){
                        if($custom_script['value'] == $params['name_in_gluu_'.$i] || $custom_script['name'] == $params['name_in_site_'.$i]){
                            $error = false;
                            array_push($error_array, $i);
                        }
                    }
                    if($error){
                        $uploader = new Varien_File_Uploader(array(
                            'name' => $_FILES['images_'.$i]['name'],
                            'type' => $_FILES['images_'.$i]['type'],
                            'tmp_name' => $_FILES['images_'.$i]['tmp_name'],
                            'error' => $_FILES['images_'.$i]['error'],
                            'size' => $_FILES['images_'.$i]['size']
                        ));
                        $uploader->setAllowedExtensions(array('png'));
                        $uploader->setAllowRenameFiles(true);

                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('skin') . DS . 'adminhtml' . DS. 'default' . DS. 'default' . DS. 'GluuOxd_Openid' . DS. 'images' . DS. 'icons' . DS;
                        $img = $uploader->save($path, $_FILES['images']['name']);
                        if($img['file']){
                            array_push($custom_scripts, array('name'=>$params['name_in_site_'.$i],'image'=>$this->getAddedImage($img['file']),'value'=>$params['name_in_gluu_'.$i]));
                            $storeConfig ->saveConfig('gluu/oxd/oxd_openid_custom_scripts',serialize($custom_scripts), 'default', 0);
                            $message.= 'New custom scripts name = '.$params['name_in_site_'.$i].' and name in gluu = '.$params['name_in_gluu_'.$i].' added Successful!<br/>';
                        }else{
                            $datahelper->displayMessage('Name = '.$params['name_in_site_'.$i]. ' or value = '. $params['name_in_gluu_'.$i]. ' is exist.',"ERROR");
                            $this->redirect("*/*/index");
                        }


                    }else{
                        $datahelper->displayMessage('Name = '.$params['name_in_site_'.$i]. ' or value = '. $params['name_in_gluu_'.$i]. ' is exist.',"ERROR");
                        $this->redirect("*/*/index");
                    }
                }else{

                    $datahelper->displayMessage('Necessary to fill the hole row.',"ERROR");
                    $this->redirect("*/*/index");
                }
            }
        }

        if(!empty($params['scope_name']) && isset($params['scope_name'])){
            foreach($params['scope_name'] as $scope){
                if($scope){
                    $get_scopes = unserialize(Mage::getStoreConfig('gluu/oxd/oxd_openid_scops'));
                    array_push($get_scopes, $scope);
                    $storeConfig ->saveConfig('gluu/oxd/oxd_openid_scops',serialize($get_scopes), 'default', 0);
                    $message.= 'New scopes name = '.$scope.' added Successful!<br/>';
                }
            }
        }
        $oxd_config = unserialize(Mage::getStoreConfig('gluu/oxd/oxd_config'));
        if(!empty($params['scope']) && isset($params['scope'])){
            $oxd_config['scope'] = $params['scope'];
            $message.= 'Scopes updated Successful!<br/>';
        }else{
            $oxd_config['scope'] =  unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
        }

        $storeConfig ->saveConfig('gluu/oxd/oxd_config',serialize($oxd_config), 'default', 0);
        $datahelper->displayMessage($message,"SUCCESS");
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

    /**
     * save social login page data
     */
    public function saveSocialLoginConfAction(){


        $params = $this->getRequest()->getParams();

        $storeConfig = new Mage_Core_Model_Config();
        foreach(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' )) as $custom_script){
            $storeConfig ->saveConfig('GluuOxd/Openid/'.$custom_script['value'].'Enable',$params['gluuoxd_openid_'.$custom_script['value'].'_enable'], 'default', 0);
        }
        $storeConfig ->saveConfig('GluuOxd/Openid/loginTheme',$params['gluuoxd_openid_login_theme'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/loginCustomTheme',$params['gluuoxd_openid_login_custom_theme'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/iconSpace',$params['gluuOxLogin_icon_space'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/iconCustomSize',$params['gluuOxLogin_icon_custom_size'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/iconCustomWidth',$params['gluuOxLogin_icon_custom_width'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/iconCustomHeight',$params['gluuOxLogin_icon_custom_height'], 'default', 0);
        $storeConfig ->saveConfig('GluuOxd/Openid/iconCustomColor',$params['gluuOxLogin_icon_custom_color'], 'default', 0);

        //print_r($params);
        //exit();
        $helper = $this->getDataHelper();
        $helper->displayMessage('Your configuration has been saved.',"SUCCESS");
        $this->redirect("*/*/index");
    }

    /**
     * getting added image link
     */
    public function getAddedImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image;
    }

    /**
     * getting icon image link
     */
    public function getIconImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image.'.png';
    }

    /**
     * deleting custom scripts
     */
    public function deleteCustomScriptAction(){
        $storeConfig = new Mage_Core_Model_Config();
        $params = $this->getRequest()->getParams();
        $datahelper = $this->getDataHelper();
        $custom_scripts = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' ));
        $up_cust_sc =  array();
        foreach($custom_scripts as $custom_script){
            if($custom_script['value'] !=$params['value_script']){
                array_push($up_cust_sc,$custom_script);
            }
        }
        $storeConfig ->saveConfig('gluu/oxd/oxd_openid_custom_scripts',serialize($up_cust_sc), 'default', 0);

        $datahelper->displayMessage('Custom scripts deleted Successful.',"SUCCESS");
        $this->redirect("*/*/index");
    }

    /**
     * getting ID from session
     */
    private function getId(){
        return $this->getSession()->getUser()->getUserId();
    }

}