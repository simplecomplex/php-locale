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
 * Locale da-dk.
 *
 * @cache-store config.locale-text_da-dk
 *      Name of cache store used by this class (effectively).
 *
 * @package SimpleComplex\Locale
 */
class LocaleDaDk extends AbstractLocale
{
    /**
     * @var string
     */
    protected $locale = 'da-dk';

    /**
     * @var array
     */
    const TEMPORAL = [
        'datePartSequence' => 'dmY',
        'dateShort' => 'd-m-Y',
        'dateLong' => 'd-m-Y H:i',
        'timeShort' => 'H:i',
        'timeLong' => 'H:i:s',
        'weekdayFirst' => 'monday',
    ];

    /**
     * @var array
     */
    const NUMERIC = [
        'decimalMark' => ',',
        'thousandSeparator' => '.',
        'noFractionMark' => ',-',
    ];

    /**
     * @var array
     */
    const CURRENCY = [
        [
            'title' => 'Danske kroner',
            'abbreviation' => 'DKK',
            'sign' => 'kr',
        ],
        [
            'title' => 'Euro',
            'abbreviation' => 'EUR',
            'sign' => '€',
        ]
    ];
}
