<?php
/**
 * @copyright Copyright (c) 2017 netz98 GmbH (http://www.netz98.de)
 *
 * @see LICENSE
 */

namespace N98\Deployer\Task;

use N98\Deployer\Registry as Deployer;

/**
 * MagentoTasks
 */
class MagentoTasks extends TaskAbstract
{
    const TASK_MAINTENANCE_MODE_ENABLE = 'magento:maintenance_mode_enable';
    const TASK_MAINTENANCE_MODE_DISABLE = 'magento:maintenance_mode_disable';
    const TASK_SYMLINKS_ENABLE = 'magento:symlinks_enable';
    const TASK_SETUP_UPGRADE = 'magento:setup_upgrade';
    const TASK_SETUP_DOWNGRADE = 'magento:setup_downgrade';
    const TASK_CONFIG_DATA_IMPORT = 'magento:config_data_import';
    const TASK_CMS_DATA_IMPORT = 'magento:cms_data_import';
    const TASK_CACHE_ENABLE = 'magento:cache_enable';
    const TASK_CACHE_DISABLE = 'magento:cache_disable';
    const TASK_CACHE_CLEAR = 'magento:cache_clear';

    public static function register()
    {
        Deployer::task(
            self::TASK_MAINTENANCE_MODE_ENABLE, 'Enable maintenance mode',
            function () { MagentoTasks::toggleMaintenenceMode(true); }
        );
        Deployer::task(
            self::TASK_MAINTENANCE_MODE_DISABLE, 'Disable maintenance mode',
            function () { MagentoTasks::toggleMaintenenceMode(false); }
        );
        Deployer::task(
            self::TASK_SYMLINKS_ENABLE, 'Allow symlinks',
            function () { MagentoTasks::allowSymlinks(); }
        );
        Deployer::task(
            MagentoTasks::TASK_SETUP_UPGRADE, 'run Magento Updates',
            function () { MagentoTasks::runSetupUpgrade(); }, ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_SETUP_DOWNGRADE, 'run Magento Downgrade',
            function () { MagentoTasks::runSetupDowngrade(); },
            ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_CONFIG_DATA_IMPORT, 'Magento config update',
            function () { MagentoTasks::updateMagentoConfig(); },
            ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_CMS_DATA_IMPORT, 'Magento CMS import',
            function () { MagentoTasks::importCmsData(); },
            ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_CACHE_ENABLE, 'Enable Magento Cache',
            function () { MagentoTasks::activateMagentoCache(true); },
            ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_CACHE_DISABLE, 'Disable Magento Cache',
            function () { MagentoTasks::activateMagentoCache(false); },
            ['db']
        );
        Deployer::task(
            MagentoTasks::TASK_CACHE_CLEAR, 'Clear Magento Cache',
            function () { MagentoTasks::flushMagentoCache(); },
            ['db']
        );
    }

    /**
     * Toggle Maintenence Mode
     *
     * @param bool $enabled
     */
    public static function toggleMaintenenceMode($enabled)
    {
        $srcDir = self::$srcDir;
        $maintenance = $enabled === true ? 'maintenance:enable' : 'maintenance:disable';

        \Deployer\run("cd $srcDir; php bin/magento $maintenance");
    }

    /**
     * Allow Symlinks
     */
    public static function allowSymlinks()
    {
        $srcDir = self::$srcDir;
        $binMagerun = self::getBinMagerun2();
        \Deployer\run("cd $srcDir; $binMagerun dev:symlinks  --global --on");
    }

    /**
     * Run Magento setup:upgrade
     */
    public static function runSetupUpgrade()
    {
        $srcDir = self::$srcDir;
        \Deployer\run("cd $srcDir; php bin/magento setup:upgrade --keep-generated");
    }

    /**
     * Run Magento setup:upgrade
     */
    public static function runSetupDowngrade()
    {
        $srcDir = self::$srcDir;
        $binMagerun = self::getBinMagerun2();
        \Deployer\run("cd $srcDir; $binMagerun sys:setup:downgrade-versions");
    }

    /**
     * Import the Magento Config using the config data files
     */
    public static function updateMagentoConfig()
    {
        $env = \Deployer\get('config-store-env');
        if (empty($env)) {
            $env =  \Deployer\input()->getArgument('stage');
        }

        $srcDir = self::$srcDir;
        \Deployer\run("cd $srcDir; php bin/magento config:data:import ../config/store $env");
    }

    /**
     * Import CMS data
     */
    public static function importCmsData()
    {
        $srcDir = self::$srcDir;
        \Deployer\run("cd $srcDir; php bin/magento cms:import");
    }

    /**
     * Enable or Disable MagentoCache
     *
     * @param $enabled
     */
    public static function activateMagentoCache($enabled)
    {
        $srcDir = self::$srcDir;
        $cache = $enabled === true ? 'cache:enable' : 'cache:disable';

        \Deployer\run("cd $srcDir; php bin/magento $cache");
    }

    /**
     * Flush Magento cache
     */
    public static function flushMagentoCache()
    {
        $srcDir = self::$srcDir;
        \Deployer\run("cd $srcDir; php bin/magento cache:flush");
    }

    /**
     * @return string
     */
    protected static function getBinMagerun2()
    {
        return \Deployer\get('bin/n98_magerun2');
    }

}