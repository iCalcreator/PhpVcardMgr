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
namespace Kigkonsult\PhpVcardMgr\VcardLoad;

use Faker;
use Kigkonsult\PhpVcardMgr\Property\N as Dto;

/**
 * N
 *
* the components of the name of the object the vCard represents
 *
 * Value type:  A single structured text value.  Each component can have multiple values.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * N-param = "VALUE=text" / sort-as-param / language-param / altid-param / any-param
 * N-value = list-component 4(";" list-component)
 * Family Names (also known as surnames), Given Names, Additional Names, Honorific Prefixes, and Honorific Suffixes.
 * Individual text components can include multiple text values separated by the COMMA character (U+002C).
 */
final class N extends LoadBase
{
    /**
     * Use faker to populate new N
     *
     * @return Dto
     */
    public static function load() : Dto
    {
        $faker     = Faker\Factory::create();
        $valueType = $faker->randomElement( Dto::getAcceptedValueTypes());
        return Dto::factory(
            [
                $faker->firstName(),
                $faker->firstName(),
                $faker->lastName(),
                $faker->title(),
                $faker->suffix() . ',' . $faker->suffix(),
            ],
            self::loadParameters( Dto::getAcceptedParameterKeys(), $valueType, Dto::isAnyParameterAllowed()),
            $valueType
        );
    }
}
