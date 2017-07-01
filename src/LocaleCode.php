<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

/**
 * Validate locale code.
 *
 * Format: 'da-dk'.
 *
 * @code
 * use SimpleComplex\Locale\LocaleCode;
 *
 * if (!LocaleCode::validate($code)) {
 *    throw new \InvalidArgumentException('Arg code is not valid, code[' . $code . '].');
 * }
 * @endcode
 *
 * @package SimpleComplex\Locale
 */
class LocaleCode
{
    /**
     * Checks that length and content is legal.
     *
     * Lowercase alpha, and third char must be hyphen.
     *
     * @param string $code
     *
     * @return bool
     */
    public static function validate(string $code) : bool
    {
        if (strlen($code) != 5) {
            return false;
        }
        return !!preg_match('/^[a-z]{2}\-[a-z]{2}$/', $code);
    }
}
