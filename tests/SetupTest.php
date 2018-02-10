<?php
namespace Dframe\tests;
ini_set('session.use_cookies', 0);

use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') AND class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class SetupTest extends PHPUnit_Framework_TestCase {


	public function setUp(){
    //ToDo
	}

	public function testToDo(){
		
	}
}
