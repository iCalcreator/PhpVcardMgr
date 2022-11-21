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

/**
 * GENDER
 *
 * Value type:  A single structured value with two components, separated by the SEMICOLON character
 *              Each component has a single text value.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * GENDER-param = "VALUE=text" / any-param
 * GENDER-value = sex [";" text]
 * sex = "" / "M" / "F" / "O" / "N" / "U"
 */
final class Gender extends PropertyBase
{
    /**
     * The components correspond, in sequence, to the sex (biological), and gender identity.
     * Each component is optional.
     *
     * @var string[]
     */
    public static $valueComponents = [
        'sex',
        'identity'
    ];

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::GENDER;
    }


    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function isAnyParameterAllowed() : bool
    {
        return true;
    }

    /**
     * GENDER-value = sex [";" indentity ]
     * sex = "" / "M" / "F" / "O" / "N" / "U"
     * A single letter.  M stands for "male", F stands for "female", O stands for "other",
     * N stands for "none or not applicable", U stands for "unknown".
     *
     * @override
     * @param string|array $value
     * @return static
     */
    public function setValue( $value ) : PropertyInterface
    {
        static $EXPECTED = [ '', 'M', 'F', 'O', 'N', 'U' ];
        static $ERR      = 'Gender expects one of "\'\',M,F,O,N,U", got \'%s\'' ;
        switch( true ) {
            case is_array( $value ) :
                break;
            case ! is_string( $value ) :
                throw new InvalidArgumentException( sprintf( $ERR, var_export( $value, true )));
            case StringUtil::containsSemic( $value ) :
                $value = StringUtil::semicSplit( $value, 2 );
                break;
            default :
                $value = [ $value ];
                break;
        }
        $value[0] = strtoupper( $value[0] );
        $value    = self::trimSub( $value );
        if( ! in_array( $value[0], $EXPECTED, true )) {
            throw new InvalidArgumentException( sprintf( $ERR, var_export( $value[0], true )));
        }
        if( empty( $value[1] )) {
            unset( $value[1] );
        }
        $this->value   = $value;
        return $this;
    }
}
