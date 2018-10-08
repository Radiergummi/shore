<?php

namespace Shore\Framework;

class Config
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieves a value from the config
     *
     * @param string $key
     * @param mixed  $fallback
     *
     * @return mixed
     */
    public function get(string $key, $fallback = null)
    {
        return ArrayHelper::get($this->data, $key, $fallback);
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key)
    {
        return ArrayHelper::has($this->data, $key);
    }

    /**
     * Set a given configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value = null): void
    {
        ArrayHelper::set($this->data, $key, $value);
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);
        array_unshift($array, $value);
        $this->set($key, $array);
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);
        $array[] = $value;
        $this->set($key, $array);
    }

    public function all(): array
    {
        return $this->data;
    }
}
