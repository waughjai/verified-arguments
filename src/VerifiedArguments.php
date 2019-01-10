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

			public function __construct( array $args, array $defaults = [] )
			{
				$this->args = [];
				foreach ( $defaults as $default_key => $default )
				{
					if ( is_array( $default ) && array_key_exists( "value", $default ) )
					{
						$this->args[ $default_key ] = $default[ "value" ];
					}
				}

				foreach ( $args as $arg_key => $arg_value )
				{
					if ( array_key_exists( $arg_key, $defaults ) )
					{
						if ( self::testExpectedType( $defaults[ $arg_key ], $arg_value ) )
						{
							$this->args[ $arg_key ] = $arg_value;
						}
					}
					else
					{
						$this->args[ $arg_key ] = $arg_value;
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

			private static function testExpectedType( array $obj, $value ) : bool
			{
				if ( array_key_exists( "type", $obj ) )
				{
					if ( is_array( $obj[ "type" ] ) )
					{
						foreach ( $obj[ "type" ] as $type )
						{
							if ( self::testType( $type, $value ) )
							{
								return true;
							}
						}
						return false;
					}
					else if ( is_string( $obj[ "type" ] ) )
					{
						return self::testType( $obj[ "type" ], $value );
					}
				}
				return true;
			}

			private static function testType( $expected, $tested ) : bool
			{
				return gettype( $tested ) === $expected || ( is_object( $tested ) && get_class( $tested ) === $expected );
			}

			private $args;
			private $defaults;
	}
}
