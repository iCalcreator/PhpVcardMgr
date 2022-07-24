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

use Kigkonsult\PhpVcardMgr\Util\StringUtil;

/**
 * Any property
 *
 * name prefixed by 'X-'
 * 'Any' parameter allowed
 */
final class Xprop extends PropertyBase
{
    /**
     * X-property name
     *
     * @var string
     */
    private $propName;

    /**
     * Class constructor
     *
     * @param string $propName
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     */
    public function __construct(
        string $propName,
        string $value,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) {
        $this->setPropName( $propName );
        $this->populate( $value, $parameters, $valueType, $group );
    }

    /**
     * Class factory method
     *
     * @param string $name
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return Xprop
     */
    public static function factory(
        string $name,
        string $value,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) : Xprop
    {
        return new self( $name, $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return $this->propName;
    }

    /**
     * @param string $propName
     * @return Xprop
     */
    public function setPropName( string $propName ) : Xprop
    {
        if( ! StringUtil::isXprefixed( $propName )) {
            $propName = self::XPREFIX . $propName;
        }
        $this->propName = $propName;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::LANGUAGE,
            self::PREF,
            self::ALTID,
            self::PID,
            self::TYPE,
            self::MEDIATYPE,
            self::CALSCALE,
            self::SORT_AS,
            self::GEO,
            self::TZ
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
        return $default
            ? self::TEXT
            : [
                self::TEXT,
                self::BOOLEAN,
                self::DATE,
                self::DATETIME,
                self::DATEANDORTIME,
                self::FLOAT,
                self::INTEGER,
                self::LANGUAGETAG,
                self::TIMESTAMP,
                self::TIME,
                self::URI,
                self::UTCOFFSET,
            ];
    }
}
