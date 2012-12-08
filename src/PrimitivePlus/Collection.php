<?php
namespace PrimitivePlus;

class Collection extends \ArrayObject
{
    function join($str)
    {
        $self = (array)$this;
        return implode($str, $self);
    }

    function has($needle, $strict=true)
    {
        $self = (array)$this;
        return in_array($needle, $self, $strict);
    }

    function __call($method, $args)
    {
        $method = preg_replace('/[A-Z]/', '_$0', $method);
        $lastPos = strlen($method) - 1;
        if ($method[$lastPos] === '_') {
            $method = substr($method, 0, -1);
            $chain = true;
        } else {
            $chain = false;
        }
        $func = 'array_' . $method;
        $self = (array)$this;
        $args = array_merge(array(&$self), $args);

        if (!function_exists($func)) {
            throw new BadMethodCallException;
        }

        $res = call_user_func_array($func, $args);
        if (is_array($res)) {
            $this->exchangeArray($res);
            return $this;
        } elseif ($chain) {
            $this->exchangeArray($self);
            return $this;
        } else {
            return $res;
        }
    }

    function __toString()
    {
        return json_encode($this->toArray());
    }

    function toArray()
    {
        $arr = (array)$this;

        foreach ($arr as &$val) {
            if ($val instanceof self) {
                $val = $val->toArray();
            }
        } unset($val);

        return $arr;
    }
}
