<?php
/**
 * Created by PhpStorm.
 * User: Vlad-Home
 * Date: 2/14/2016
 * Time: 1:44 PM
 */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Core_Model_Config();

foreach(unserialize(Mage::getStoreConfig ( 'gluu/oxd/oxd_openid_custom_scripts' )) as $custom_script){
    $setup ->deleteConfig('GluuOxd/Openid/'.$custom_script['value'].'Enable');
}
$setup->deleteConfig('gluu/oxd/oxd_id');
$setup->deleteConfig('gluu/oxd/oxd_openid_scops');
$setup->deleteConfig('gluu/oxd/oxd_config');
$setup->deleteConfig('gluu/oxd/oxd_openid_scops');
$setup->deleteConfig('gluu/oxd/oxd_openid_custom_scripts');

$setup->deleteConfig('GluuOxd/Openid/loginTheme');
$setup->deleteConfig('GluuOxd/Openid/loginCustomTheme');
$setup->deleteConfig('GluuOxd/Openid/iconSpace');
$setup->deleteConfig('GluuOxd/Openid/iconCustomSize');
$setup->deleteConfig('GluuOxd/Openid/iconCustomWidth');
$setup->deleteConfig('GluuOxd/Openid/iconCustomHeight');
$setup->deleteConfig('GluuOxd/Openid/iconCustomColor');



$installer->endSetup();