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

use DateTime;
use InvalidArgumentException;

/**
 * BDAY
 *
 * birth date of the object the vCard represents
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
 *   calscale-param can only be present when BDAY-value is
 *   date-and-or-time and actually contains a date or date-time.
 */
final class Bday extends PropertyDate
{
    /**
     * Class constructor
     *
     * @param string|DateTime $value  Ymd-string or DateTime
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @throws InvalidArgumentException
     */
    public function __construct(
        $value,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) {
        $this->populate( $value, $parameters, $valueType, $group );
    }

    /**
     * Class factory method
     *
     * @param string|DateTime $value  Ymd-string or DateTime
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * @return Bday
     * @throws InvalidArgumentException
     */
    public static function factory( 
        $value,
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : Bday
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::BDAY;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::ALTID,
            self::CALSCALE,
            self::LANGUAGE,
        ];
    }
}