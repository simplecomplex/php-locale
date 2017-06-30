<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Utils\Explorable;
use SimpleComplex\Config\SectionedConfigInterface;

/**
 * Abstract locale.
 *
 * @dependency-injection-container locale
 *
 * @property-read string $locale
 * @property-read string $language
 * @property-read array $temporal
 * @property-read array $numeric
 * @property-read array $currency
 *
 * @package SimpleComplex\Locale
 */
abstract class AbstractLocale extends Explorable
{
    // Extending class MUST override these properties.--------------------------

    /**
     * Do override.
     *
     * @var string
     */
    protected $locale = 'xx-xx';

    /**
     * Do override.
     *
     * @var array
     */
    const TEMPORAL = [
        'datePartSequence' => 'Ymd',
        'dateShort' => 'Y-m-d',
        'dateLong' => 'Y-m-d H:i',
        'timeShort' => 'H:i',
        'timeLong' => 'H:i:s',
        'weekdayFirst' => 'monday',
    ];

    /**
     * Do override.
     *
     * @var array
     */
    const NUMERIC = [
        'decimalMark' => '.',
        'thousandSeparator' => ' ',
        'noFractionMark' => '/-',
    ];

    /**
     * Do override.
     *
     * @var array[]
     */
    const CURRENCY = [
        [
            'title' => 'money',
            'abbreviation' => 'XYZ',
            'sign' => '¤',
        ],
    ];


    // Explorable.--------------------------------------------------------------

    /**
     * @var array
     */
    protected $explorableIndex = [
        'locale',
        'language',
        'temporal',
        'numeric',
        'currency',
    ];

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws \OutOfBoundsException
     *      If no such instance property.
     */
    public function __get($name)
    {
        if (in_array($name, $this->explorableIndex, true)) {
            switch ($name) {
                case 'temporal':
                    // Copy, to secure read-only.
                    $v = static::TEMPORAL;
                    return $v;
                case 'numeric':
                    // Copy, to secure read-only.
                    $v = static::NUMERIC;
                    return $v;
                case 'currency':
                    // Copy, to secure read-only.
                    $v = static::CURRENCY;
                    return $v;
            }
            return $this->{$name};
        }
        throw new \OutOfBoundsException(get_class($this) . ' instance exposes no property[' . $name . '].');
    }

    /**
     * @param string $name
     * @param mixed|null $value
     *
     * @return void
     *
     * @throws \OutOfBoundsException
     *      If no such instance property.
     * @throws \RuntimeException
     *      If that instance property is read-only.
     */
    public function __set($name, $value) /*: void*/
    {
        if (in_array($name, $this->explorableIndex, true)) {
            throw new \RuntimeException(get_class($this) . ' instance property[' . $name . '] is read-only.');
        }
        throw new \OutOfBoundsException(get_class($this) . ' instance exposes no property[' . $name . '].');
    }


    // Business.----------------------------------------------------------------

    /**
     * Config var default section.
     *
     * @var string
     */
    const CONFIG_SECTION = 'lib_simplecomplex_locale';

    /**
     * @var string
     */
    protected $language;

    /**
     * @var SectionedConfigInterface
     */
    protected $config;

    /**
     * @var LocaleText
     */
    protected $text;

    /**
     * @param SectionedConfigInterface $config
     * @param string $language
     *      Format: en-us.
     *      Empty: uses config localeToLanguage[locale].
     */
    public function __construct(SectionedConfigInterface $config, string $language = '')
    {
        $this->config = $config;
        if (!$language) {
            $language = $config->get(static::CONFIG_SECTION, 'localeToLanguage')[$this->locale];
        }
        $this->language = $language;
        $this->text = new LocaleText($language, $config->get(static::CONFIG_SECTION, 'localeTextPaths', []));
    }

    /**
     * Get localized text.
     *
     * @param string $section
     * @param string $key
     * @param string|array $default
     *      Beware that an item may be a list.
     *
     * @return mixed|null
     *      null: if using arg default null.
     */
    public function text(string $section, string $key, $default = '')
    {
        return $this->text->get($section, $key, $default);
    }

    /**
     * @param string $key
     *      Empty: return all temporal settings.
     *
     * @return mixed|array
     *
     * @throws \InvalidArgumentException
     *      Unsupported arg key.
     */
    public function temporal(string $key = '')
    {
        if (!$key) {
            return static::TEMPORAL;
        }
        if (isset(static::TEMPORAL[$key])) {
            return static::TEMPORAL[$key];
        }
        throw new \InvalidArgumentException('Arg key not supported, key[' . $key . '].');
    }

    /**
     * @param string $key
     *      Empty: return all numeric settings.
     *
     * @return mixed|array
     *
     * @throws \InvalidArgumentException
     *      Unsupported arg key.
     */
    public function numeric(string $key = '')
    {
        if (!$key) {
            return static::NUMERIC;
        }
        if (isset(static::NUMERIC[$key])) {
            return static::NUMERIC[$key];
        }
        throw new \InvalidArgumentException('Arg key not supported, key[' . $key . '].');
    }

    /**
     * @param bool $default
     *      False: return all currencies.
     *      True: return first currency.
     *
     * @return string|array
     */
    public function currency(bool $default = false)
    {
        return !$default ? static::CURRENCY : static::CURRENCY[0];
    }
}
