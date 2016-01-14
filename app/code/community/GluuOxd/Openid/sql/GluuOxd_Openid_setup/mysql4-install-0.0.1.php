<?php
/**
 * Created by PhpStorm.
 * User: Vlad Karapetyan
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('admin/user'), 'GluuOxd_2factor_email', 'varchar(128) null');

$installer->endSetup();