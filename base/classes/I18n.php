<?php

class I18n extends Kohana_I18n
{
    static function plural($count, $one, $many, $nul = NULL)
    {
        if (empty($nul)) {
            $nul = $many;
        }
        $l2 = $count % 100;
        $l1 = $count % 10;
        if ($l1 == 0 || ($l2 > 10 && $l2 < 20)) {
            return __($nul);
        }
        if ($l1 == 1) {
            return __($one);
        }
        if ($l1 <= 4) {
            return __($many);
        }
        return __($nul);
    }

    /**
     * Returns the translation table for a given language.
     *
     *     // Get all defined Spanish messages
     *     $messages = I18n::load('es-es');
     *
     * @param   string $lang language to load
     * @return  array
     * @throws
     */
    public static function load($lang)
    {
        if (isset(I18n::$_cache[$lang])) {
            return I18n::$_cache[$lang];
        }

        $cache = Cache::instance();
        $key = SITE . '_i18n_' . $lang;
        if (!$table = $cache->get($key)) {
            $section = SITE;
            if ($section == 'cnsulting') $section = 'consult';
            $section = (new Model_Section())->where('section', '=', $section)->find();
            $table = (new Model_Localization())
                ->where('section_id', '=', $section->id)
                ->or_where('section_id', '=', 0)
                ->order_by('section_id')->find_all()->as_array('key', $lang);
            $cache->set_with_tags($key, $table, 86400, array('i18n'));
        }

        // Cache the translation table locally
        return I18n::$_cache[$lang] = $table;
    }

    public static function get($string, $lang = null)
    {
        static $inuse = false;
        if ($inuse) return $string;
        $inuse = true;
        $ret = parent::get($string, $lang);
        $inuse = false;
        return $ret;
    }

//    public static function get($string, $lang = NULL)
//    {
//        if (!$lang) {
//            // Use the global target language
//            $lang = I18n::$lang;
//        }
//
//        // Load the translation table for this language
//        $table = I18n::load($lang);
//
//        // Return the translated string if it exists
//        if (isset($table[$string])) {
//            return $table[$string];
//        }
//
//       // Cache::instance()->delete_tag('i18n');
//        I18n::$_cache[$lang][$string] = $string;
////        (new Model_Localization())->values(array(
////            'key' => $string,
////            'ru' => $string,
////            'uk' => $string,
////            'type' => 0
////        ))->save();
//
//        return $string;
//    }
} 