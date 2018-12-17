## Locale ##

### Localization, primarily of texts ###

Texts are referred by (free format) IDs.  
.ini-file based.

Superior to gettext (.po) in these ways:
- texts (translations) don't get _orphaned_ when you change the source text  
- editors/translators work in standard plaintext editors
- texts can be organised in groups and sub groups.
- no need for complex parsers and im-/export features, databases etc.
- miniscule resource footprint

### .ini-file example ###

```ini
[some-group]
meeting = reunión
some-sub-group[start] = ¡Hola!
some-sub-group[continue] = ¿Cómo estás?
some-sub-group[end] = Hasta luego...
```

### Dependency injection container ID: locale ###

Recommendation: access (and thus instantiate) locale via DI container ID 'locale'.  
See [SimpleComplex Utils](https://github.com/simplecomplex/php-utils) ``` Dependency ```.

### CLI commands ###

```bash
# List all locale-text commands and their help.
php cli.php locale-text -h
# One command's help.
php cli.php locale-text-xxx -h

# Display/get value of a locale-text item.
php cli.php locale-text-get language section key

# Set a locale-text item.
php cli.php locale-text-set language section key value

# Delete a locale-text item.
php cli.php locale-text-delete language section key

# Refresh a locale-text store from .locale-text.[language].ini file sources.
php cli.php locale-text-refresh language

# Export a locale-text store as JSON to a file.
php cli.php locale-text-export language target-path-and-file
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

##### Suggestions #####

- [SimpleComplex Inspect](https://github.com/simplecomplex/inspect)
