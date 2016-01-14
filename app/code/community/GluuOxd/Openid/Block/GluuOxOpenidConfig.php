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

    
}