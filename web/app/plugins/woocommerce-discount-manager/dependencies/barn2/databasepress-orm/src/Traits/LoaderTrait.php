<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits;

use Closure;
trait LoaderTrait
{
    /** @var array */
    protected $with = [];
    /** @var bool */
    protected $immediate = \false;
    /**
     * @param string|array $value
     * @param bool         $immediate
     * @return mixed|LoaderTrait
     */
    public function with($value, bool $immediate = \false) : self
    {
        if (!\is_array($value)) {
            $value = [$value];
        }
        $this->with = $value;
        $this->immediate = $immediate;
        return $this;
    }
    /**
     * @return  array
     */
    protected function getWithAttributes() : array
    {
        $with = [];
        $extra = [];
        foreach ($this->with as $key => $value) {
            $fullName = $value;
            $callback = null;
            if ($value instanceof Closure) {
                $fullName = $key;
                $callback = $value;
            }
            $fullName = \explode('.', $fullName);
            $name = \array_shift($fullName);
            $fullName = \implode('.', $fullName);
            if ($fullName === '') {
                if (!isset($with[$name]) || $callback !== null) {
                    $with[$name] = $callback;
                    if (!isset($extra[$name])) {
                        $extra[$name] = [];
                    }
                }
            } else {
                if (!isset($extra[$name])) {
                    $with[$name] = null;
                    $extra[$name] = [];
                }
                $t =& $extra[$name];
                if (isset($t[$fullName]) || \in_array($fullName, $t)) {
                    continue;
                }
                if ($callback === null) {
                    $t[] = $fullName;
                } else {
                    $t[$fullName] = $callback;
                }
            }
        }
        return ['with' => $with, 'extra' => $extra];
    }
}
