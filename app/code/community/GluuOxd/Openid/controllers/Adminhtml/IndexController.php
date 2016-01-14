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
}