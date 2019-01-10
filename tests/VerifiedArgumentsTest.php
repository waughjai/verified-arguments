<?php

use PHPUnit\Framework\TestCase;
use WaughJ\VerifiedArguments\VerifiedArguments;

class VerifiedArgumentsTest extends TestCase
{
	public function testMissingArgument() : void
	{
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash );
		$this->assertNull( $args->get( "expertise" ) );
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
		$this->assertNull( $args->get( "name" ) );
	}

	public function testWrongArgumentTypeDefaultValue() : void
	{
		$expected_args =
		[
			"name" => [ "type" => "integer", "value" => "nada" ]
		];
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash, $expected_args );
		$this->assertEquals( $args->get( "name" ), "nada" );

		$snd_args = new VerifiedArguments( [ "name" => new DateTime( "10/23/2018" ) ], [ "name" => [ "type" => \DateTime::class, "value" => false ]]);
		$this->assertEquals( $snd_args->get( "name" ), new DateTime( "10/23/2018" ) );

		$trd_args = new VerifiedArguments( [ "name" => new DateTime( "10/23/2018" ) ], [ "name" => [ "type" => "object", "value" => false ]]);
		$this->assertEquals( $trd_args->get( "name" ), new DateTime( "10/23/2018" ) );
	}

	public function testGetList() : void
	{
		$hash = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $hash );
		$this->assertEquals( $hash, $args->getList() );
	}
}
