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
 * RELATED
 *
* a relationship between another entity and the entity represented by this vCard
 *
 * Value type:  A single URI.  It can also be reset to a single text value.  The text value can be used to specify textual information.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * RELATED-param = RELATED-param-uri / RELATED-param-text
 * RELATED-value = URI / text
 * ; Parameter and value MUST match.
 *
 * RELATED-param-uri = "VALUE=uri" / mediatype-param
 * RELATED-param-text = "VALUE=text" / language-param
 *
 * RELATED-param =/ pid-param / pref-param / altid-param / type-param / any-param
 *
 * type-param-related = related-type-value *("," related-type-value)
 * ; type-param-related MUST NOT be used with a property other than
 * ; RELATED.
 *
 * related-type-value = "contact" / "acquaintance" / "friend" / "met"
 *                      / "co-worker" / "colleague" / "co-resident"
 *                      / "neighbor" / "child" / "parent"
 *                      / "sibling" / "spouse" / "kin" / "muse"
 *                      / "crush" / "date" / "sweetheart" / "me"
 *                      / "agent" / "emergency"
 */
final class Related extends PropertyBase
{
    /**
     * Class constructor
     *
     * @param string $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     */
    public function __construct( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) {
        $this->populate( $value, $parameters, $valueType, $group );
    }

    /**
     * Class factory method
     *
     * @param string $value
     * @param null|array $parameters
     * @param string|null $valueType
     * @param null|string $group
     * @return Related
     */
    public static function factory( 
        string $value, 
        ? array $parameters = [], 
        ? string $valueType = null, 
        ? string $group = null
    ) : Related
    {
        return new self( $value, $parameters, $valueType, $group );
    }

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::RELATED;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::MEDIATYPE,
            self::LANGUAGE,
            self::PID,
            self::PREF,
            self::ALTID,
            self::TYPE,
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
     * Value types, URI, may be reset to TEXT
     *
     * @override
     */
    public static function getAcceptedValueTypes( ? bool $default = false )
    {
        return $default ? self::URI : [ self::URI, self::TEXT ];
    }
}