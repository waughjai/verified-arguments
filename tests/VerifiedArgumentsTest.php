<?php

use PHPUnit\Framework\TestCase;
use WaughJ\VerifiedArguments\VerifiedArguments;

class VerifiedArgumentsTest extends TestCase
{
	public function testCustomAndDefault() : void
	{
		$defaults =
		[
			"name" => [ "value" => "Anonymous" ],
			"age" => [ "value" => 0 ]
		];
		$newValues = [ "age" => 27 ];
		$args = new VerifiedArguments( $newValues, $defaults );
		$this->assertEquals( "Anonymous", $args->get( "name" ) ); // Falls back to default.
		$this->assertEquals( 27, $args->get( "age" ) ); // Retrieves new value.
	}

	public function testMissingArgument() : void
	{
		$defaults =
		[
			"name" => [ "value" => "Anonymous" ],
			"age" => [ "value" => 0 ]
		];
		$args = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $args );
		$this->assertNull( $args->get( "expertise" ) ); // Set in neither default nor new values, so retrieves nothing.
	}

	public function testMissingDefault() : void
	{
		$defaults = [ "age" => [ "value" => 0 ] ];
		$newValues = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $newValues, $defaults, true );
		$this->assertEquals( "Jaimeson", $args->get( "name" ) ); // Even though there’s no name default, set name works fine.

		$args2 = new VerifiedArguments( $newValues, $defaults, false ); // Set $allowCustomArgKeys to false.
		$this->assertNull( $args2->get( "name" ) ); // Even though name is set, it isn’t in defaults, so not put in new list.
	}

	public function testArgumentTypes() : void
	{
		$newValues = [ "name" => "Jaimeson", "age" => 27 ];

		$defaults = [ "name" => [ "type" => "integer" ] ];
		$args = new VerifiedArguments( $newValues, $defaults );
		$this->assertNull( $args->get( "name" ) ); // Since set name is not integer, it isn’t included in list.

		$defaults2 = [ "name" => [ "type" => "string" ] ];
		$args2 = new VerifiedArguments( $newValues, $defaults2 );
		$this->assertEquals( "Jaimeson", $args2->get( "name" ) ); // Since set name is a string, it’s included in list.

		$defaults3 = [ "name" => [ "type" => [ "integer", "callable", "object" ] ] ];
		$args3 = new VerifiedArguments( $newValues, $defaults3 );
		// Since none of the types in the default type list are the set name’s type (string),
		// the set name isn’t included in the final arguments list.
		$this->assertNull( $args3->get( "name" ) );

		$defaults4 = [ "name" => [ "type" => [ "integer", "callable", "object", "string" ] ] ];
		$args4 = new VerifiedArguments( $newValues, $defaults4 );
		// Since the set name’s type (string) is in the default type list,
		// it is included in the final arguments list.
		$this->assertEquals( "Jaimeson", $args4->get( "name" ) );


		$newValues2 = [ "date" => new DateTime( "10/23/2018" ) ];

		$defaults5 = [ "date" => [ "type" => \DateTime::class ] ];
		$args5 = new VerifiedArguments( $newValues2, $defaults5 );
		$this->assertEquals( $newValues2[ "date" ], $args5->get( "date" ) ); // date is DateTime, so it’s in list.

		$defaults6 = [ "date" => [ "type" => "object" ] ];
		$args6 = new VerifiedArguments( $newValues2, $defaults6 );
		$this->assertEquals( $newValues2[ "date" ], $args6->get( "date" ) ); // date is also an object, so it’s in list.
	}

	public function testWrongArgumentTypeDefaultValue() : void
	{
		$defaults = [ "name" => [ "type" => [ "integer", "callable" ], "value" => "nada" ] ];
		$newValues = [ "name" => "Jaimeson", "age" => 27 ];
		$args = new VerifiedArguments( $newValues, $defaults );
		// Since set value is not 1 of the valid default types, it falls back to default value.
		// Note that default value does not need to be default type.
		$this->assertEquals( $args->get( "name" ), "nada" );
	}

	public function testCustomTests() : void
	{
		$defaults = [ "name" => [ "value" => "Anonymous", "test" => "is_string" ] ]; // String with name of global function as callable.
		$newValues = [ "name" => 2 ];
		$args = new VerifiedArguments( $newValues, $defaults );
		$this->assertEquals( "Anonymous", $args->get( "name" ) ); // Set name fails test, so returns default.
		$newValues2 = [ "name" => "Jaimeson" ];
		$args2 = new VerifiedArguments( $newValues2, $defaults );
		$this->assertEquals( "Jaimeson", $args2->get( "name" ) ); // New set name passes test, so it is returned.

		$defaults2 = [ "number" => [ "test" => fn( $value ) => $value > 100 ] ]; // Lambda as callable.
		$newValues3 = [ "number" => 20 ];
		$args3 = new VerifiedArguments( $newValues3, $defaults2 );
		$this->assertNull( $args3->get( "number" ) ); // Set number not greater than 100, so isn’t set.
		$newValues4 = [ "number" => 200 ];
		$args4 = new VerifiedArguments( $newValues4, $defaults2 );
		$this->assertEquals( 200, $args4->get( "number" ) ); // Set number is greater than 100, so it is set.

		$defaults3 = [ "name" => [ "test" => [ "is_string", fn( $value ) => mb_strlen( $value ) < 8 ] ] ]; // Multiple tests.
		$newValues5 = [ "name" => "Jaimeson" ]; // Name has 8 characters.
		$args5 = new VerifiedArguments( $newValues5, $defaults3 );
		$this->assertNull( $args5->get( "name" ) ); // Though name passes 1st test, it fails the 2nd, so it is not set.
		$newValues6 = [ "name" => "Jaime" ]; // Name has less than 8 characters.
		$args6 = new VerifiedArguments( $newValues6, $defaults3 );
		$this->assertEquals( "Jaime", $args6->get( "name" ) ); // New name passes all tests, so it is set.
	}

	public function testSanitizer() : void
	{
		$defaults = [ "name" => [ "value" => "Anonymous", "sanitizer" => "strtoupper" ] ]; // String with name of global function as callable.
		$newValues = [ "name" => "Jaimeson" ];
		$args = new VerifiedArguments( $newValues, $defaults );
		$this->assertEquals( "JAIMESON", $args->get( "name" ) ); // Sanitizer automatically makes set value uppercase.
		$args2 = new VerifiedArguments( [], $defaults );
		$this->assertEquals( "Anonymous", $args2->get( "name" ) ); // Note that sanitizer does not apply to default values.

		$defaults2 = [ "number" => [ "value" => 10, "sanitizer" => fn( $value ) => $value * 2 ] ]; // Lambda as callable.
		$newValues2 = [ "number" => 20 ];
		$args3 = new VerifiedArguments( $newValues2, $defaults2 );
		$this->assertEquals( 40, $args3->get( "number" ) ); // Set value is double.
		$args4 = new VerifiedArguments( [], $defaults2 );
		$this->assertEquals( 10, $args4->get( "number" ) ); // But default is normal value.

		// Multiple sanitizers.
		// Note that 2nd sanitizer is all-caps. Sanitizers are applied in order, so the “strtoupper” sanitizer will
		// apply to the value before the 2nd applies, and the value that sanitizer produces will be the value the
		// 2nd sanitizer applies to.
		$defaults3 = [ "name" => [ "sanitizer" => [ "strtoupper", fn( $value ) => str_replace( "ME", "THEY", $value ) ] ] ];
		$args5 = new VerifiedArguments( $newValues, $defaults3 );
		$this->assertEquals( "JAITHEYSON", $args5->get( "name" ) );
	}

	public function testGetList() : void
	{
		$args = new VerifiedArguments( [] );
		$this->assertEquals( [], $args->getList() );

		$newValues2 = [ "name" => "Jaimeson", "age" => 27 ];
		$args2 = new VerifiedArguments( $newValues2, [], false );
		$this->assertEquals( [], $args2->getList() );

		$newValues3 = [ "name" => "Jaimeson", "age" => 27 ];
		$args3 = new VerifiedArguments( $newValues3 );
		$this->assertEquals( $newValues3, $args3->getList() );
	}

	public function testReadme() : void
	{
		$defaults =
		[
			"name" => [ "value" => "Anonymous", "type" => "string", "sanitizer" => "strtoupper" ],
			"age" => [ "type" => "integer" ],
			"birthday" => [ "type" => \DateTime::class ]
		];
		$values = [ "name" => "Jaimeson", "age" => "old", "city" => "SeaTac" ];
		$args = new VerifiedArguments( $values, $defaults );

		$this->assertEquals( "JAIMESON", $args->get( "name" ) );
		$this->assertNull( $args->get( "age" ) );
		$this->assertEquals( "SeaTac", $args->get( "city" ) );
	}
}
