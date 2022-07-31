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

abstract class PropertyPV extends PropertyBase
{
    /**
     * @override
     */
    public function getGroup() : ? string
    {
        return null;
    }

    /**
     * @override
     */
    public function isGroupSet() : bool
    {
        return false;
    }

    /**
     * @override
     */
    public function setGroup( string $group ) : PropertyInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGroupPropName() : string
    {
        return $this->getPropName();
    }

    /**
     * @override
     */
    public function getParameters( ? string $key = null ) : array
    {
        return [];
    }

    /**
     * @override
     */
    public function hasParameter( ? string $key = null ) : bool
    {
        return false;
    }

    /**
     * @override
     */
    public function hasValueParameter() : bool
    {
        return false;
    }

    /**
     * @override
     */
    public function hasTypeParameter( ? string $typeValue = null) : bool
    {
        return false;
    }

    /**
     * @override
     */
    public function isParametersSet() : bool
    {
        return false;
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
        return false;
    }

    /**
     * @override
     */
    public function addParameter( string $key, $value ) : PropertyInterface
    {
        return $this;
    }

    /**
     * @override
     */
    public function setParameters( array $parameters ) : PropertyInterface
    {
        return $this;
    }

    /**
     * @override
     */
    public function unsetParameter( string $key ) : PropertyInterface
    {
        return $this;
    }

    /**
     * @override
     */
    public function getValueType() : ? string
    {
        return self::TEXT;
    }

    /**
     * @override
     */
    public function setValueType( string $valueType ) : PropertyInterface
    {
        return $this;
    }

    /**
     * @override
     */
    public function setValue( $value ) : PropertyInterface
    {
        return $this;
    }
}
