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
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap as Dto;

/**
 * CLIENTPIDMAP
 *
 * give a global meaning to a local PID source identifier
 *
 * Value type:  A semicolon-separated pair of values.
 *              The first field is a small integer corresponding to the second field of a PID parameter instance.
 *              The second field is a URI.
 *              The "uuid" URN namespace defined in [RFC4122] is particularly well suited to this task,
 *              but other URI schemes MAY be used.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * CLIENTPIDMAP-param = any-param
 * CLIENTPIDMAP-value = 1*DIGIT ";" URI
 */
final class ClientPidMap extends LoadBase
{
    /**
     * Use faker to populate new ClientPidMap
     *
     * @param null|int $loadType
     * @return Dto
     */
    public static function load( ? int $loadType = 0 ) : Dto
    {
        $faker     = Faker\Factory::create();
        $faker->addProvider( new SchemaURI( $faker ));
        $valueType = $faker->randomElement( Dto::getAcceptedValueTypes());
        $pid       = $faker->randomDigitNotNull;
        $uri       = $faker->urnUuidUri();
        return Dto::factory(
            (( 0 === $loadType ) ? $pid . ';' . $uri : [ $pid, $uri ] ),
            self::loadParameters( Dto::getAcceptedParameterKeys(), $valueType, Dto::isAnyParameterAllowed()),
            $valueType
        );
    }
}
