<?php
/**
 * Created by PhpStorm.
 * User: anp135
 * Date: 17.04.15
 * Time: 0:29
 */

class PluginPhpbbex_ModuleSecurity extends PluginPhpbbex_Inherit_ModuleSecurity {

    public function Init() {
        parent::Init();
        //F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbbex_functions.php');
        //F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb-3.1_functions.php');
        //F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/config_php_file.php');
        //F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/di/container_builder.php');
        //F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/di/extension/core.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/driver_interface.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/base.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/helper.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/bcrypt.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/bcrypt_2y.php');
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/driver/bcrypt_wcf2.php');

        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbb/passwords/manager.php');
    }

    public function Salted($sData, $sType = null) {
        //$phpbbex = new Phpbb\passwords\manager();
        //return ($sType == 'pass') ? $phpbbex->phpbb_hash($sData) : parent::Salted($sData, $sType);
        return parent::Salted($sData, $sType);
    }
    public function CheckSalted($sSalted, $sData, $sType = null) {
        $phpbb = new Phpbb\passwords\manager();
        return $phpbb->check($sData, '$2y$10$5ICzEIk/Ts9m.IR5/uSjFOrdu9UBcsUVynnOSOKEXby/Lm8elg4WK');
        //$phpbbex = new phpbb\passwords\manager();
        //return ($sType == 'pass') ? $phpbbex->phpbb_check_hash(($sData), $sSalted) : parent::CheckSalted($sSalted, $sData, $sType);
        //return parent::CheckSalted($sSalted, $sData, $sType);
    }
}
?>