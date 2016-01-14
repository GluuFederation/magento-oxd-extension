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


}