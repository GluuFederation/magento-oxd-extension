<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Openid_Customer_IndexController extends Mage_Core_Controller_Front_Action
{
    private $logout = "GluuOxd_Openid/logout";

    /**
     * @return string
     */
    public function getLogout()
    {
        return Mage::helper($this->logout);
    }
    /**
     * Administrator logout action
     */
    public function logoutAction()
    {
        $logout = $this->getLogout();
        $config_option = unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_config' ));
        $oxd_id = Mage::getStoreConfig ( 'gluu/oxd/oxd_id' );
        $logout->setRequestOxdId($oxd_id);
        $logout->setRequestPostLogoutRedirectUri($config_option['logout_redirect_uri']);
        $logout->setRequestIdToken($_SESSION['user_oxd_access_token']);
        $logout->request();
        /** @var $adminSession Mage_Admin_Model_Session */
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        $adminSession->addSuccess(Mage::helper('adminhtml')->__('You have logged out.'));

        $this->_redirect('*');
    }

}