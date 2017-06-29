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
 * @internal
 *
 * @package SimpleComplex\Locale
 */
class LocaleText extends IniSectionedFlatConfig
{

    /**
     * No default paths.
     */
    const PATH_DEFAULTS = [];

    /**
     * Allows any paths; any names and number of.
     *
     * Relative path is relative to document root.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * LocaleText constructor.
     *
     * @param string $language
     *      Lisp-cased; 'en' or 'en-gb'.
     * @param string[] $paths
     */
    public function __construct(string $language, array $paths)
    {
        if (!$language) {
            throw new \InvalidArgumentException('Arg language cannot be empty.');
        }

        parent::__construct('locale-text_' . $language, $paths);
    }
}
