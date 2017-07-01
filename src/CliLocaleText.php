<?php
/**
 * SimpleComplex PHP Locale
 * @link      https://github.com/simplecomplex/php-locale
 * @copyright Copyright (c) 2017 Jacob Friis Mathiasen
 * @license   https://github.com/simplecomplex/php-locale/blob/master/LICENSE (MIT License)
 */
declare(strict_types=1);

namespace SimpleComplex\Locale;

use SimpleComplex\Utils\CliCommandInterface;
use SimpleComplex\Utils\CliEnvironment;
use SimpleComplex\Utils\CliCommand;
use SimpleComplex\Utils\Dependency;
use SimpleComplex\Config\Config;
use SimpleComplex\Config\ConfigKey;

/**
 * CLI only.
 *
 * Expose/execute locale-text commands.
 *
 * @see simplecomplex_locale_text_cli()
 *
 * @see IniSectionedFlatConfig::get()
 * @see IniSectionedFlatConfig::set()
 * @see IniSectionedFlatConfig::delete()
 * @see IniConfigBase::refresh()
 *
 * @code
 * # CLI
 * cd vendor/simplecomplex/locale/src/cli
 * php cli.phpsh locale-text -h
 * @endcode
 *
 * @package SimpleComplex\Locale
 */
class CliLocaleText implements CliCommandInterface
{
    /**
     * @var string
     */
    const COMMAND_PROVIDER_ALIAS = 'locale-text';

    /**
     * @var string
     */
    const CLASS_CONFIG = Config::class;

    /**
     * @var string
     */
    const CLASS_LOCALE_TEXT = LocaleText::class;

    /**
     * @var string
     */
    const CLASS_INSPECT = '\\SimpleComplex\\Inspect\\Inspect';

    /**
     * Registers LocaleText CliCommands at CliEnvironment.
     *
     * @throws \LogicException
     *      If executed in non-CLI mode.
     */
    public function __construct()
    {
        if (!CliEnvironment::cli()) {
            throw new \LogicException('Cli mode only.');
        }

        $this->environment = CliEnvironment::getInstance();
        // Declare supported commands.
        $this->environment->registerCommands(
            new CliCommand(
                $this,
                static::COMMAND_PROVIDER_ALIAS . '-get',
                'Get a locale-text item.',
                [
                    'language' => 'Locale, like da-dk.',
                    'section' => 'Text section.',
                    'key' => 'Text item key.',
                ],
                [
                    'print' => 'Print to console, don\'t return value.',
                    'inspect' => 'Print Inspect\'ed value instead of JSON-encoded.',
                ],
                [
                    'p' => 'print',
                    'i' => 'inspect',
                ]
            ),
            new CliCommand(
                $this,
                static::COMMAND_PROVIDER_ALIAS . '-set',
                'Set a locale-text item.',
                [
                    'language' => 'Locale, like da-dk.',
                    'section' => 'Text section.',
                    'key' => 'Text item key.',
                    'value' => 'Value to set, please enclose in single quotes.',
                ],
                [],
                []
            ),
            new CliCommand(
                $this,
                static::COMMAND_PROVIDER_ALIAS . '-delete',
                'Delete a locale-text item.',
                [
                    'language' => 'Locale, like da-dk.',
                    'section' => 'Text section.',
                    'key' => 'Text item key.',
                ],
                [],
                []
            ),
            new CliCommand(
                $this,
                static::COMMAND_PROVIDER_ALIAS . '-refresh',
                'Flushes the locale-text\'s cache store,'
                . ' and loads fresh texts from all .locale-text.[language].ini files in the paths defined in'
                . "\n" . 'config global lib_simplecomplex_locale localeTextPaths.'
                . "\n" . 'NB: All items that have been set, overwritten or deleted since last refresh'
                . ' will be gone or restored to .ini-files\' original state.',
                [
                    'language' => 'Locale, like da-dk.',
                ],
                [],
                []
            )
        );
    }

    /**
     * @var CliCommand
     */
    protected $command;

    /**
     * @var CliEnvironment
     */
    protected $environment;

    /**
     * @return mixed
     *      Exits if option 'print'.
     */
    protected function cmdGet()
    {
        /**
         * @see simplecomplex_locale_text_cli()
         */
        $container = Dependency::container();
        // Validate input. ---------------------------------------------
        $language = '';
        if (empty($this->command->arguments['language'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['language']) ?
                'Missing \'language\' argument.' : 'Empty \'language\' argument.';
        } else {
            $language = $this->command->arguments['language'];
            if (!LocaleCode::validate($language)) {
                $this->command->inputErrors[] = 'Invalid \'language\' argument.';
            }
        }
        $section = '';
        if (empty($this->command->arguments['section'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['section']) ?
                'Missing \'section\' argument.' : 'Empty \'section\' argument.';
        } else {
            $section = $this->command->arguments['section'];
            if (!ConfigKey::validate($section)) {
                $this->command->inputErrors[] = 'Invalid \'section\' argument.';
            }
        }
        $key = '';
        if (empty($this->command->arguments['key'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['key']) ?
                'Missing \'key\' argument.' : 'Empty \'key\' argument.';
        } else {
            $key = $this->command->arguments['key'];
            if (!ConfigKey::validate($key)) {
                $this->command->inputErrors[] = 'Invalid \'key\' argument.';
            }
        }

        $print = !empty($this->command->options['print']);
        $inspect = !empty($this->command->options['inspect']);

        if ($this->command->inputErrors) {
            foreach ($this->command->inputErrors as $msg) {
                $this->environment->echoMessage(
                    $this->environment->format($msg, 'hangingIndent'),
                    'notice'
                );
            }
            // This command's help text.
            $this->environment->echoMessage("\n" . $this->command);
            exit;
        }
        // Display command and the arg values used.---------------------
        if ($print || $inspect) {
            $this->environment->echoMessage(
                $this->environment->format(
                    $this->environment->format($this->command->name, 'emphasize')
                    . "\n" . 'language: ' . $language
                    . "\n" . 'section: ' . $section
                    . "\n" . 'key: ' . $key
                    . (!$this->command->options ? '' : ("\n--" . join(' --', array_keys($this->command->options)))),
                    'hangingIndent'
                )
            );
        }
        // Check if the command is doable.------------------------------
        if ($container->has('config')) {
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = $container->get('config');
        } else {
            $config_class = static::CLASS_CONFIG;
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = new $config_class('global');
        }
        $locale_text_paths = $config->get('lib_simplecomplex_locale', 'localeTextPaths');
        if (!$locale_text_paths || !is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config misses item section[lib_simplecomplex_locale] key[localeTextPaths].',
                'warning'
            );
            exit;
        }
        if (!is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config item section[lib_simplecomplex_locale] key[localeTextPaths] is not array.',
                'error'
            );
            exit;
        }
        // Do it.
        $locale_text_class = static::CLASS_LOCALE_TEXT;
        /** @var LocaleText $locale_text */
        $locale_text = new $locale_text_class($language, $locale_text_paths);
        if (!$locale_text->has($section, $key)) {
            $this->environment->echoMessage('');
            $this->environment->echoMessage(
                'Locale-text language[' . $language . '] section[' . $section . '] key[' . $key . '] doesn\'t exist.',
                'notice'
            );
            exit;
        }
        $value = $locale_text->get($section, $key);
        if (!$print && !$inspect) {
            return $value;
        }
        $this->environment->echoMessage('');
        if ($inspect) {
            $inspector = null;
            if ($container->has('inspector')) {
                $inspector = $container->get('inspector');
            } elseif (class_exists(static::CLASS_INSPECT)) {
                $class_inspect = static::CLASS_INSPECT;
                $inspector = new $class_inspect($container->has('config') ? $container->get('config') : null);
            }
            if ($inspector) {
                $this->environment->echoMessage($inspector->inspect($value)->toString(true));
                exit;
            }
        }
        $this->environment->echoMessage(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        exit;
    }

    /**
     * @return void
     *      Exits.
     */
    protected function cmdSet() /*: void*/
    {
        /**
         * @see simplecomplex_locale_text_cli()
         */
        $container = Dependency::container();
        // Validate input. ---------------------------------------------
        $language = '';
        if (empty($this->command->arguments['language'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['language']) ? 'Missing \'language\' argument.' :
                'Empty \'language\' argument.';
        } else {
            $language = $this->command->arguments['language'];
            if (!LocaleCode::validate($language)) {
                $this->command->inputErrors[] = 'Invalid \'language\' argument.';
            }
        }
        $section = '';
        if (empty($this->command->arguments['section'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['section']) ?
                'Missing \'section\' argument.' : 'Empty \'section\' argument.';
        } else {
            $section = $this->command->arguments['section'];
            if (!ConfigKey::validate($section)) {
                $this->command->inputErrors[] = 'Invalid \'section\' argument.';
            }
        }
        $key = '';
        if (empty($this->command->arguments['key'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['key']) ?
                'Missing \'key\' argument.' : 'Empty \'key\' argument.';
        } else {
            $key = $this->command->arguments['key'];
            if (!ConfigKey::validate($key)) {
                $this->command->inputErrors[] = 'Invalid \'key\' argument.';
            }
        }
        $value = $this->command->arguments['value'];

        if ($this->command->inputErrors) {
            foreach ($this->command->inputErrors as $msg) {
                $this->environment->echoMessage(
                    $this->environment->format($msg, 'hangingIndent'),
                    'notice'
                );
            }
            // This command's help text.
            $this->environment->echoMessage("\n" . $this->command);
            exit;
        }
        // Check if the command is doable.------------------------------
        if ($container->has('config')) {
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = $container->get('config');
        } else {
            $config_class = static::CLASS_CONFIG;
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = new $config_class('global');
        }
        $locale_text_paths = $config->get('lib_simplecomplex_locale', 'localeTextPaths');
        if (!$locale_text_paths || !is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config misses item section[lib_simplecomplex_locale] key[localeTextPaths].',
                'warning'
            );
            exit;
        }
        if (!is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config item section[lib_simplecomplex_locale] key[localeTextPaths] is not array.',
                'error'
            );
            exit;
        }
        $locale_text_class = static::CLASS_LOCALE_TEXT;
        /** @var LocaleText $locale_text */
        $locale_text = new $locale_text_class($language, $locale_text_paths);
        // Display command and the arg values used.---------------------
        if (!$this->command->preConfirmed) {
            $this->environment->echoMessage(
                $this->environment->format(
                    $this->environment->format($this->command->name, 'emphasize')
                    . "\n" . 'language: ' . $language
                    . "\n" . 'section: ' . $section
                    . "\n" . 'key: ' . $key
                    . "\n" . 'value: ' . addcslashes($value, "\0..\37")
                    . (!$this->command->options ? '' : ("\n--" . join(' --', array_keys($this->command->options)))),
                    'hangingIndent'
                )
            );
        }
        // Request confirmation, unless user used the --yes/-y option.
        if (
            !$this->command->preConfirmed
            && !$this->environment->confirm(
                'Set that locale-text item? Type \'yes\' or \'y\' to continue:',
                ['yes', 'y'],
                '',
                'Aborted setting locale-text item.'
            )
        ) {
            exit;
        }
        // Do it.
        if (!$locale_text->set($section, $key, $value)) {
            $this->environment->echoMessage(
                'Failed to set locale-text item language[' . $language
                . '] section[' . $section . '] key[' . $key . '] value[' . addcslashes($value, "\0..\37") . '].',
                'error'
            );
        } else {
            $this->environment->echoMessage(
                'Set locale-text item language[' . $language
                . '] section[' . $section . '] key[' . $key . '] value[' . addcslashes($value, "\0..\37") . '].',
                'success'
            );
        }
        exit;
    }

    /**
     * @return void
     *      Exits.
     */
    protected function cmdDelete() /*: void*/
    {
        /**
         * @see simplecomplex_locale_text_cli()
         */
        $container = Dependency::container();
        // Validate input. ---------------------------------------------
        $language = '';
        if (empty($this->command->arguments['language'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['language']) ? 'Missing \'language\' argument.' :
                'Empty \'language\' argument.';
        } else {
            $language = $this->command->arguments['language'];
            if (!LocaleCode::validate($language)) {
                $this->command->inputErrors[] = 'Invalid \'language\' argument.';
            }
        }
        $section = '';
        if (empty($this->command->arguments['section'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['section']) ?
                'Missing \'section\' argument.' : 'Empty \'section\' argument.';
        } else {
            $section = $this->command->arguments['section'];
            if (!ConfigKey::validate($section)) {
                $this->command->inputErrors[] = 'Invalid \'section\' argument.';
            }
        }
        $key = '';
        if (empty($this->command->arguments['key'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['key']) ?
                'Missing \'key\' argument.' : 'Empty \'key\' argument.';
        } else {
            $key = $this->command->arguments['key'];
            if (!ConfigKey::validate($key)) {
                $this->command->inputErrors[] = 'Invalid \'key\' argument.';
            }
        }

        if ($this->command->inputErrors) {
            foreach ($this->command->inputErrors as $msg) {
                $this->environment->echoMessage(
                    $this->environment->format($msg, 'hangingIndent'),
                    'notice'
                );
            }
            // This command's help text.
            $this->environment->echoMessage("\n" . $this->command);
            exit;
        }
        // Check if the command is doable.------------------------------
        if ($container->has('config')) {
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = $container->get('config');
        } else {
            $config_class = static::CLASS_CONFIG;
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = new $config_class('global');
        }
        $locale_text_paths = $config->get('lib_simplecomplex_locale', 'localeTextPaths');
        if (!$locale_text_paths || !is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config misses item section[lib_simplecomplex_locale] key[localeTextPaths].',
                'warning'
            );
            exit;
        }
        if (!is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config item section[lib_simplecomplex_locale] key[localeTextPaths] is not array.',
                'error'
            );
            exit;
        }
        $locale_text_class = static::CLASS_LOCALE_TEXT;
        /** @var LocaleText $locale_text */
        $locale_text = new $locale_text_class($language, $locale_text_paths);
        // Display command and the arg values used.---------------------
        if (!$this->command->preConfirmed) {
            $this->environment->echoMessage(
                $this->environment->format(
                    $this->environment->format($this->command->name, 'emphasize')
                    . "\n" . 'language: ' . $language
                    . "\n" . 'section: ' . $section
                    . "\n" . 'key: ' . $key,
                    'hangingIndent'
                )
            );
        }
        // Request confirmation, unless user used the --yes/-y option.
        if (
            !$this->command->preConfirmed
            && !$this->environment->confirm(
                'Delete that locale-text item? Type \'yes\' or \'y\' to continue:',
                ['yes', 'y'],
                '',
                'Aborted deleting locale-text item.'
            )
        ) {
            exit;
        }
        // Do it.
        if (!$locale_text->delete($section, $key)) {
            $this->environment->echoMessage(
                'Failed to delete locale-text item language[' . $language . '] section[' . $section . '] key[' . $key . '].',
                'error'
            );
        } else {
            $this->environment->echoMessage(
                'Deleted locale-text item language[' . $language . '] section[' . $section . '] key[' . $key . '].',
                'success'
            );
        }
        exit;
    }

    /**
     * Ignores pre-confirmation --yes/-y option.
     *
     * @return void
     *      Exits.
     */
    protected function cmdRefresh() /*: void*/
    {
        /**
         * @see simplecomplex_locale_text_cli()
         */
        $container = Dependency::container();
        // Validate input. ---------------------------------------------
        $language = '';
        if (empty($this->command->arguments['language'])) {
            $this->command->inputErrors[] = !isset($this->command->arguments['language']) ? 'Missing \'language\' argument.' :
                'Empty \'language\' argument.';
        } else {
            $language = $this->command->arguments['language'];
            if (!LocaleCode::validate($language)) {
                $this->command->inputErrors[] = 'Invalid \'language\' argument.';
            }
        }
        // Pre-confirmation --yes/-y ignored for this command.
        if ($this->command->preConfirmed) {
            $this->command->inputErrors[] = 'Pre-confirmation \'yes\'/-y option not supported for this command.';
        }
        if ($this->command->inputErrors) {
            foreach ($this->command->inputErrors as $msg) {
                $this->environment->echoMessage(
                    $this->environment->format($msg, 'hangingIndent'),
                    'notice'
                );
            }
            // This command's help text.
            $this->environment->echoMessage("\n" . $this->command);
            exit;
        }
        // Check if the command is doable.------------------------------
        if ($container->has('config')) {
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = $container->get('config');
        } else {
            $config_class = static::CLASS_CONFIG;
            /** @var \SimpleComplex\Config\IniSectionedConfig $config */
            $config = new $config_class('global');
        }
        $locale_text_paths = $config->get('lib_simplecomplex_locale', 'localeTextPaths');
        if (!$locale_text_paths || !is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config misses item section[lib_simplecomplex_locale] key[localeTextPaths].',
                'warning'
            );
            exit;
        }
        if (!is_array($locale_text_paths)) {
            $this->environment->echoMessage(
                'Global config item section[lib_simplecomplex_locale] key[localeTextPaths] is not array.',
                'error'
            );
            exit;
        }
        $locale_text_class = static::CLASS_LOCALE_TEXT;
        /** @var LocaleText $locale_text */
        $locale_text = new $locale_text_class($language, $locale_text_paths);
        // Display command and the arg values used.---------------------
        $this->environment->echoMessage(
            $this->environment->format(
                $this->environment->format($this->command->name, 'emphasize')
                . "\n" . 'language: ' . $language,
                'hangingIndent'
            )
        );
        // Request confirmation, ignore --yes/-y pre-confirmation option.
        if (
            !$this->environment->confirm(
                'Refresh that locale-text language? Type \'yes\' to continue:',
                ['yes'],
                '',
                'Aborted refreshing locale-text language.'
            )
        ) {
            exit;
        }
        // Do it.
        if (!$locale_text->refresh()) {
            $this->environment->echoMessage('Failed to refresh locale-text language[' . $language . '].', 'error');
        } else {
            $this->environment->echoMessage('Refreshed locale-text language[' . $language . '].', 'success');
        }
        exit;
    }


    // CliCommandInterface.-----------------------------------------------------

    /**
     * @return string
     */
    public function commandProviderAlias(): string
    {
        return static::COMMAND_PROVIDER_ALIAS;
    }

    /**
     * @param CliCommand $command
     *
     * @return mixed
     *      Return value of the executed command, if any.
     *      May well exit.
     *
     * @throws \LogicException
     *      If the command mapped by CliEnvironment
     *      isn't this provider's command.
     */
    public function executeCommand(CliCommand $command)
    {
        $this->command = $command;
        $this->environment = CliEnvironment::getInstance();

        switch ($command->name) {
            case static::COMMAND_PROVIDER_ALIAS . '-get':
                return $this->cmdGet();
            case static::COMMAND_PROVIDER_ALIAS . '-set':
                $this->cmdSet();
                exit;
            case static::COMMAND_PROVIDER_ALIAS . '-delete':
                $this->cmdDelete();
                exit;
            case static::COMMAND_PROVIDER_ALIAS . '-refresh':
                $this->cmdRefresh();
                exit;
            default:
                throw new \LogicException(
                    'Command named[' . $command->name . '] is not provided by class[' . get_class($this) . '].'
                );
        }
    }
}