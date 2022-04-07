<?php

namespace App\Ethereum;

use Ethereum\EcRecover;
use kornrunner\Keccak;

class Address
{

    CONST ZERO_ADDRESS = '0x0000000000000000000000000000000000000000';
    /**
     * https://eips.ethereum.org/EIPS/eip-55
     *
     * @param string $addr
     *
     * @return string
     *
     * @throws \Exception
     */
    static public function encode(string $addr): string
    {
        $hash = Keccak::hash($addr = strtolower(preg_replace('/^0x/', '', $addr)), 256);
        if (!preg_match('/[0-9a-f]{40}/', $addr)) {
            throw new \InvalidArgumentException();
        }

        for ($i = 0; $i < 40; $i++) {
            if (8 <= intval($hash[$i], 16)) {
                $addr[$i] = strtoupper($addr[$i]);
            }
        }

        return '0x'.$addr;
    }

    static public function personalEcRecover(string $message, string $signature): string
    {
        $s = hex2bin(preg_replace('/^0x/', '', $signature));
        if (!$s || 65 !== strlen($s) || !in_array($s[64], ["\x1b", "\x1c"])) {
            throw new \InvalidArgumentException();
        }

        return EcRecover::personalEcRecover($message, '0x'.bin2hex($s));
    }

    static public function bcdechex($dec) {
        $hex = '';
        do {
            $last = bcmod($dec, 16);
            $hex = dechex($last).$hex;
            $dec = bcdiv(bcsub($dec, $last), 16);
        } while($dec>0);
        return '0x'.$hex;
    }

    static public function bchexdec($hex)
    {
        $len = strlen($hex);
        $dec = 0;
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }
}
