<?php

if (!class_exists('Plugin')) {
	die('Hacking attempt!');
}

class PluginPhpbbex extends Plugin {
        protected $aInherits = array(
            'module' => array(
                'ModuleSecurity',
                'ModuleUser',
                'ModuleSession'
            ),
            'mapper' => array(
                'ModuleUser_MapperUser',
            ),
            'entity' => array(
                'ModuleUser_EntityUser',
            )
        );

        /**
         * Активация плагина
         * В принципе, здесь нам делать ничего не нужно
         */
        public function Activate() {
                return true;
        }
        
        /**
         * Инициализация плагина
         */
        public function Init() {
                return true;
        }
        
        /**
         * Деактивация плагина
         * В принципе, тут тоже ничего не нужно делать
         */
        public function Deactivate() {
                return true;
        }
}