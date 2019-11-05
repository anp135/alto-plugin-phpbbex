<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Модуль для работы с сессиями
 * Выступает в качестве надстроки для стандартного механизма сессий
 *
 * @package engine.modules
 * @since 1.0
 */
class PluginPhpbb_ModuleSession extends PluginPhpbb_Inherit_ModuleSession {
    protected $sId = null;
    protected $aData = array();
    protected $aFlashUserAgent = array(
        'Shockwave Flash'
    );
    protected $bUseStandartSession = true;

    public function Init() {
        parent::Init();
    }

    protected function Start() {

        if ($this->bUseStandartSession) {
            $sSysSessionName = Config::Get('sys.session.name');
            if(!session_id()) {
                session_name($sSysSessionName);
                session_set_cookie_params(
                    Config::Get('sys.session.timeout'),
                    Config::Get('sys.session.path'),
                    Config::Get('sys.session.host')
                );
                session_start();
            }


            //135 implement PHP standart session management
            //session_id() ? true : session_start();
            $this->sId = session_id();
            parent::SetCookie($sSysSessionName, $this->sId, Config::Get('sys.session.timeout'));

            if (!session_id()) {

                // * Попытка подменить идентификатор имени сессии через куку
                if (isset($_COOKIE[$sSysSessionName])) {
                    if (!is_string($_COOKIE[$sSysSessionName])) {
                        $this->DelCookie($sSysSessionName . '[]');
                        $this->DelCookie($sSysSessionName);
                    } elseif (!preg_match('/^[\-\,a-zA-Z0-9]{1,128}$/', $_COOKIE[$sSysSessionName])) {
                        $this->DelCookie($sSysSessionName);
                    }
                }

                // * Попытка подменить идентификатор имени сессии в реквесте
                $aRequest = array_merge($_GET, $_POST); // Исключаем попадаение $_COOKIE в реквест
                if (@ini_get('session.use_only_cookies') === '0' && isset($aRequest[$sSysSessionName]) && !is_string($aRequest[$sSysSessionName])) {
                    session_name($this->GenerateId());
                }

                // * Даем возможность флешу задавать id сессии
                $sSSID = F::GetRequestStr('SSID');
                if ($sSSID && $this->_validFlashAgent() && preg_match('/^[\w]{5,40}$/', $sSSID)) {
                    session_id($sSSID);
                    session_start();
                } else {
                    // wrong session ID, regenerates it
                    session_regenerate_id();
                    session_start();
                }
            }
        } else {
            $this->SetId();
            $this->ReadData();
        }
    }
    public function GetKey() {

        return $this->GetId();
    }
    public function GetId() {

        if ($this->bUseStandartSession) {
            return session_id();
        } else {
            return $this->sId;
        }
    }
}

// EOF