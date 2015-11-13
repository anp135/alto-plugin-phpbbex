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

        $sSessionUserKey = Config::Get('security.session.user_key');
        $this->oMapper = Engine::GetMapper('ModuleUser');

        // * Проверяем есть ли у юзера сессия, т.е. залогинен или нет
        $nUserId = intval($this->Session_Get($sSessionUserKey));

        //135 anonymous
        if ($nUserId == 1)
            return;


        $this->AutoLogin();
        return;

        if ($nUserId && ($oUser = $this->GetUserById($nUserId)) && $oUser->getActivate()) {
            if ($this->oSession = $oUser->getSession()) {
                if ($this->oSession->GetSessionExit()) {
                    // Сессия была закрыта
                    $this->Logout();
                    return;
                }
                /**
                 * Сюда можно вставить условие на проверку айпишника сессии
                 */
                $this->oUserCurrent = $oUser;
            }
        }
        /**
         * Запускаем автозалогинивание
         * В куках стоит время на сколько запоминать юзера
         */
        $this->AutoLogin();

        // * Обновляем сессию
        if (isset($this->oSession)) {
            $this->UpdateSession();
        }
    }

    protected function AutoLogin() {

        $sSessionUserKey = Config::Get('security.session.user_key');

        if ($this->oUserCurrent) {
            return;
        }

        $sSessionKey = $this->RestoreSessionKey();

        if ($sSessionKey) {
            $oUser = $this->GetUserBySessionKey($sSessionKey);
            $nSessionUserId = intval($this->Session_Get($sSessionUserKey));
            if ($oUser && ($nSessionUserId == $oUser->getId()) || $nSessionUserId == 0) {
                // Не забываем продлить куку
                $this->Authorization($oUser, true);

            } elseif (isset($oUser) && $nSessionUserId != $oUser->getId()) {
                //$this->Logout();
            }
        }
    }

    public function Authorization(ModuleUser_EntityUser $oUser = null, $bRemember = true, $sSessionKey = null) {

        if (!$oUser || !$oUser->getId() || !$oUser->getActivate()) {
            return false;
        }

        // * Получаем ключ текущей сессии
        if (is_null($sSessionKey)) {
            $sSessionKey = $this->Session_GetKey();
        }

        // * Создаём новую сессию
        if (!$this->CreateSession($oUser, $sSessionKey)) {
            return false;
        }

        // * Запоминаем в сесси юзера
        $this->Session_Set('user_id', $oUser->getId());
        $this->oUserCurrent = $oUser;

        //135 Дописываем в сессию user_id форума
        $this->Session_Set('forum_user_id', $oUser->getForumId());
        $this->Session_SetCookie('4x4krasnodar_u', $oUser->getForumId(), Config::Get('sys.cookie.time'));

        // * Ставим куку
        if ($bRemember) {
            $this->Session_SetCookie($this->GetKeyName(), $sSessionKey, Config::Get('sys.cookie.time'));
        }

        return true;
    }

}
?>