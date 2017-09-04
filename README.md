## Locale ##

- [Installation](#installation)
- [Requirements](#requirements)

### CLI commands ###

```bash
# List all locale-text commands and their help.
php cli.phpsh locale-text -h
# One command's help.
php cli.phpsh locale-text-xxx -h

# Display/get value of a locale-text item.
php cli.phpsh locale-text-get language section key

# Set a locale-text item.
php cli.phpsh locale-text-set language section key value

# Delete a locale-text item.
php cli.phpsh locale-text-delete language section key

# Refresh a locale-text store from .locale-text.[language].ini file sources.
php cli.phpsh locale-text-refresh language

# Export a locale-text store as JSON to a file.
php cli.phpsh locale-text-export language target-path-and-file
```

### Installation ###

Copy the 'global' config .ini file (see [SimpleComplex Config](https://github.com/simplecomplex/php-config))  
```[locale package dir]/```**```config-ini/locale.global.ini```**  
and place it in _config_'s 'base' or 'override' path.

Follow the instructions within in that file,  
and modify it to suit the current system's structure and features.

### Requirements ###

- PHP >=7.0
- [SimpleComplex Config](https://github.com/simplecomplex/php-config)
- [SimpleComplex Utils](https://github.com/simplecomplex/php-utils)
