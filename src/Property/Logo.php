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
 * LOGO
 *
* a graphic image of a logo associated with the object the vCard represents
 *
 * Value type:  A single URI.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * LOGO-param = "VALUE=uri" / language-param / pid-param / pref-param / type-param / mediatype-param / altid-param / any-param
 * LOGO-value = URI

 */
final class Logo extends PropertyBase
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
     * @return Logo
     */
    public static function factory( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : Logo
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::LOGO;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::LANGUAGE,
            self::PID,
            self::PREF,
            self::TYPE,
            self::MEDIATYPE,
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
        return $default ? self::URI : [ self::URI ];
    }
}
