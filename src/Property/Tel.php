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

/**
 * TEL
 *
* the telephone number for telephony communication with the object the vCard represents
 *
 * Value type:  By default, it is a single free-form text value (for backward compatibility with vCard 3),
 * but it SHOULD be reset to a URI value.
 * It is expected that the URI scheme will be "tel", as specified in [RFC3966], but other schemes MAY be used.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * TEL-param = TEL-text-param / TEL-uri-param
 * TEL-value = TEL-text-value / TEL-uri-value
 * ; Value and parameter MUST match.
 *
 * TEL-text-param = "VALUE=text"
 * TEL-text-value = text
 *
 * TEL-uri-param = "VALUE=uri" / mediatype-param
 * TEL-uri-value = URI
 *
 * TEL-param =/ type-param / pid-param / pref-param / altid-param / any-param
 *
 * type-param-tel = "text" / "voice" / "fax" / "cell" / "video" / "pager" / "textphone" / iana-token / x-name
 * ; type-param-tel MUST NOT be used with a property other than TEL.
 */
final class Tel extends PropertyBase
{
    /**
     * Class constructor
     *
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @todo other schemas ???
     */
    public function __construct( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) {
        static $SCHEME_TEL = 'tel:';
        if( empty( $valueType ) && ( 0 === stripos( $value, $SCHEME_TEL ))) {
            $valueType = self::URI;
        }
        $this->populate( $value, $parameters, $valueType, $group );
    }

    /**
     * Class factory method
     *
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return Tel
     */
    public static function factory( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : Tel
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::TEL;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::MEDIATYPE,
            self::TYPE,
            self::PID,
            self::PREF,
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
     */
    public static function getAcceptedValueTypes( ? bool $default = false )
    {
        return $default ? self::TEXT : [ self::TEXT, self::URI ];
    }
}
