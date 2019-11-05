<?php

class PluginPhpbb_ModuleUser_EntityUser extends PluginPhpbb_Inherits_ModuleUser_EntityUser {
    public function Init() {
        parent::Init();
    }

    public function getForumUserId() {
        $iForumUserId = $this->getProp('forum_user_id');
        return $iForumUserId ? intval($iForumUserId) : null;
    }
}

// EOF