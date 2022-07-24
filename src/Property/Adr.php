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
 * ADR
 *
* the components of the delivery address for the vCard object
 *
 * Value type:  A single structured text value, separated by the SEMICOLON character
 *              Each component can have multiple (comma-separated) values.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * label-param = "LABEL=" param-value
 *
 * ADR-param = "VALUE=text" / label-param / language-param / geo-parameter / tz-parameter / altid-param / pid-param
 *             / pref-param / type-param / any-param
 *
 * ADR-value = ADR-component-pobox ";" ADR-component-ext ";"
 *             ADR-component-street ";" ADR-component-locality ";"
 *             ADR-component-region ";" ADR-component-code ";"
 *             ADR-component-country
 * ADR-component-pobox    = list-component
 * ADR-component-ext      = list-component
 * ADR-component-street   = list-component
 * ADR-component-locality = list-component
 * ADR-component-region   = list-component
 * ADR-component-code     = list-component
 * ADR-component-country  = list-component
 */
final class Adr extends PropertyBase
{
    /**
     * The structured type value
     *
     * ...consists of a sequence of address components, separated by the SEMICOLON character
     * The component values MUST be specified in their corresponding position.
     * Each component can have multiple (comma-separated) values.
     *
     * @var string[]
     */
    public static $valueComponents = [
        'pobox',
        'ext',
        'street',
        'locality',
        'region',
        'code',
        'country',
    ];

    /**
     * Class constructor
     *
     * @param string|array $pobox    list-component   or a 7-items array
     * @param null|string|array $ext list-component   or parameters
     * @param null|string $street    list-component
     * @param null|string $locality  list-component
     * @param null|string $region    list-component
     * @param null|string $code      list-component
     * @param null|string $country   list-component
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     */
    public function __construct(
        $pobox,
        $ext = null,
        ? string $street = null,
        ? string $locality = null,
        ? string $region = null,
        ? string $code = null,
        ? string $country = null,
        ? array  $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) {
        if( is_array( $pobox ) && ( 7 === count( $pobox ))) {
            $parameters = is_array( $ext ) ? $ext : [];
            $valueType  = $street;
            $group      = $locality;
            [ $pobox2, $ext, $street, $locality, $region, $code, $country ] = $pobox;
            $pobox      = $pobox2;
        }
        $this->populate(
            [
                $pobox,
                $ext,
                $street,
                $locality,
                $region,
                $code,
                $country,
            ],
            $parameters,
            $valueType,
            $group
        );
    }

    /**
     * Class factory method
     *
     * @param string|array $pobox    list-component   or a 7-items array
     * @param null|string|array $ext list-component   or parameters
     * @param null|string $street    list-component   or valueType
     * @param null|string $locality  list-component   or group
     * @param null|string $region    list-component
     * @param null|string $code      list-component
     * @param null|string $country   list-component
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return Adr
     */
    public static function factory(
        $pobox,
        $ext = null,
        ? string $street = null,
        ? string $locality = null,
        ? string $region = null,
        ? string $code = null,
        ? string $country = null,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) : Adr
    {
        return new self(
            $pobox,
            $ext,
            $street,
            $locality,
            $region,
            $code,
            $country,
            $parameters,
            $valueType,
            $group
        );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::ADR;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::LABEL,
            self::LANGUAGE,
            self::GEO,
            self::TZ,
            self::ALTID,
            self::PID,
            self::PREF,
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
}
