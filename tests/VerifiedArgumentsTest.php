<?php

use PHPUnit\Framework\TestCase;
use WaughJ\VerifiedArguments\VerifiedArguments;

class VerifiedArgumentsTest extends TestCase
{
	public function testMissingArgument() : void
	{
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash );
		$this->assertEquals( $args->get( "expertise" ), null );
	}

	public function testRightArgument() : void
	{
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash );
		$this->assertEquals( $args->get( "name" ), "Jaimeson" );
	}

	public function testWrongArgumentType() : void
	{
		$expected_args =
		[
			"name" => [ "type" => "integer" ]
		];
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash, $expected_args );
		$this->assertEquals( $args->get( "name" ), null );
	}
}
