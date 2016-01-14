<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Openid_Block_GluuOxOpenidConfig extends Mage_Core_Block_Template{

    /**
     * save config in database
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
     * geting image link
    */
    public function getImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/'.$image.'.png';
    }

    /**
     * geting icone image link
     */
    public function getIconImage($image){
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        return $url.'adminhtml/default/default/GluuOxd_Openid/images/icons/'.$image.'.png';
    }
}