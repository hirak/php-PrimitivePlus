<?php
namespace PrimitivePlus;

abstract class Entity implements \IteratorAggregate, \Countable
{
    const
        BOOLEAN  = 'boolean'
      , INTEGER  = 'integer'
      , DOUBLE   = 'double'
      , STRING   = 'string'
      , CALLABLE = 'callable'
      , ARR      = 'array'
      , RESOURCE = 'resource'
      , OBJECT   = 'object'
    ;
    private $_storage = array();

    private $_freezed = false;

    static private $_cache = array();

    final function __construct(array $init=array())
    {
        $class = get_class($this);

        if (isset(self::$_cache[$class])) {
            $schema = self::$_cache[$class];
        } else {
            $schema = static::getSchema();
            if (!is_array($schema)) {
                throw new \DomainException("$class::getSchema() must return string[].");
            }
            foreach ($schema as $key => $value) {
                if (! is_string($value)) {
                    throw new \DomainException("$class::getSchema()[$key] must be string.");
                }
            }
        }

        $default = static::getDefault();
        if (!is_array($default)) {
            throw new \DomainException($class'::getDefault() must return array.');
        }

        $this->_storage = $default;

        foreach ($schema as $key => $value) {
            if (! array_key_exists($key, $this->_storage)) {
                $this->_storage[$key] = null;
            }
        }

        foreach ($init as $key => $value) {
            $this->__set($key, $value);
        }
    }

    final function __get($label)
    {
        if (! array_key_exists($label, $this->_storage)) {
            throw new \OutOfRangeException(get_class($this) . "->$label is not defined.");
        }

        return $this->_storage[$label];
    }

    final function __set($label, $value)
    {
        if ($this->_freezed) {
            throw new \DomainException("Object is freezed.");
        }

        $class = get_class($this);
        $schema = self::$_cache[$class];

        if (! array_key_exists($label, $schema)) {
            throw new \OutOfRangeException("$class->$label is not defined.");
        }

        $type = gettype($value);
        switch ($schema[$label]) {
            case self::CALLABLE:
                if (is_callable($value)) {
                    $this->_storage[$label] = $value;
                    return;
                }
                break;
            case self::BOOLEAN: case self::INTEGER: case self::DOUBLE: case self::STRING:
            case self::ARR: case self::OBJECT: case self::RESOURCE:
                if ($type === $schema[$label]) {
                    $this->_storage[$label] = $value;
                    return;
                }
                break;
            default:
                if ($value instanceof $schema[$label]) {
                    $this->_storage[$label] = $value;
                    return;
                }
        }

        throw new \InvalidArgumentException("$class->$value must be {$schema[$label]}.");
    }

    final function freeze()
    {
        $this->_freezed = true;
    }

    function getIterator()
    {
        return new \ArrayIterator($this->_storage);
    }

    function count()
    {
        return count($this->_storage);
    }

    abstract static function getSchema();

    abstract static function getDefault();

    abstract function isValid();
}
