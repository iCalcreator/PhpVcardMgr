<?php
/**
 * PhpVcardMgr, the PHP class package managing Vcard/Xcard/Jcard information.
 *
 * This file is a part of PhpVcardMgr.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software PhpVcardMgr.
 *            The above copyright, link, package and this licence notice shall
 *            be included in all copies or substantial portions of the PhpVcardMgr.
 *
 *            PhpVcardMgr is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            PhpVcardMgr is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with PhpVcardMgr. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\PhpVcardMgr\Util;

use RuntimeException;

/**
 * Class json
 *
 * Encapsulates json methods
 */
class Json
{
    /**
     * Encapsulates json method json_decode
     *
     * @param string $jsonString
     * @param null|int $flags    default JSON_OBJECT_AS_ARRAY
     * @return array
     * @throws RuntimeException
     */
    public static function jsonDecode( string $jsonString, ? int  $flags = null ) : array
    {
        static $ERR = 'NO json decode result array';
        $jsonArray = json_decode(
            $jsonString,
            true,
            512,
            $flags ?? JSON_OBJECT_AS_ARRAY
        );
        $jsonLastError = json_last_error();
        if( JSON_ERROR_NONE !== $jsonLastError ) {
            throw new RuntimeException( json_last_error_msg(), $jsonLastError );
        }
        if( ! is_array( $jsonArray )) {
            throw new RuntimeException( $ERR );
        }
        return $jsonArray;
    }

    /**
     * Encapsulates json method json_encode
     *
     * @param string[]|string[][] $jsonArray
     * @param null|int $flags   default  JSON_UNESCAPED_SLASHES
     * @return string
     * @throws RuntimeException
     */
    public static function jsonEncode( array $jsonArray, ? int $flags = null ) : string
    {
        if( false === ( $jsonString = json_encode( $jsonArray,$flags ?? JSON_UNESCAPED_SLASHES ))) {
            throw new RuntimeException( json_last_error_msg(), json_last_error());
        }
        return $jsonString;
    }
}
