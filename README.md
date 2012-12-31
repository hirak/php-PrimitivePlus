php-PrimitivePlus
=================

Primitive wrapping Classes

## Entity

A Class for User-Defined Type in PHP.

```php
<?php
class Foo extends PrimitivePlus\Entity
{
    static function schema()
    {
        return array(
            'int' => 0,   // integer
            'dbl' => 1.1, // double
            'str' => 'some text', //string
            'boo' => true, //boolean
        );
    }

    function checkErrors()
    {
        return array();
    }
}

$tri = new Foo;

$tri->int = 1; // success
//$tri->int = '5'; //throw Exception
```

### for DCI (Data, Context and Interactions)

by cast() method

```php
<?php
class User extends PrimitivePlus\Entity
{
    static function schema()
    {
        return array(
            'name' => '',
            'email' => 'foo@example.com',
        );
    }

    static function cast(self $user)
    {
        return parent::_cast($user);
    }

    function checkErrors() { return array(); }
}
```

