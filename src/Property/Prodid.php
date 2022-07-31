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

use Kigkonsult\PhpVcardMgr\PhpVcardMgr;

/**
 * PRODID
 *
* the identifier for the product that created the  vCard object
 *
 * Type value:  A single text value.
 * Cardinality:  *1, Exactly one instance per vCard MAY be present.
 *
 * PRODID-param = "VALUE=text" / any-param  param ignored
 * PRODID-value = text
 */
final class Prodid extends PropertyPV
{
    /**
     * Class constructor
     *
     * @override
     */
    public function __construct(
        ? string $value = null,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) {
        static $FMT      = '-//NONSGML kigkonsult.se %s//';
        $this->value     = sprintf( $FMT, PhpVcardMgr::PVM_VERSION );
        $this->valueType = self::getAcceptedValueTypes( true );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::PRODID;
    }
}
