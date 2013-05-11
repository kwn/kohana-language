<?php defined('SYSPATH') or die('No direct script access.');


class Model_Language extends ORM {


    protected static $_current = NULL;


    protected static $_base = NULL;


    protected static $_supported_languages = NULL;


    protected $_table_columns = array(
        'id'   => 'id',
        'name' => 'name',
    );


    public static function base() {
        // TODO cache usage
        if (self::$_base === NULL) {
            self::$_base = ORM::factory('language')->where('name', '=', Kohana::$config->load('language.base_language'))->find();
        }

        return self::$_base;
    }


    public static function current() {
        // TODO cache usage
        if (self::$_current === NULL) {
            self::$_current = ORM::factory('language')->where('name', '=', i18n::lang())->find();
        }

        return self::$_current;
    }


    public static function supported_languages() {
        // TODO cache usage
        if (self::$_supported_languages === NULL) {
            self::$_supported_languages = ORM::factory('language')->find_all()->as_array('id', 'name');
        }

        return self::$_supported_languages;
    }


    public static function set_language($language) {
        if (in_array($language, Language::supported_languages())) {
            Session::instance()->set('_lang', $language);
        }
    }

}