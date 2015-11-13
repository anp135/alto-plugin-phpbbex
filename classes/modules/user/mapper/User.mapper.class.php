<?php

class PluginPhpbbex_ModuleUser_MapperUser extends PluginPhpbbex_Inherit_ModuleUser_MapperUser {
    public function GetUsersByArrayId($aUsersId) {

        if (!is_array($aUsersId) || count($aUsersId) == 0) {
            return array();
        }

        $sql
            = "
            SELECT
                u.user_id AS ARRAY_KEY,
				u.*,
				IF(ua.user_id IS NULL,0,1) as user_is_administrator,
				IF(fu.user_id IS NULL,1,fu.user_id) as forum_user_id,
				ab.banline, ab.banunlim, ab.banactive, ab.bancomment
			FROM
				?_user as u
				LEFT JOIN ?_user_administrator AS ua ON u.user_id=ua.user_id
				LEFT JOIN ?_adminban AS ab ON u.user_id=ab.user_id AND ab.banactive=1
				LEFT JOIN anp135_forum.phpbb_users AS fu ON u.user_login = fu.username
			WHERE
				u.user_id IN(?a)
			LIMIT ?d
			";
        $aUsers = array();
        if ($aRows = $this->oDb->select($sql, $aUsersId, count($aUsersId))) {
            $aUsers = Engine::GetEntityRows('User', $aRows, $aUsersId);
        }
        return $aUsers;
    }
}

// EOF