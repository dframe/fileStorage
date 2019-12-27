<?php

namespace Dframe\FileStorage\Tests;

use Dframe\FileStorage\Storage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * Class FileTests
 *
 * @package Dframe\FileStorage\Tests
 */
class FileTest extends TestCase
{

    /** @var Storage */
    protected $Storage;

    public function setUp()
    {
        clearstatcache();
        $fs = new Local(__DIR__ . '/');
        $fs->deleteDir('files');
        $fs->createDir('files', new Config());
        $fs->write('file.txt', 'contents', new Config());
        $local = new Filesystem($fs);

        $driver = new FakeDriver();
        $config = [
            'pluginsDir' => __DIR__ . '/plugins',
            'adapters' => [
                'local' => $local,
            ]
        ];

        $this->Storage = new Storage($driver, $config);
    }

    public function testHas()
    {
        $this->assertTrue($this->Storage->getManager()->has('local://file.txt'));
    }

    public function testPut()
    {
        $put = $this->Storage->put('local', __DIR__ . '/file.txt', 'files/file.txt');
        $this->assertTrue($put['return']);
    }
}
