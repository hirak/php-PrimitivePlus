php-PrimitivePlus
=================

Primitive wrapping Classes

## Entity

A Class for User-Defined Type in PHP.

```php
<?php
class Triangle extends PrimitivePlus\Entity
{
    static function getSchema()
    {
        return array(
            'a' => self::NUMBER,
            'b' => self::NUMBER,
            'c' => self::NUMBER,
        );
    }

    static function getDefault()
    {
        return array();
    }

    function isValid()
    {
        return true;
    }
}

$tri = new Triangle;
```
