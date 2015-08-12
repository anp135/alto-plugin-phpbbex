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
        F::IncludeFile(Plugin::GetPath('phpbbex') . 'libs/phpbbex_functions.php');
    }

    public function Salted($sData, $sType = null) {
        $phpbbex = new Phpbbex();
        return ($sType == 'pass') ? $phpbbex->phpbb_hash($sData) : parent::Salted($sData, $sType);
    }
    public function CheckSalted($sSalted, $sData, $sType = null) {
        $phpbbex = new Phpbbex();
        return ($sType == 'pass') ? $phpbbex->phpbb_check_hash(($sData), $sSalted) : parent::CheckSalted($sSalted, $sData, $sType);
    }
}
?>