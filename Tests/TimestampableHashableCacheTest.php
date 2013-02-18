<?php
namespace Wa72\DoctrineCacheExtensions\Tests;

use Wa72\DoctrineCacheExtensions\TimestampableHashableCache;


class TimestampableHashableCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TimestampableHashableCache
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new TimestampableHashableCache(new \Doctrine\Common\Cache\ArrayCache());
    }

    protected function tearDown() {
        $this->object->flushAll();
    }

    /**
     * @covers Wa72\Tueliveserver\TimestampableHashableCache::getTimestamp
     */
    public function testGetTimestamp()
    {
        $ts = time();
        $this->object->save('test1', 'blabla');

        // because we get the reference timestamp before actually saving the object,
        // timestamps may differ +/- one second
        $this->assertGreaterThanOrEqual($ts - 1, $this->object->getTimestamp('test1'));
        $this->assertLessThanOrEqual($ts + 1, $this->object->getTimestamp('test1'));
    }

    /**
     * @covers Wa72\Tueliveserver\TimestampableHashableCache::isOlder
     */
    public function testIsOlder()
    {
        $this->object->save('test2', 'blabla');
        $this->assertTrue($this->object->isOlder('test2', time() + 1));
    }

    /**
     * @covers Wa72\Tueliveserver\TimestampableHashableCache::isNewer
     */
    public function testIsNewer()
    {
        $this->object->save('test3', 'blabla');
        $this->assertTrue($this->object->isOlder('test2', time() - 2));
    }

    /**
     * @covers Wa72\Tueliveserver\TimestampableHashableCache::getHash
     */
    public function testGetHash() {
        $this->object->save('test4', 'asdadfa');
        $hash = md5('asdadfa');
        $this->assertEquals($hash, $this->object->getHash('test4'));
    }
}
