<?php

namespace Shore\Framework;

class Hash
{
    public const ALGORITHM_ADLER32 = 'adler32';

    public const ALGORITHM_CRC32 = 'crc32';

    public const ALGORITHM_GOST = 'gost';

    public const ALGORITHM_MD4 = 'md4';

    public const ALGORITHM_MD5 = 'md5';

    public const ALGORITHM_PREFERRED_CRYPTO = self::ALGORITHM_SHA512;

    public const ALGORITHM_PREFERRED_NON_CRYPTO = self::ALGORITHM_MD4;

    public const ALGORITHM_SHA1 = 'sha1';

    public const ALGORITHM_SHA256 = 'sha256';

    public const ALGORITHM_SHA384 = 'sha384';

    public const ALGORITHM_SHA512 = 'sha512';

    /**
     * Generates a random hash of the given length
     *
     * @param int|null $bytes
     *
     * @return string
     * @throws \Exception
     */
    public function generate(?int $bytes = 32): string
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
    public function from($input, ?bool $randomize = false, ?string $algorithm = self::ALGORITHM_PREFERRED_NON_CRYPTO): string
    {
        // If the input is an object and it supports hash casting, call the method now
        if (is_object($input) && $input instanceof Hashable) {
            $input = $input->toHash($algorithm);
        }

        $data = (string) $input . ($randomize ? $this->generate() : '');

        return hash($algorithm, $data);
    }
}
