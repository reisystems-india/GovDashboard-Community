
Must have the igbinary extension installed.
http://pecl.php.net/package/igbinary

To use this, add the following to settings.php file.

// the path to the core cache file
include_once(DRUPAL_ROOT.'/includes/cache.inc');
// the path to the igbinary cache file
include_once(DRUPAL_ROOT.'/sites/all/modules/custom/igbinarycache/igbinarycache.php');
// make MemCacheDrupal the default cache class
$conf['cache_default_class'] = 'IgbinaryDatabaseCache';