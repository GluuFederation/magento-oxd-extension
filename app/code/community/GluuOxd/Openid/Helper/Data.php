<?php

/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */
class GluuOxd_Openid_Helper_Data extends Mage_Core_Helper_Abstract
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

}