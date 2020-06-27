<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

namespace raklib;

use InvalidArgumentException;
use function chr;
use function define;
use function defined;
use function ord;
use function pack;
use function preg_replace;
use function round;
use function sprintf;
use function substr;
use function unpack;
use const PHP_INT_MAX;

if(!defined("ENDIANNESS")){
	define("ENDIANNESS", (pack("s", 1) === "\0\1" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
}

class Binary{
	public const BIG_ENDIAN = 0x00;
	public const LITTLE_ENDIAN = 0x01;

	public static function signByte($value) {
		return $value << 56 >> 56;
	}

	public static function unsignByte($value) {
		return $value & 0xff;
	}

	public static function signShort($value) {
		return $value << 48 >> 48;
	}

	public static function unsignShort($value) {
		return $value & 0xffff;
	}

	public static function signInt($value) {
		return $value << 32 >> 32;
	}

	public static function unsignInt($value) {
		return $value & 0xffffffff;
	}


	public static function flipShortEndianness($value) {
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness($value) {
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness($value) {
		return self::readLLong(self::writeLong($value));
	}

	/**
	 * Reads a byte boolean
	 *
	 * @param string $b
	 *
	 * @return bool
	 */
	public static function readBool($b) {
		return $b !== "\x00";
	}

	/**
	 * Writes a byte boolean
	 *
	 * @param bool $b
	 *
	 * @return string
	 */
	public static function writeBool($b) {
		return $b ? "\x01" : "\x00";
	}

	/**
	 * Reads an unsigned byte (0 - 255)
	 *
	 * @param string $c
	 *
	 * @return int
	 */
	public static function readByte($c) {
		return ord($c[0]);
	}

	/**
	 * Reads a signed byte (-128 - 127)
	 *
	 * @param string $c
	 *
	 * @return int
	 */
	public static function readSignedByte($c) {
		return self::signByte(ord($c[0]));
	}

	/**
	 * Writes an unsigned/signed byte
	 *
	 * @param int $c
	 *
	 * @return string
	 */
	public static function writeByte($c) {
		return chr($c);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readShort($str) {
		return unpack("n", $str)[1];
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort($str) {
		return self::signShort(unpack("n", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeShort($value) {
		return pack("n", $value);
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLShort($str) {
		return unpack("v", $str)[1];
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readSignedLShort($str) {
		return self::signShort(unpack("v", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLShort($value) {
		return pack("v", $value);
	}

	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readTriad($str) {
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * Writes a 3-byte big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeTriad($value) {
		return substr(pack("N", $value), 1);
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLTriad($str) {
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * Writes a 3-byte little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLTriad($value) {
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * Reads a 4-byte signed integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readInt($str) {
		return self::signInt(unpack("N", $str)[1]);
	}

	/**
	 * Writes a 4-byte integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeInt($value) {
		return pack("N", $value);
	}

	/**
	 * Reads a 4-byte signed little-endian integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLInt($str) {
		return self::signInt(unpack("V", $str)[1]);
	}

	/**
	 * Writes a 4-byte signed little-endian integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLInt($value) {
		return pack("V", $value);
	}

	/**
	 * Reads a 4-byte floating-point number
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readFloat($str) {
		return unpack("G", $str)[1];
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedFloat($str, $accuracy) {
		return round(self::readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeFloat($value) {
		return pack("G", $value);
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLFloat($str) {
		return unpack("g", $str)[1];
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedLFloat($str, $accuracy) {
		return round(self::readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLFloat($value) {
		return pack("g", $value);
	}

	/**
	 * Returns a printable floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function printFloat($value) {
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	/**
	 * Reads an 8-byte floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readDouble($str) {
		return unpack("E", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeDouble($value) {
		return pack("E", $value);
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLDouble($str) {
		return unpack("e", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLDouble($value) {
		return pack("e", $value);
	}

	/**
	 * Reads an 8-byte integer.
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLong($str) {
		return unpack("J", $str)[1];
	}

	/**
	 * Writes an 8-byte integer.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLong($value) {
		return pack("J", $value);
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLLong($str) {
		return unpack("P", $str)[1];
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLLong($value) {
		return pack("P", $value);
	}
}
