<?php

class PluginPhpbbex_ModuleUser_EntityUser extends PluginPhpbbex_Inherit_ModuleUser_EntityUser {
    public function Init() {

        parent::Init();
    }

    public function getForumId() {

        return $this->getProp('forum_user_id');
    }
}

// EOF