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
 * N
 *
* the components of the name of the object the vCard represents
 *
 * Value type:  A single structured text value.  Each component can have multiple (comma-separated) values.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * N-param = "VALUE=text" / sort-as-param / language-param / altid-param / any-param
 * N-value = list-component 4(";" list-component)
 * Family Names (also known as surnames), Given Names, Additional Names, Honorific Prefixes, and Honorific Suffixes.
 * Individual text components can include multiple text values separated by the COMMA character (U+002C).
 */
final class N extends PropertyBase
{
    /**
     * The structured property value
     *
     * ...consists of a sequence of components, separated by the SEMICOLON character
     * The component values MUST be specified in their corresponding position.
     * Each component can have multiple (comma-separated) values.
     *
     * @var string[]
     */
    public static $valueComponents = [
        'surname',
        'given',
        'additional',
        'prefix',
        'suffix'
    ];

    /**
     * Class constructor
     *
     * @param string|array $familyNames  or a 5-items array
     * @param null|string|array $givenNames
     * @param null|string $additionalNames
     * @param null|string $namePrefixes
     * @param null|string $nameSuffixes
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     */
    public function __construct(
        $familyNames,
        $givenNames = null,
        ? string $additionalNames = null,
        ? string $namePrefixes = null,
        ? string $nameSuffixes = null,
        ? array  $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) {
        if( is_array( $familyNames ) & ( 5 === count( $familyNames ))) {
            $parameters = is_array( $givenNames ) ? $givenNames : [];
            $valueType  = $additionalNames;
            $group      = $namePrefixes;
            [ $familyNames2, $givenNames, $additionalNames, $namePrefixes, $nameSuffixes ] = $familyNames;
            $familyNames = $familyNames2;
        }
        $this->populate(
            [
                $familyNames,
                $givenNames,
                $additionalNames,
                $namePrefixes,
                $nameSuffixes
            ],
            $parameters,
            $valueType,
            $group
        );
    }

    /**
     * Class factory method
     *
     * @param string|array $familyNames  or a 5-items array
     * @param null|string|array $givenNames
     * @param null|string $additionalNames
     * @param null|string $namePrefixes
     * @param null|string $nameSuffixes
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return N
     */
    public static function factory(
        $familyNames,
        $givenNames = null,
        ? string $additionalNames = null,
        ? string $namePrefixes = null,
        ? string $nameSuffixes = null,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) : N
    {
        return new self(
            $familyNames,
            $givenNames,
            $additionalNames,
            $namePrefixes,
            $nameSuffixes,
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
        return self::N;
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
}
