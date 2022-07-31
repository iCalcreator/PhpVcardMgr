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
 * VERSION
 *
* the version of the vCard specification used to format this vCard
 *
 * Value type:  A single text value.
 * Cardinality:  1, Exactly one instance per vCard MUST be present.
 *
 * The value MUST be "4.0"
 *
 * VERSION-param = "VALUE=text" / any-param
 * VERSION-value = "4.0"
 */
final class Version extends PropertyPV
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
        $this->value     = self::VERSION4;
        $this->valueType = self::getAcceptedValueTypes( true );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::VERSION;
    }
}
