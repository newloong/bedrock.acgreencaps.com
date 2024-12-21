<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\databasepress
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\Schema;

class BaseColumn
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $type;
    /** @var array */
    protected $properties = [];
    /**
     * BaseColumn constructor.
     *
     * @param string      $name
     * @param string|null $type
     */
    public function __construct(string $name, string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * @return array
     */
    public function getProperties() : array
    {
        return $this->properties;
    }
    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }
    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function set(string $name, $value) : self
    {
        $this->properties[$name] = $value;
        return $this;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->properties[$name]);
    }
    /**
     * @param string     $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }
    /**
     * @param string $value
     * @return $this
     */
    public function size(string $value) : self
    {
        $value = \strtolower($value);
        if (!\in_array($value, ['tiny', 'small', 'normal', 'medium', 'big'])) {
            return $this;
        }
        return $this->set('size', $value);
    }
    /**
     * @return $this
     */
    public function notNull() : self
    {
        return $this->set('nullable', \false);
    }
    /**
     * @param string $comment
     * @return $this
     */
    public function description(string $comment) : self
    {
        return $this->set('description', $comment);
    }
    /**
     * @param mixed $value
     * @return $this
     */
    public function defaultValue($value) : self
    {
        return $this->set('default', $value);
    }
    /**
     * @param bool $value
     * @return $this
     */
    public function unsigned(bool $value = \true) : self
    {
        return $this->set('unsigned', $value);
    }
    /**
     * @param mixed $value
     * @return $this
     */
    public function length($value) : self
    {
        return $this->set('length', $value);
    }
}
