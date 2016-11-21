<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Gluufolder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * displaying message
     * @return string
     */
    public function displayMessage($message, $type) {
        Mage::getSingleton ( 'core/session' )->getMessages ( true );
        if (strcasecmp ( $type, "SUCCESS" ) == 0)
            Mage::getSingleton ( 'core/session' )->addSuccess ( $message );
        else if (strcasecmp ( $type, "ERROR" ) == 0)
            Mage::getSingleton ( 'core/session' )->addError ( $message );
        else if (strcasecmp ( $type, "NOTICE" ) == 0)
            Mage::getSingleton ( 'core/session' )->addNotice ( $message );
        else
            Mage::getSingleton ( 'core/session' )->addWarning ( $message );
    }

    /**
     * checking config and getting result
     * @return array or string
     */
    public function getConfig($config, $id) {

        switch ($config) {
            case 'loginTheme' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/loginTheme' );
                break;
            case 'loginCustomTheme' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/loginCustomTheme' );
                break;
            case 'iconSpace' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/iconSpace' );
                break;
            case 'iconCustomSize' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/iconCustomSize' );
                break;
            case 'iconCustomWidth' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/iconCustomWidth' );
                break;
            case 'iconCustomHeight' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/iconCustomHeight' );
                break;
            case 'iconCustomColor' :
                $result = Mage::getStoreConfig ( 'GluuOxd/Openid/iconCustomColor' );
                break;
        }
        foreach(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' )) as $custom_script){
            if ($config == $custom_script['value'].'Enable') {
                return  Mage::getStoreConfig ( 'GluuOxd/Openid/'.$custom_script['value'].'Enable' );

            }
        }
        return $result;
    }

}