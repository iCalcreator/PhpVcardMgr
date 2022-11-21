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

use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

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
final class ClientPidMap extends PropertyBase
{
    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::CLIENTPIDMAP;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function isAnyParameterAllowed() : bool
    {
        return true;
    }

    /**
     * @override
     * @param int|string|array $value   i.e. pid
     * @param null|string $uri
     * @return static
     * @throws InvalidArgumentException
     */
    public function setValue( $value, ? string $uri = null ) : PropertyInterface
    {
        static $ERR = 'ClientPidMap expect (int) pid and (string) uri, got pid %s and uri %s';
        if( is_string( $value ) && StringUtil::containsSemic( $value )) {
            [ $value, $uri ] = StringUtil::semicSplit( $value, 2 );
        }
        elseif( is_array( $value )) {
            [ $value, $uri ] = $value;
        }
        if( empty( $uri ) ||
            ! is_string( $uri ) ||
            ! ctype_digit( trim((string) $value ))) {
            throw new InvalidArgumentException(
                sprintf( $ERR, var_export( $value, true ), var_export( $uri, true ))
            );
        }
        $this->value  = self::trimSub( [ (int) $value, $uri ] );
        return $this;
    }
}
