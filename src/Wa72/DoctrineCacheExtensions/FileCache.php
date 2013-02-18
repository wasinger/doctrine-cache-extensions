<?php
namespace Wa72\DoctrineCacheExtensions;

use Doctrine\Common\Cache\CacheProvider;

class FileCache extends CacheProvider
{
    private $cachedir;

    /**
     * {@inheritdoc}
     */
    function doFetch($id)
    {
        if ($this->doContains($id)) {
            return file_get_contents($this->filename($id));
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    function doContains($id)
    {
        return file_exists($this->filename($id)) && $this->isValid($id);
    }

    /**
     * {@inheritdoc}
     */
    function doSave($id, $data, $lifeTime = false)
    {
        $r = file_put_contents($this->filename($id), $data);
        if ($r >= 0) $r = true;
        if ($lifeTime > 0) {
            $this->set_expires($id, $lifeTime);
        }
        return $r;
    }

    /**
     * {@inheritdoc}
     */
    function doDelete($id)
    {
        return unlink($this->filename($id));
    }

    /**
     * {@inheritdoc}
     */
    function doGetStats()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    function doFlush()
    {
        foreach (scandir($this->cachedir) as $item) {
            if ($item == '.' || $item == '..') continue;
            unlink($this->cachedir . DIRECTORY_SEPARATOR . $item);
        }
        return rmdir($this->cachedir);
    }

    function __construct($cachedir)
    {
        if (substr($cachedir, -1) == '/') $cachedir = substr($cachedir, 0, -1);
        if (!is_dir($cachedir)) {
            $r = mkdir($cachedir, 0777, true);
            if (!$r || !is_dir($cachedir)) {
                throw new \InvalidArgumentException('FileCache: Invalid cache dir ' . $cachedir);
            }
        }
        if (!is_writable($cachedir)) {
            throw new \InvalidArgumentException('FileCache: cache dir ' . $cachedir . ' not writeable!');
        }
        $this->cachedir = $cachedir;
    }

    /**
     * @param string $id Raw item id, not namespaced!
     * @return int
     */
    function getTimestamp($id) {
        return $this->doGetTimestamp($id);
    }

    /**
     * @param string $id Namespaced id as from $this->getNamespacedId($id)
     * @return int
     */
    function doGetTimestamp($id) {
        return filemtime($this->filename($this->getNamespacedId($id)));
    }

    /**
     * Prefix the passed id with the configured namespace value
     * copied here from the base class because it is private there
     *
     * @param string $id  The id to namespace
     * @return string $id The namespaced id
     */
    private function getNamespacedId($id)
    {
        $namespaceCacheKey = sprintf(self::DOCTRINE_NAMESPACE_CACHEKEY, $this->getNamespace());
        $namespaceVersion  = ($this->doContains($namespaceCacheKey)) ? $this->doFetch($namespaceCacheKey) : 1;

        return sprintf('%s[%s][%s]', $this->getNamespace(), $id, $namespaceVersion);
    }



    protected function filename($id)
    {
        return $this->cachedir . '/' . $id;
    }

    /**
     * @param string $id
     * @param int $lifetime in seconds
     */
    protected function set_expires($id, $lifetime) {
        if ($lifetime > 0) file_put_contents($this->filename($id) . '_expires', time() + $lifetime);
    }

    protected function isValid($id) {
        if (file_exists($this->filename($id) . '_expires')) {
            $expires = intval(file_get_contents($this->filename($id) . '_expires'));
            if ($expires < time()) return false;
        }
        return true;
    }

}