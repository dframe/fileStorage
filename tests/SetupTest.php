<?php
namespace Dframe\FileStorage\tests;
ini_set('session.use_cookies', 0);

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') and class_exists('\PHPUnit_Framework_TestCase')) {
	class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class SetupTest extends \PHPUnit\Framework\TestCase
{


	public function setUp()
	{
    //ToDo
	}

	public function testToDo()
	{

	}
}
