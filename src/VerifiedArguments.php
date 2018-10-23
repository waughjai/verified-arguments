<?php

declare( strict_types = 1 );
namespace WaughJ\VerifiedArguments
{
	class VerifiedArguments
	{
		public function __construct( array $args, array $defaults = [] )
		{
			$this->args = $defaults;
			foreach ( $args as $arg_key => $arg_value )
			{
				if ( array_key_exists( $arg_key, $this->args ) )
				{
					
				}
			}
		}

		public function get( string $key )
		{
			return ( isset( $this->args[ $key ] ) ) ? $this->args[ $key ] : null;
		}

		private $args;
		private $defaults;
	}
}
