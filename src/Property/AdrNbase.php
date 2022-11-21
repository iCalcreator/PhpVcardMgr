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
namespace Kigkonsult\PhpVcardMgr\Property;

use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

abstract class AdrNbase extends PropertyBase
{
    /**
     * @ param int $expNumArgsarray,
     * @ param $value1, value2..
     * @return array
     */
    protected static function conformInput() : array
    {
        $value   = self::trimSub(
            self::fixArgList( func_get_args())
        );
        if( empty( implode( $value ))) {
            throw new InvalidArgumentException( static::getERRstr( $value ));
        }
        return $value;
    }

    /**
     * @param array $argList
     * @return array
     */
    protected static function fixArgList( array $argList ) : array
    {
        switch( true ) {
            case ( is_string( $argList[1] ) && StringUtil::containsSemic( $argList[1] )) :
                $argList[1] = explode( StringUtil::$SEMIC, $argList[1] );
                // fall through
            case is_array( $argList[1] ) :
                $value   = self::processArr( $argList[0], $argList[1] );
                break;
            default :
                $value   = [];
                for( $x  = 1; $x <= $argList[0]; $x++ ) {
                    $value[] = $argList[$x] ?? null;
                }
        } // end switch
        return $value;
    }

    /**
     * @param int $expNumArgsarray
     * @param array $value
     * @return array
     */
    protected static function processArr( int $expNumArgsarray, array $value ) : array
    {
        $arrCnt = count( $value );
        switch( true ) {
            case ( $expNumArgsarray === $arrCnt ) :
                break;
            case ( $expNumArgsarray > $arrCnt ) :
                $value = array_pad( $value, $expNumArgsarray, StringUtil::$SP0 );
                break;
            default :
                throw new InvalidArgumentException( static::getERRstr( $value ));
        } // end switch
        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    abstract protected static function getERRstr( $value );
}
