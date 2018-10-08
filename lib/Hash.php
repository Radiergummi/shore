<?php

namespace Shore\Framework;

class Hash
{
    public const ALGORITHM_MD5 = 'md5';

    /**
     * Generates a random hash of the given length
     *
     * @param int|null $bytes
     *
     * @return string
     * @throws \Exception
     */
    public function generate(?int $bytes = 32)
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Generates a random hash from an input
     *
     * @param        $input
     * @param bool   $randomize
     * @param string $algorithm
     *
     * @return string
     * @throws \Exception
     */
    public function from($input, ?bool $randomize = false, ?string $algorithm = self::ALGORITHM_MD5)
    {
        // If the input is an object and it supports hash casting, call the method now
        if (is_object($input) && $input instanceof Hashable) {
            $input = $input->toHash($algorithm);
        }

        $data = (string) $input . ($randomize ? $this->generate() : '');

        return hash($algorithm, $data);
    }
}
