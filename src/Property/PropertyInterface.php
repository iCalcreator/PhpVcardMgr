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

use Kigkonsult\PhpVcardMgr\BaseInterface;

interface PropertyInterface extends BaseInterface
{
    /**
     * Return property (vcard) name
     *
     * @return string
     */
    public function getPropName() : string;

    /**
     * @return null|string
     */
    public function getGroup() : ? string;

    /**
     * Return bool true if group is set
     *
     * @return bool
     */
    public function isGroupSet() : bool;

    /**
     * @param string $group
     * @return static
     */
    public function setGroup( string $group ) : PropertyInterface;

    /**
     * Return [group '.' ] property name
     *
     * @return string
     */
    public function getGroupPropName() : string;

    /**
     * Return all accepted parameter keys, opt iana or x-prefixed keys NOT included
     *
     * @return array
     */
    public static function getAcceptedParameterKeys() : array;

    /**
     * Return all parameters as array or spec parameter key value (false if not found)
     *
     * @param null|string $key
     * @return bool|string|array
     */
    public function getParameters(  ? string $key = null );

    /**
     * Return bool true if parameter key is set
     *
     * @param string $key
     * @return bool
     */
    public function hasParameter( string $key ) : bool;

    /**
     * @return bool
     */
    public function hasValueParameter() : bool;

    /**
     * @return bool
     */
    public function hasTypeParameter() : bool;

    /**
     * Return bool true if parameters is set
     *
     * @return bool
     */
    public function isParametersSet() : bool;

    /**
     * Add parameter, if exists, replace
     *
     * For some properties, the TYPE parameter is (silently) skiped, otherwise forced into array
     *
     * @param string $key
     * @param int|string|array $value
     * @return static
     */
    public function addParameter( string $key, $value ) : PropertyInterface;

    /**
     * Set array of parameters
     *
     * @param array $parameters
     * @return static
     */
    public function setParameters( array $parameters ) : PropertyInterface;

    /**
     * Remove parameter
     *
     * @param string $key
     * @return static
     */
    public function unsetParameter( string $key ) : PropertyInterface;


    /**
     * Return all (array) or the default one (string)
     *
     * @param null|bool $default
     * @return string|string[]
     */
    public static function getAcceptedValueTypes( ? bool $default = false );

    /**
     * Return type of property value
     *
     * @return null|string
     */
    public function getValueType() : ? string;

    /**
     * Set type of property value
     *
     * @param string $valueType
     * @return static
     */
    public function setValueType( string $valueType ) : PropertyInterface;

    /**
     * Return property value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return bool true if value is set
     *
     * @return bool
     */
    public function isValueSet() : bool;

    /**
     * Set property value
     *
     * @param mixed $value
     * @return static
     */
    public function setValue( $value ) : PropertyInterface;
}
