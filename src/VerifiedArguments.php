<?php

declare( strict_types = 1 );
namespace WaughJ\VerifiedArguments
{
	class VerifiedArguments
	{
		//
		//  PUBLIC
		//
		/////////////////////////////////////////////////////////

			public function __construct( array $args, array $defaults = [], bool $allowCustomArgKeys = true )
			{
				$this->args = [];

				// Start by setting defaults.
				foreach ( $defaults as $defaultKey => $default )
				{
					// Test that default is validly formatted.
					if ( is_array( $default ) && array_key_exists( "value", $default ) )
					{
						// Set default.
						$this->args[ $defaultKey ] = $default[ "value" ];
					}
				}

				// Then replace with custom args, if available.
				foreach ( $args as $argKey => $argValue )
				{
					// Test validity.
					// If default is set, check that the value fits the expected type ( if any ).
					// If default not set, only allow if $allowCustomArgKeys is set to true.
					$valid = false;
					if ( array_key_exists( $argKey, $defaults ) )
					{
						if
						(
							self::testExpectedType( $defaults[ $argKey ], $argValue ) &&
							self::testCustomTests( $defaults[ $argKey ], $argValue )
						)
						{
							$valid = true;
						}
					}
					else if ( $allowCustomArgKeys )
					{
						$valid = true;
					}

					if ( $valid )
					{
						// If defaults set, apply sanitizer ( if any ).
						$value = ( array_key_exists( $argKey, $defaults ) ) ? self::applySanitizer( $defaults[ $argKey ], $argValue ) : $argValue;
						$this->args[ $argKey ] = $value;
					}
				}
			}

			public function get( string $key )
			{
				return ( array_key_exists( $key, $this->args ) ) ? $this->args[ $key ] : null;
			}

			public function getList() : array
			{
				return $this->args;
			}



		//
		//  PRIVATE
		//
		/////////////////////////////////////////////////////////

			private static function testExpectedType( array $default, $value ) : bool
			{
				// If default option has type specified, validate that the new value matches it.
				if ( array_key_exists( "type", $default ) )
				{
					// If array, ensure that new value is at least 1 of these types.
					if ( is_array( $default[ "type" ] ) )
					{
						foreach ( $default[ "type" ] as $type )
						{
							if ( self::testType( $type, $value ) )
							{
								return true;
							}
						}
						return false;
					}
					else if ( is_string( $default[ "type" ] ) )
					{
						return self::testType( $default[ "type" ], $value );
					}
				}
				return true;
			}

			private static function testType( $expected, $tested ) : bool
			{
				return gettype( $tested ) === $expected || ( is_object( $tested ) && get_class( $tested ) === $expected );
			}

			private static function testCustomTests( array $default, $value ) : bool
			{
				// If custom test is set, run custom test.
				if ( array_key_exists( "test", $default ) )
				{
					// If test is list, run every test.
					// If a single test fails, return false.
					if ( is_array( $default[ "test" ] ) )
					{
						foreach ( $default[ "test" ] as $test )
						{
							if ( !call_user_func( $test, $value ) )
							{
								return false;
							}
						}
					}
					else
					{
						return call_user_func( $default[ "test" ], $value );
					}
				}

				// If no tests, then there’s no test to fail.
				return true;
			}

			private static function applySanitizer( array $default, $value )
			{
				// If default has sanitizers set, apply them all to the value before returning.
				// Else, just return it as it is.
				if ( array_key_exists( "sanitizer", $default ) )
				{
					$tests = ( is_array( $default[ "sanitizer" ] ) ) ? $default[ "sanitizer" ] : [ $default[ "sanitizer" ] ];
					foreach ( $tests as $test )
					{
						$value = call_user_func( $test, $value );
					}
				}
				return $value;
			}

			private array $args;
	}
}
