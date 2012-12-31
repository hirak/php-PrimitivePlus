<?php
namespace PrimitivePlus;

interface EntityInterface {
    static function schema();
    function errors();
}

abstract class Entity implements EntityInterface, \IteratorAggregate, \Countable
{
    const
        BOOL = 'boolean'
      , INT  = 'integer'
      , DBL  = 'double'
      , STR  = 'string'
      , CALL = 'callable'
      , ARR  = 'array'
      , RES  = 'resource'
      , OBJ  = 'object'
    ;
    private $_storage = array();

    private $_freezed = false;

    static private $_schemaCache = array();

    final function __construct()
    {
        $class = get_class($this);

        $schema = $this->schema();
        if (! is_array($schema)) {
            throw new \DomainException("$class::schema() must return array.");
        }

        foreach ($schema as $key => $value) {
            $type = gettype($value);
            switch ($type) {
                case self::ARR:
                    if (!is_string($value[0])) {
                        throw new \DomainException(
                            "$class::schema()[$key] must be array(string [, default])."
                        );
                    }
                    $schema[$key] = $value[0];
                    $this->_storage[$key] = isset($value[1]) ? $value[1] : null;
                    break;

                case self::STR: case self::BOOL: case self::INT:
                case self::RES: case self::DBL:
                    $schema[$key] = $type;
                    $this->_storage[$key] = $value;
                    break;

                case self::OBJ:
                    $schemaclass = get_class($value);
                    $schema[$key] = $schemaclass;
                    $this->_storage[$key] = $value;
                    break;

                default:
                    throw new \DomainException("$class::schema()[$key] is invalid.");
            }
        }
        if (!isset(self::$_schemaCache[$class])) {
            self::$_schemaCache[$class] = $schema;
        }

        call_user_func_array(array($this,'init'), func_get_args());
    }

    function init() {}

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
        $schema = self::$_schemaCache[$class];

        if (! array_key_exists($label, $schema)) {
            throw new \OutOfRangeException("$class->$label is not defined.");
        }

        switch ($schema[$label]) {
            case self::CALL:
                if (is_callable($value)) {
                    $this->_storage[$label] = $value;
                    return;
                }
                break;
            case self::BOOL: case self::INT: case self::DBL: case self::STR:
            case self::ARR: case self::OBJ: case self::RESOURCE:
                if (gettype($value) === $schema[$label]) {
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

    final function toArray()
    {
        return $this->_storage;
    }

    final function fromArray(array $newData)
    {
        $schema = self::$_schemaCache[get_class($this)];

        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $schema)) {
                $this->__set($key, $value);
            } else {
                trigger_error("$key is not defined. ignored.", E_USER_NOTICE);
            }
        }
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

    /**
     * for DCI
     */
    final protected static function _cast(self $entity)
    {
        $self = new static;
        $self->_storage =& $entity->_storage;
        $self->_freezed =& $entity->_freezed;

        return $self;
    }
}
