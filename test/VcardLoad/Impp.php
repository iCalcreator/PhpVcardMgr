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
use Kigkonsult\FakerLocRelTypes\Provider\SchemaURI;
use Kigkonsult\PhpVcardMgr\Property\Impp as Dto;

/**
 * IMPP
 *
* the URI for instant messaging and presence protocol communications with the object the vCard represents
 *
 * Value type:  A single URI.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * IMPP-param = "VALUE=uri" / pid-param / pref-param / type-param / mediatype-param / altid-param / any-param
 * IMPP-value = URI
 */
final class Impp extends LoadBase
{
    /**
     * Use faker to populate new Impp
     *
     * @return Dto
     */
    public static function load() : Dto
    {
        $faker     = Faker\Factory::create();
        $faker->addProvider( new SchemaURI( $faker ));
        $valueType = $faker->randomElement( Dto::getAcceptedValueTypes());
        return Dto::factory(
            $faker->anyUri(),
            self::loadParameters(
                Dto::getAcceptedParameterKeys(),
                $valueType,
                Dto::isAnyParameterAllowed(),
                self::$MTmessage
            ),
            $valueType
        );
    }
}
