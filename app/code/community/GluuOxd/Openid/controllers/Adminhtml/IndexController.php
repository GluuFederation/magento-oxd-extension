<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Openid_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    private $dataHelper = "GluuOxd_Openid";
    private $gluuOxOpenidUtilityHelper = "GluuOxd_Openid/gluuOxOpenidUtility";
    private $oxdRegisterSite = "GluuOxd_Openid/registerSite";

    /**
     * @return string
     */
    public function getDataHelper()
    {
        return Mage::helper($this->dataHelper);
    }

    /**
     * @return string
     */
    public function getGluuOxOpenidUtilityHelper()
    {
        return Mage::helper($this->gluuOxOpenidUtilityHelper);
    }

    /**
     * @return string
     */
    public function getOxdRegisterSite()
    {
        return Mage::helper($this->oxdRegisterSite);
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
                "admin_email" => Mage::getSingleton('admin/session')->getEnteredEmail(),
                "authorization_redirect_uri" => Mage::helper('customer')->getLoginUrl().'?option=getOxdSocialLogin',
                "logout_redirect_uri" => Mage::helper('customer')->getLogoutUrl(),
                "scope" => [ "openid", "profile"],
                "application_type" => "web",
                "redirect_uris" => [ Mage::helper('customer')->getLoginUrl().'?option=getOxdSocialLogin' ],
                "acr_values" => [],
            );
            if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' )))){
                $storeConfig ->saveConfig('gluu/oxd/oxd_config',serialize($config_option), 'default', 0);
            }
        }
        if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_scops' )))){
            $storeConfig ->saveConfig('gluu/oxd/oxd_openid_scops',serialize(array('openid','profile','email')), 'default', 0);
        }
        if(empty(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' )))){
            $storeConfig ->saveConfig('gluu/oxd/oxd_openid_custom_scripts',
                serialize(array(
                    array('name'=>'Google','image'=>$this->getIconImage('google'),'value'=>'gplus'),
                    array('name'=>'Basic','image'=>$this->getIconImage('basic'),'value'=>'basic'),
                    array('name'=>'Duo','image'=>$this->getIconImage('duo'),'value'=>'duo'),
                    array('name'=>'U2F token','image'=>$this->getIconImage('u2f'),'value'=>'u2f')
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
        $config_option['oxd_host_port'] = $oxd_port;
        $config_option['admin_email'] = $email;
        $storeConfig ->saveConfig('gluu/oxd/oxd_config',serialize($config_option), 'default', 0);
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
}