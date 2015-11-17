<?php

class PluginPhpbbex_ModuleUser extends PluginPhpbbex_Inherit_ModuleUser {

    protected $oMapper;

    protected $oUserCurrent = null;

    protected $oSession = null;

    protected $aUserFieldTypes
        = array(
            'social', 'contact'
        );

    protected $aAdditionalData = array('vote', 'session', 'friend', 'geo_target', 'note');

    public function Init() {
        $this->oMapper = E::GetMapper(__CLASS__);

        E::ModuleSession()->Init();
        $this->CheckAuth();

        parent::Init();
    }

    protected function AutoLogin() {

        if ($this->oUserCurrent) {
            return;
        }
        $sSessionKey = $this->RestoreSessionKey();
        if ($sSessionKey) {
            if ($oUser = $this->GetUserBySessionKey($sSessionKey)) {
                // Не забываем продлить куку
                $this->Authorization($oUser, true);
            }
        }
    }

    protected function RestoreSessionKey() {
        return session_id();
    }

    public function Authorization(ModuleUser_EntityUser $oUser, $bRemember = true, $sSessionKey = null) {

        if (!$oUser->getId() || !$oUser->getActivate()) {
            return false;
        }

        // * Получаем ключ текущей сессии
        if (is_null($sSessionKey)) {
            $sSessionKey = E::ModuleSession()->GetKey();
        }

        // * Создаём новую сессию
        if (!$this->CreateSession($oUser, $sSessionKey)) {
            return false;
        }

        // * Запоминаем в сесси юзера
        E::ModuleSession()->Set('user_id', $oUser->getId());
        E::ModuleSession()->Set('forum_user_id', $oUser->getForumUserId());
        $this->oUserCurrent = $oUser;

        // * Ставим куку
        if ($bRemember) {
            E::ModuleSession()->SetCookie($this->GetKeyName(), $sSessionKey, Config::Get('sys.cookie.time'));
            E::ModuleSession()->SetCookie(Config::Get('plugin.phpbbex.cookie.user_id'), $oUser->getForumUserId(), Config::Get('sys.cookie.time'));
        }
        return true;
    }

    protected function CheckAuth() {
        $sSessionKey = session_id();

        if (!$sSessionKey)  return false;

        $iSessionUserId = intval(E::ModuleSession()->Get('user_id', -1));
        $iSessionForumUserId = intval(E::ModuleSession()->Get('forum_user_id', -1));

        $oUser = $this->GetUserBySessionKey($sSessionKey);

        if($oUser) {
            if($iSessionUserId > -1 && $iSessionUserId != $oUser->GetId()) {
                $this->Logout();
                return false;
            }

            if($iSessionForumUserId > -1 && $iSessionForumUserId != $oUser->GetForumUserId()) {
                E::ModuleSession()->Set('forum_user_id', 1);
                E::ModuleSession()->DelCookie(Config::Get('plugin.phpbbex.cookie.user_id'));
                return false;
            }
        }
        return true;
    }

    public function GetUserBySessionKey($sKey) {
        $aUser = $this->oMapper->GetUserBySessionKey($sKey);
        return $aUser ? $aUser[0] : null;
    }
}
?>