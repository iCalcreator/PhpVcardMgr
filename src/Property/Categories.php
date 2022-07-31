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
 * CATEGORIES
 *
* application category information about the vCard, also known as "tags"
 *
 * Value type:  One or more text values separated by a COMMA character (U+002C).
 * Cardinality:  *
 *
 * CATEGORIES-param = "VALUE=text" / pid-param / pref-param / type-param / altid-param / any-param
 * CATEGORIES-value = text-list
 */
final class Categories extends PropertyBase
{
    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::CATEGORIES;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::PID,
            self::PREF,
            self::TYPE,
            self::ALTID,
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
     * @override
     * @param string|array $value
     * @return static
     * @throws InvalidArgumentException
     */
    public function setValue( $value ) : PropertyInterface
    {
        static $ERR = '%s expects string, got \'%s\'' ;
        switch( true ) {
            case is_array( $value ) :
                break;
            case ( ! is_string( $value )) :
                throw new InvalidArgumentException(
                    sprintf(
                        $ERR,
                        $this->getPropName(),
                        var_export( $value, true )
                    )
                );
            case ( false !== strpos( $value, StringUtil::$COMMA )) :
                $value = explode( StringUtil::$COMMA, $value );
                break;
            default :
                $value = [ $value ];
                break;
        }
        $this->value = self::trimSub( $value );
        return $this;
    }
}
