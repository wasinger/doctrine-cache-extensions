<?php
namespace Wa72\DoctrineCacheExtensions\Tests;

use Wa72\DoctrineCacheExtensions\FileCache;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileCache
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new FileCache(sys_get_temp_dir() . '/test');
    }

    protected function tearDown()
    {
        $this->object->flushAll();
    }
    public function testFileCache()
    {
        $fc = $this->object;
        $data = 'Inhalt des ersten Caches';
        $fc->save('abc', $data);

        $this->assertTrue($fc->contains('abc'));
        $this->assertFalse($fc->contains('asdfasf'));
        $this->assertEquals($data, $fc->fetch('abc'));
        $fc->delete('abc');
        $this->assertFalse($fc->contains('abc'));

        $fc->save('def', $data, 1);
        $this->assertTrue($fc->contains('def'));
        $this->assertEquals($data, $fc->fetch('def'));
        sleep(2);
        $this->assertFalse($fc->contains('def'));
        $this->assertFalse($fc->fetch('def'));


    }
    /**
     * @covers Wa72\Tueliveserver\FileCache::getTimestamp
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
}

