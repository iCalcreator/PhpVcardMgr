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
use Kigkonsult\PhpVcardMgr\Property\Gender as Dto;

/**
 * GENDER
 *
* the components of the sex and gender identity of the object the vCard represents
 *
 * Value type:  A single structured value with two components.  Each component has a single text value.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * GENDER-param = "VALUE=text" / any-param
 * GENDER-value = sex [";" text]
 * sex = "" / "M" / "F" / "O" / "N" / "U"
 */
final class Gender extends LoadBase
{
    /**
     * Use faker to populate new Gender
     *
     * @return Dto
     */
    public static function load() : Dto
    {
        $faker     = Faker\Factory::create();
        $valueType = $faker->randomElement( Dto::getAcceptedValueTypes());
        return Dto::factory(
            $faker->randomElement( [ '', 'M', 'F', 'O', 'N', 'U' ] ) . ';' . $faker->word(),
            self::loadParameters( Dto::getAcceptedParameterKeys(), $valueType, Dto::isAnyParameterAllowed()),
            $valueType
        );
    }
}
