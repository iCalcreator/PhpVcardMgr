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
use Kigkonsult\PhpVcardMgr\Property\Tel as Dto;

/**
 * TEL
 *
* the telephone number for telephony communication with the object the vCard represents
 *
 * Value type:  By default, it is a single free-form text value (for backward compatibility with vCard 3),
 * but it SHOULD be reset to a URI value.
 * It is expected that the URI scheme will be "tel", as specified in [RFC3966], but other schemes MAY be used.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * TEL-param = TEL-text-param / TEL-uri-param
 * TEL-value = TEL-text-value / TEL-uri-value
 * ; Value and parameter MUST match.
 *
 * TEL-text-param = "VALUE=text"
 * TEL-text-value = text
 *
 * TEL-uri-param = "VALUE=uri" / mediatype-param
 * TEL-uri-value = URI
 *
 * TEL-param =/ type-param / pid-param / pref-param / altid-param / any-param
 *
 * type-param-tel = "text" / "voice" / "fax" / "cell" / "video" / "pager" / "textphone" / iana-token / x-name
 * ; type-param-tel MUST NOT be used with a property other than TEL.
 */
final class Tel extends LoadBase
{
    /**
     * Use faker to populate new Tel
     *
     * @return Dto
     */
    public static function load() : Dto
    {
        $faker     = Faker\Factory::create();
        $faker->addProvider( new SchemaURI( $faker ));
        $valueType = $faker->randomElement( Dto::getAcceptedValueTypes());
        return Dto::factory(
            (( Dto::URI === $valueType ) ? $faker->telUri() : $faker->phoneNumber()),
            self::loadParameters(
                Dto::getAcceptedParameterKeys(),
                $valueType, Dto::isAnyParameterAllowed(),
                self::$MTmessage
            ),
            $valueType
        );
    }
}
