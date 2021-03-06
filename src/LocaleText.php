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
 * Text localisation based on .ini files.
 *
 * Contrary to gettext (.po files) there's no translation involved.
 * Here, it's all about 'context' - implemented as 'section'+'key'.
 *
 * gettext is great, but translation sources tend to be a problem;
 * - oops, we've changed a source text (typically in English)
 * and now all it's translations are de-referenced/orphans...
 * In the end, text item identifiers (context, section+key) is the only thing
 * that works reliably.
 *
 * @cache-store config.locale-text_[xx-yy]
 *      Name of cache store used by this class (effectively).
 *
 * @see \SimpleComplex\Config\Interfaces\SectionedConfigInterface
 *
 * @package SimpleComplex\Locale
 */
class LocaleText extends IniSectionedFlatConfig
{
    /**
     * Allow long keys.
     *
     * @var bool
     */
    const CACHE_KEY_LONG = true;

    /**
     * Expects and will handle illegal .ini key names.
     *
     * @see \SimpleComplex\Config\IniConfigBase::$escapeSourceKeys
     *
     * @var bool
     */
    protected $escapeSourceKeys = true;

    /**
     * All values are strings; don't type null|true|false|N|N.N.
     *
     * @var bool
     */
    protected $parseTyped = false;

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
     * @var array
     */
    protected $fileExtensions;

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
