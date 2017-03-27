<?php
	
	/**
	 * @copyright Copyright (c) 2017, Gluu Inc. (https://gluu.org/)
	 * @license	  MIT   License            : <http://opensource.org/licenses/MIT>
	 *
	 * @package	  OpenID Connect SSO Extension by Gluu
	 * @category  Extension for Magento 1.9.x
	 * @version   3.0.1
	 *
	 * @author    Gluu Inc.          : <https://gluu.org>
	 * @link      Oxd site           : <https://oxd.gluu.org>
	 * @link      Documentation      : <https://gluu.org/docs/oxd/3.0.1/plugin/magento/>
	 * @director  Mike Schwartz      : <mike@gluu.org>
	 * @support   Support email      : <support@gluu.org>
	 * @developer Volodya Karapetyan : <https://github.com/karapetyan88> <mr.karapetyan88@gmail.com>
	 *
	 *
	 * This content is released under the MIT License (MIT)
	 *
	 * Copyright (c) 2017, Gluu inc, USA, Austin
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 *
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