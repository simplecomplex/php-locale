<?php

namespace SimpleComplex\Locale\Exception;

/**
 * No such locale text found exception.
 *
 * To differentiate exceptions thrown in-package from exceptions
 * thrown out-package.
 *
 * Please do not use - throw - in code of another package/library.
 *
 * @package SimpleComplex\Locale
 */
class TextIdentifierException extends \LogicException
{
}
