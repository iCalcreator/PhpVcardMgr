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
 * ORG
 *
* the organizational name and units associated  with the vCard
 *
 * Value type:  A single structured text value consisting of components separated by the SEMICOLON character (U+003B).
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * ORG-param = "VALUE=text" / sort-as-param / language-param / pid-param / pref-param / altid-param / type-param / any-param
 * ORG-value = component *(";" component)
 */
final class Org extends PropertyBase
{
    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::ORG;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::SORT_AS,
            self::LANGUAGE,
            self::PID,
            self::PREF,
            self::ALTID,
            self::TYPE,
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
        static $ERR = 'Org expects string/array, got \'%s\'' ;
        switch( true ) {
            case is_array( $value ) :
                break;
            case ( false !== strpos( $value, StringUtil::$SEMIC )) :
                $value = explode( StringUtil::$SEMIC, $value );
                break;
            default :
                $value = [ $value ];
                break;
        }
        $value = self::trimSub( $value );
        if( empty( implode( $value ))) {
            throw new InvalidArgumentException( $ERR . var_export( $value, true ));
        }
        $this->value = $value;
        return $this;
    }
}
