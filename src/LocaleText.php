<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Config\IniSectionedFlatConfig;

/**
 * ???
 *
 * @todo: say something
 *
 * @package SimpleComplex\Locale
 */
class LocaleText extends IniSectionedFlatConfig
{
    /**
     * Expects and will handle illegal .ini key names.
     *
     * @see \SimpleComplex\Config\IniConfigBase::$escapeSourceKeys
     *
     * @var bool
     */
    protected $escapeSourceKeys = true;

    /**
     * Overridden with empty to make constructor argument paths rule.
     *
     * @var string[]
     */
    const PATH_DEFAULTS = [];

    /**
     * Overridden with empty to make constructor argument paths rule.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * LocaleText constructor.
     *
     * @param string $language
     *      Lisp-cased; 'da-dk'.
     * @param string[] $paths
     */
    public function __construct(string $language, array $paths)
    {
        if (!$language) {
            throw new \InvalidArgumentException('Arg language cannot be empty.');
        }

        $this->fileExtensions = [
            'locale-text.' . $language . '.ini',
        ];

        parent::__construct('locale-text_' . $language, $paths);
    }
}
