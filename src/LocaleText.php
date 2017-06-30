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

// @todo: make CliLocaleText class like CliConfig.

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
     * LocaleText constructor.
     *
     * @param string $language
     *      Lisp-cased; 'en-gb'.
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
