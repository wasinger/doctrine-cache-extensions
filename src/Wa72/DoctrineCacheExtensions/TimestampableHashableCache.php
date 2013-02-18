<?php
namespace Wa72\DoctrineCacheExtensions;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;

class TimestampableHashableCache implements Cache
{
    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $provider;

    function __construct(CacheProvider $provider) {
        $this->provider = $provider;
    }
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    function fetch($id)
    {
        return $this->provider->fetch($id);
    }

    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    function contains($id)
    {
        return $this->provider->contains($id);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache entry/data.
     * @param int $lifeTime The lifetime. If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    function save($id, $data, $lifeTime = 0)
    {
        $ret = $this->provider->save($id, $data, $lifeTime);
        if ($ret && !method_exists($this->provider, 'getTimestamp')) {
            $tsid = $id . '_timestamp';
            $this->provider->save($tsid, time());
        }
        if ($ret && !method_exists($this->provider, 'getHash')) {
            $hashid = $id . '_md5hash';
            $this->provider->save($hashid, md5($data));
        }
        return $ret;
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    function delete($id)
    {
        if (!method_exists($this->provider, 'getTimestamp')) {
            $this->provider->delete($id . '_timestamp');
        }
        if (!method_exists($this->provider, 'getHash')) {
            $this->provider->delete($id . '_md5hash');
        }
        return $this->provider->delete($id);
    }

    /**
     * @inheritdoc
     */
    function getStats()
    {
        return $this->provider->getStats();
    }

    /**
     * Get the timestamp of when the cache entry was saved
     *
     * @param string $id cache entry id
     * @return int UNIX timestamp of the cache entry
     */
    public function getTimestamp($id) {
        if (method_exists($this->provider, 'getTimestamp')) {
            return (int) $this->provider->getTimestamp($id);
        } else {
            return (int) $this->provider->fetch($id . '_timestamp');
        }
    }

    /**
     * Check whether cache entry is older than given timestamp
     *
     * @param string $id
     * @param int $timestamp UNIX timestamp for comparison
     * @return bool
     */
    public function isOlder($id, $timestamp) {
        return $this->getTimestamp($id) < $timestamp;
    }

    /**
     * Check whether cache entry is newer than given timestamp
     *
     * @param string $id
     * @param int $timestamp UNIX timestamp for comparison
     * @return bool
     */
    public function isNewer($id, $timestamp) {
        return $this->getTimestamp($id) > $timestamp;
    }

    public function flushAll() {
        return $this->provider->flushAll();
    }

    /**
     * Return MD5 hash of cached content
     *
     * @param $id
     * @return string
     */
    public function getHash($id) {
        if (method_exists($this->provider, 'getHash')) {
            return $this->provider->getHash($id);
        } else {
            return $this->provider->fetch($id . '_md5hash');
        }
    }


}
