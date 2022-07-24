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
 * FullName, vcard FN
 *
* the formatted text corresponding to the name of the object the vCard represents
 *
 * Value type:  A single text value.
 * Cardinality:  1*, One or more instances per vCard MUST be present.
 *
 * FullName-param = "VALUE=text" / type-param / language-param / altid-param / pid-param / pref-param / any-param
 * FullName-value = text
 */
final class FullName extends PropertyBase
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
     * @return FullName
     */
    public static function factory( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : FullName
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::FN;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::TYPE,
            self::LANGUAGE,
            self::ALTID,
            self::PID,
            self::PREF,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function isAnyParameterAllowed() : bool
    {
        return true;
    }
}
