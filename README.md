DoctrineCacheExtensions
=======================

Contains two classes that are add-ons to Doctrine\Common\Cache:

- Wa72\DoctrineCacheExtensions\FileCache
- Wa72\DoctrineCacheExtensions\TimestampableHashableCache  

FileCache is a cache provider for Doctrine\Common\Cache that stores cached elements as files in a given cache 
directory. As of today, there is a native FileCache provider class in the Doctrine Cache package itself, 
but it didn't exist yet when I needed it some months ago, so I wrote this class.
Now I'm still using my implementation because it has special support for the second class in this package, TimestampableHashableCache, because it is able to return the filemtime of the cache file.

TimestampableHashableCache is a class that implements Doctrine's Cache interface and provides some additional methods for getting the timestamp when the cache was saved and an md5 hash of the cached content. For doing the real work it needs another CacheProvider. This can be any of the Doctrine\Common\Cache\CacheProvider subclasses, such as ApcCache or the above mentioned FileCache.


Requirements
------------

-   PHP 5.3+
-   [Doctrine\Common\Cache](https://github.com/doctrine/cache)

Installation
------------

-   using [composer] (http://getcomposer.org): add "wa72/doctrine-cache-extensions": "dev-master" to the "require" section of your composer.json

-   using other PSR-0 compliant autoloader: clone this project to where your vendor libraries are 
    and point your autoloader to look for the "\Wa72\DoctrineCacheExtensions" namespace in the "src" 
    directory of this project

