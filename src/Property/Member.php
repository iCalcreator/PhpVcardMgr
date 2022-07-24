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
 * MEMBER
 *
 * include a member in the group this vCard represents
 *
 * Value type:  A single URI.  It MAY refer to something other than a vCard object.
 * For example, an email distribution list could employ the "mailto" URI scheme [RFC6068] for efficiency.
 * Cardinality: *
 *
 * MEMBER-param = "VALUE=uri" / pid-param / pref-param / altid-param / mediatype-param / any-param
 * MEMBER-value = URI
 */
final class Member extends PropertyBase
{
    /**
     * Class constructor
     *
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     */
    public function __construct( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) {
        $this->populate( $value, $parameters, $valueType, $group );
    }

    /**
     * Class factory method
     *
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return Member
     */
    public static function factory( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : Member
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::MEMBER;
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
            self::ALTID,
            self::MEDIATYPE,
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
        return $default ? self::URI : [ self::URI ];
    }

    /**
     * @override
     */
    public function setValue( $value ) : PropertyInterface
    {
        static $MAILTO = 'mailto:';
        if( 0 === stripos( substr( $value, 0, 7 ), $MAILTO )) {
            $value = substr( $value, 7 );
        }
        $this->value = $value;
        return $this;
    }
}
