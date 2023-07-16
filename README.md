# Information

Интеграция текста лицензии в статью.

## Install

1. Загрузите папки и файлы в директорию `extensions/MW_EXT_License`.
2. В самый низ файла `LocalSettings.php` добавьте строку:

```php
wfLoadExtension( 'MW_EXT_License' );
```

## Syntax

```html
{{#license: [TYPE]}}
```
