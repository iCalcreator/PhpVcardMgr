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

abstract class CatNickBase extends PropertyBase
{
    /**
     * @param string $propName
     * @param string|array $value
     * @return array
     */
    protected static function conformInput( string $propName, $value ) : array
    {
        static $ERR = '%s expects string/array, got \'%s\'' ;
        switch( true ) {
            case is_array( $value ) :
                break;
            case ( ! is_string( $value )) :
                throw new InvalidArgumentException(
                    sprintf( $ERR, $propName, var_export( $value, true ))
                );
            case ( false !== strpos( $value, StringUtil::$COMMA )) :
                $value = explode( StringUtil::$COMMA, $value );
                break;
            default :
                $value = [ $value ];
                break;
        }
        return self::trimSub( $value );
    }
}
