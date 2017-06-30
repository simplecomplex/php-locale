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
 * Locale en-us.
 *
 * @package SimpleComplex\Locale
 */
class LocaleEnUs extends AbstractLocale
{
    /**
     * @var string
     */
    protected $locale = 'en-us';

    /**
     * @var array
     */
    const TEMPORAL = [
        'datePartSequence' => 'mdY',
        'dateShort' => 'm/d/Y',
        'dateLong' => 'm/d/Y H:i',
        'timeShort' => 'H:i',
        'timeLong' => 'H:i:s',
        'weekdayFirst' => 'sunday',
    ];

    /**
     * @var array
     */
    const NUMERIC = [
        'decimalMark' => '.',
        'thousandSeparator' => ' ',
        'noFractionMark' => '/-',
    ];

    /**
     * @var array
     */
    const CURRENCY = [
        [
            'title' => 'US Dollar',
            'abbreviation' => 'USD',
            'sign' => '$',
        ]
    ];
}
