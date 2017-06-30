<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Utils\Unicode;
use SimpleComplex\Config\SectionedConfigInterface;

/**
 * ???
 *
 * @property-read string $locale
 * @property-read string $language
 *
 * @package SimpleComplex\Locale
 */
class Locale
{
    /**
     * @param SectionedConfigInterface $config
     * @param string $locale
     * @param string $language
     */
    public function __construct(SectionedConfigInterface $config, string $locale = '', string $language = '')
    {
        $this->config = $config;

        $this->unicode = Unicode::getInstance();

        $this->locale = str_replace('_', '-', strtolower($locale));
        $this->language = $language ? str_replace('_', '-', strtolower($language)) : $this->locale;




        $this->locale = $this->language = strtolower($locale);

        $this->text = new LocaleText($locale, $config->get(static::CONFIG_SECTION, 'text_paths', []));
    }

    /**
     * @param string $section
     * @param string $key
     * @param string|array $default
     *
     * @return mixed|null
     */
    public function text(string $section, string $key, $default = '')
    {
        return $this->text->get($section, $key, $default);
    }
}
