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

        // * Проверяем есть ли у юзера сессия, т.е. залогинен или нет
        $iUserId = intval(E::ModuleSession()->Get('user_id', -1));
        if ($iUserId && ($oUser = $this->GetUserById($iUserId)) && $oUser->getActivate()) {
            if ($this->oSession = $oUser->getCurrentSession()) {
                if ($this->oSession->GetSessionExit()) {
                    // Сессия была закрыта
                    $this->Logout();
                    return;
                }
                $this->oUserCurrent = $oUser;
            }
        }
        // Если сессия оборвалась по таймауту (не сам пользователь ее завершил),
        // то пытаемся автоматически авторизоваться
        if (!$this->oUserCurrent) {
            $this->AutoLogin();
        }

        // * Обновляем сессию
        if (isset($this->oSession)) {
            $this->UpdateSession();
        }

        return;

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

        if ($this->oUserCurrent) {
            return;
        }
        $sSessionKey = $this->RestoreSessionKey();
        if ($sSessionKey) {
            if ($oUser = $this->GetUserBySessionKey($sSessionKey)) {
                // Не забываем продлить куку
                $this->Authorization($oUser, true);
            } else {
                //$this->Logout();
            }
        }


        return;

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

    protected function RestoreSessionKey() {
        return session_id();
    }

    protected function UpdateSession() {

        $this->oSession->setDateLast(F::Now());
        $this->oSession->setIpLast(F::GetUserIp());
        $PHPSESSID = session_id();

        $sCacheKey = "session_{$PHPSESSID}";

        // Используем кеширование по запросу
        if (false === ($data = E::ModuleCache()->Get($sCacheKey, true))) {
            $data = array(
                'time'    => time(),
                'session' => $this->oSession
            );
        } else {
            $data['session'] = $this->oSession;
        }
        if ($data['time'] <= time()) {
            $data['time'] = time() + 600;
            $this->oMapper->UpdateSession($this->oSession);
        }
        E::ModuleCache()->Set($data, $sCacheKey, array('session_update'), 'PT20M', true);
    }
}
?>