<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017-2019 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Utils\Explorable;
use SimpleComplex\Utils\Dependency;
use SimpleComplex\Utils\Utils;
use SimpleComplex\Config\Interfaces\SectionedConfigInterface;
use SimpleComplex\Locale\Exception\TextIdentifierException;
use SimpleComplex\Locale\Exception\TextNotFoundException;

/**
 * Abstract locale.
 *
 * Locale and language codes
 * -------------------------
 * This class, and children, uses the same format for locale and language codes.
 * That is: lowercase hyphen-separated IETF; 'da-dk'.
 * For more versatile input format support, use the Locale factory class.
 *
 * @see Locale
 *
 * @dependency-injection-container-id locale
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

    // Extending class may override these properties.---------------------------

    /**
     * Delimiter between text identifier parts
     *
     * Used like 'section:key' or 'section:key:sub'.
     *
     * @var string
     */
    const TEXT_ID_DELIMITER = ':';

    /**
     * Text to display (return) when text not found.
     *
     * @see AbstractLocale::text()
     *
     * @var string
     */
    const TEXT_NOT_FOUND_TEXT = 'Locale text not found: %identifier';

    /**
     * Class name of \SimpleComplex\Locale\LocaleText or extending class.
     *
     * @see AbstractLocale::__construct()
     *
     * @var string
     */
    const CLASS_LOCALE_TEXT = LocaleText::class;


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
     *      Format: 'da-dk'.
     *      Empty: uses config localeToLanguage[locale].
     */
    public function __construct(SectionedConfigInterface $config, string $language = '')
    {
        $this->config = $config;
        if (!$language) {
            $language = $config->get(static::CONFIG_SECTION, 'localeToLanguage')[$this->locale];
        }
        $this->language = $language;
        $class_locale_text = static::CLASS_LOCALE_TEXT;
        $paths = $config->get(static::CONFIG_SECTION, 'localeTextPaths', []);
        // localeTextPaths are relative to vendor dir, unless absolute.
        $vendor_dir = Utils::getInstance()->vendorDir();
        foreach ($paths as &$path) {
            if ($path{0} !== '/' && strpos($path, $vendor_dir . '/') !== 0) {
                $path = $vendor_dir . '/' . $path;
            }
        }
        unset($path);
        $this->text = new $class_locale_text($language, $paths);
    }

    /**
     * Get localized text.
     *
     * Arg default is only used if an argument was passed; otherwise error
     * or text-not-found text.
     *
     * Uses global config var lib_simplecomplex_locale:localeTextErrNotFound.
     *
     * @param string $identifier
     *      'section:key' or 'section:key:sub'.
     * @param array $replacers
     *      List of placeholders and values.
     *      Placeholder 'name' will be used on text content '... %name ...'.
     * @param string $default
     *      Only used if an argument passed.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     *      Empty arg identifier, or too many/few section-key-bucket delimiters.
     * @throws TextNotFoundException
     *      Unless suppressed by falsy setting
     *      lib_simplecomplex_locale:localeTextErrNotFound.
     * @throws TextIdentifierException
     *      If arg identifier only points at section:key, but that value is
     *      array; indicating missing trailing :sub in identifier.
     *      Or the opposite: arg identifier points at section:key:sub, but
     *      section:key value isn't array.
     */
    public function text(string $identifier, array $replacers = [], string $default = '')
    {
        if (!$identifier) {
            throw new \InvalidArgumentException('Arg identifier cannot be empty.');
        }
        $key_path = explode(static::TEXT_ID_DELIMITER, $identifier);
        $n_keys = count($key_path);
        if ($n_keys < 2) {
            throw new \InvalidArgumentException(
                'Arg identifier must contain at least one section-key-sub delimiter[' . static::TEXT_ID_DELIMITER . '].'
            );
        } elseif ($n_keys > 3) {
            throw new \InvalidArgumentException(
                'Arg identifier cannot more than two section-key-bucket delimiters[' . static::TEXT_ID_DELIMITER . '].'
            );
        }

        $text = $this->text->get($key_path[0], $key_path[1], false);
        $use_default = false;
        if ($text === false) {
            if (func_num_args() > 2) {
                // Use arg default if argument actually passed.
                $use_default = true;
                $text = $default;
            }
            else {
                // Err if missing text should count as an error.
                if ($this->config->get(static::CONFIG_SECTION, 'localeTextErrNotFound', true)) {
                    throw new TextNotFoundException(
                        'Locale text not found, identifier[' . $identifier . '].'
                    );
                }
                $container = Dependency::container();
                if ($container->has('logger')) {
                    $container->get('logger')->warning('Locale text not found, identifier[{identifier}].', [
                        'identifier' => $identifier,
                    ]);
                }
                return str_replace('%identifier', $identifier, static::TEXT_NOT_FOUND_TEXT);
            }
        }
        if (!$use_default) {
            $is_array = is_array($text);
            if ($n_keys == 2) {
                // Must be string.
                if ($is_array) {
                    throw new TextIdentifierException(
                        'Locale text identifier misses sub item part, identifier[' . $identifier . '] is a list.'
                    );
                }
            } else {
                // Must be array.
                if (!$is_array) {
                    throw new TextIdentifierException(
                        'Locale text identifier has surplus sub item part, section+key['
                        . $key_path[0] . ':' . $key_path[1] . '] is string not list.'
                    );
                }
                if (!isset($text[$key_path[2]])) {
                    if (func_num_args() > 2) {
                        // Use arg default if argument actually passed.
                        $text = $default;
                    }
                    else {
                        // Err if missing text should count as an error.
                        if ($this->config->get(static::CONFIG_SECTION, 'localeTextErrNotFound', true)) {
                            throw new TextNotFoundException(
                                'Locale text not found, identifier[' . $identifier . '].'
                            );
                        }
                        $container = Dependency::container();
                        if ($container->has('logger')) {
                            $container->get('logger')->warning('Locale text not found, identifier[{identifier}].', [
                                'identifier' => $identifier,
                            ]);
                        }
                        return str_replace('%identifier', $identifier, static::TEXT_NOT_FOUND_TEXT);
                    }
                } else {
                    $text = $text[$key_path[2]];
                }
            }
        }

        if ($replacers) {
            foreach ($replacers as $placeholder => $v) {
                $plchldr = '' . $placeholder;
                switch ($plchldr) {
                    case '':
                    case '%':
                        break;
                    default:
                        // Allow that placeholder key is % prefixed.
                        $text = str_replace(($plchldr{0} == '%' ? '' : '%') . $plchldr, $v, $text);
                }
            }
        }

        return $text;
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
