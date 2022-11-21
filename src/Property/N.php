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
final class N extends AdrNbase
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

    /**
     * @override
     * @param string|array $value  string (surname) or a 5-items array
     * @param null|string|array $givenNames
     * @param null|string $additionalNames
     * @param null|string $namePrefixes
     * @param null|string $nameSuffixes
     * @return PropertyInterface
     */
    public function setValue(
        $value,
        ? string $givenNames = null,
        ? string $additionalNames = null,
        ? string $namePrefixes = null,
        ? string $nameSuffixes = null
    ) : PropertyInterface
    {
        $this->value = self::conformInput( 5,  $value, $givenNames, $additionalNames, $namePrefixes, $nameSuffixes );
        return $this;
    }

    /**
     * @param mixed $value
     */
    protected static function getERRstr( $value ) : string
    {
        static $ERR = 'N expects surname/given/additional/prefix/suffix, got ' ;
        return $ERR . var_export( $value, true );
    }
}
