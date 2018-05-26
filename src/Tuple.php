<?php

declare(strict_types=1);

namespace Spatie\Typed;

use ArrayAccess;

class Tuple implements ArrayAccess
{
    use ValidatesType;

    /** @var \Spatie\Typed\Type[] */
    private $types;

    /** @var @var array */
    private $data;

    public function __construct(Type ...$types)
    {
        $this->types = $types;
    }

    public function set(array $data): self
    {
        $iterator = new TupleIterator($this->types, $data);

        foreach ($iterator as $key => ['type' => $type, 'value' => $value]) {
            $data[$key] = $this->validateType($type, $value);
        }

        $this->data = $data;

        return $this;
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null || ! is_numeric($offset)) {
            throw WrongType::fromMessage('You must specify a numeric offset');
        }

        $type = $this->types[$offset] ?? null;

        if (! $type) {
            throw WrongType::fromMessage("No type was configured for this tuple at offset {$offset}");
        }

        $this->data[$offset] = $this->validateType($type, $value);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetUnset($offset)
    {
        throw WrongType::fromMessage('Tuple values cannot be unset');
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
