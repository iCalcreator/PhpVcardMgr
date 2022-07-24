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
use Kigkonsult\PhpVcardMgr\Property\Bday as Dto;

/**
 * BDAY, birth date of the object the vCard represents
 *
 * Value type:  The default is a single date-and-or-time value.  It can also be reset to a single text value.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * BDAY-param = BDAY-param-date / BDAY-param-text
 * BDAY-value = date-and-or-time / text
 * ; Value and parameter MUST match.
 *
 * BDAY-param-date = "VALUE=date-and-or-time"
 * BDAY-param-text = "VALUE=text" / language-param
 *
 * BDAY-param =/ altid-param / calscale-param / any-param
 * ; calscale-param can only be present when BDAY-value is
 * ; date-and-or-time and actually contains a date or date-time.
 */
final class Bday extends LoadBase
{
    /**
     * Use faker to populate new Bday
     *
     * @param null|bool $datetimeDateOnly
     * @return Dto
     */
    public static function load( ? bool $datetimeDateOnly = false ) : Dto
    {
        $faker     = Faker\Factory::create();
        $valueType = $datetimeDateOnly
            ? $faker->randomElement( [ self::DATETIME, self::DATE ] )
            : $faker->randomElement( Dto::getAcceptedValueTypes());
        switch( $valueType ) {
            case self::DATE :
                $value = $faker->dateTime()->format( 'Ymd' );
                break;
            case self::TIME :
                $value = $faker->dateTime()->format( 'HisO' );
                break;
            case self::DATEANDORTIME : // fall through
            case self::TEXT :          // fall through
            default :
                $dateTime = $faker->dateTime();
                $value    = $faker->boolean()
                    ? str_replace( [ '-', ':' ],'', $dateTime->format( 'c' ))
                    : $dateTime;
                break;
        } // end switch
        return Dto::factory(
            $value,
            self::loadParameters( Dto::getAcceptedParameterKeys(), $valueType, Dto::isAnyParameterAllowed()),
            $valueType
        );
    }
}
