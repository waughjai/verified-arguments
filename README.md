Verified Arguments
=========================

Class for generating special type of associative array/map with fallback default values, validity tests, and sanitizers.

## Use

Takes in associative array of custom values, associative array of default values, and boolean determining whether a
new value can be set without a default set.

Default values are fallbacks for keys whose values are not custom-set. If neither a default nor custom value is set
for a key, trying to get the value of that key returns null. The custom value is only set if it follows the tests
and types specified for that default. Tests can be any callable and types must be strings representing the type name.
Tests and types can be either singular or lists. A custom-set value must pass all tests in a list and must be at least
1 of the types in a list. Otherwise, the set value with fallback to the default value or to null if no default value
is given.

Defaults also have sanitizer, which will be applied to any custom-set values before set. Sanitizers can be any
callable and can be singular or an array of callables, which will all be applied to the value in the order given.
( The 2nd sanitizer will sanitize the output of the 1st sanitizer, the 3rd the 2nd, & so on ).

## Example

    use WaughJ\VerifiedArguments;

    $defaults =
    [
        "name" => [ "value" => "Anonymous", "type" => "string", "sanitizer" => "strtoupper" ],
        "age" => [ "type" => "integer" ],
        "birthday" => [ "type" => \DateTime::class ]
    ];
    $values = [ "name" => "Jaimeson", "age" => "old", "city" => "SeaTac" ];
    $args = new VerifiedArguments( $values, $defaults );

    echo( $args->get( "name" ) ); // Will print "JAIMESON".

    echo( $args->get( "age" ) ); // Will print null.

    echo( $args->get( "city" ) ); // Will print "SeaTac".

## Changelog

### 1.0.0
* Implement custom tests and sanitizers.
* Add flag for blocking custom values without defaults set.

### 0.6.0
* Add ability to get list from object

### 0.5.0
* 1st beta.