<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Config\SectionedConfigInterface;

/**
 * Helper which maps locale and/or language to supported/default.
 *
 * If locale is known/asserted and known to be supported then it is far more
 * efficient to instantiate the appropriate Locale[locale code] class directly.
 *
 * @package SimpleComplex\Locale
 */
class Locale
{
    /**
     * @var string
     */
    const CONFIG_SECTION = 'lib_simplecomplex_locale';

    /**
     * @param SectionedConfigInterface $config
     * @param string $locale
     *      Formats supported: en, en_US, en_us, en-US, en-us.
     * @param string $language
     *      Formats supported: en, en_US, en_us, en-US, en-us.
     *
     * @return AbstractLocale
     */
    public static function create(SectionedConfigInterface $config, string $locale = '', string $language = '')
    {
        $locl = $locale ? str_replace('_', '-', strtolower($locale)) : '';
        $lang = $language ? str_replace('_', '-', strtolower($language)) : '';
        $locale_final = $language_final = '';
        $code_short_to_long = [];

        $localeToClass = $config->get(static::CONFIG_SECTION, 'localeToClass');

        if ($locl) {
            if (isset($localeToClass[$locl])) {
                $locale_final = $locl;
            } elseif (strlen($locl) == 2) {
                $code_short_to_long = $config->get(static::CONFIG_SECTION, 'codeShortToLong');
                $locale_final = $code_short_to_long[$locl] ?? '';
            }
        }
        if ($lang) {
            if (in_array($lang, $config->get(static::CONFIG_SECTION, 'languages'))) {
                $language_final = $lang;
            } elseif (strlen($lang) == 2) {
                if (!$code_short_to_long) {
                    $code_short_to_long = $config->get(static::CONFIG_SECTION, 'codeShortToLong');
                }
                $language_final = $code_short_to_long[$lang] ?? '';
            }
        }

        if (!$locale_final && !$language_final) {
            $locale_final = $config->get(static::CONFIG_SECTION, 'localeDefault');
            $language_final = $config->get(static::CONFIG_SECTION, 'languageDefault');
        } elseif (!$locale_final) {
            if ($lang) {
                // Arg language may be supported as locale despite not supported as langugage.
                $locale_final = $config->get(static::CONFIG_SECTION, 'languageToLocale')[$lang] ?? null;
            }
            if (!$locale_final) {
                $locale_final = $config->get(static::CONFIG_SECTION, 'languageToLocale')[$language_final] ??
                    $config->get(static::CONFIG_SECTION, 'localeDefault');
            }
        } elseif (!$language_final) {
            if ($locl) {
                // Arg locale may be supported as language despite not supported as locale.
                $language_final = $config->get(static::CONFIG_SECTION, 'localeToLanguage')[$locl] ?? null;
            }
            if (!$language_final) {
                $language_final = $config->get(static::CONFIG_SECTION, 'localeToLanguage')[$locale_final] ??
                    $config->get(static::CONFIG_SECTION, 'languageDefault');
            }
        }
        // LocaleEnUs.
        $class_locale = $localeToClass[$locale_final];

        /** @var AbstractLocale */
        return new $class_locale($language_final);
    }
}
