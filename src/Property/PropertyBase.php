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
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

abstract class PropertyBase implements PropertyInterface
{
    /**
     * Return property (vcard) name
     *
     * @return string
     */
    abstract public function getPropName() : string ;

    /**
     * Property group
     *
     * @var null|string
     */
    protected $group = null;

    /**
     * Property parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Property valuetype
     *
     * @var string
     */
    protected $valueType;

    /**
     * Property value
     *
     * @var mixed
     */
    protected $value;

    /**
     * @param int|float|string|array|DateTime $value
     * @param null|array $parameters
     * @param null|string $valueType
     * @param null|string $group
     * ·@return void
     * @throws InvalidArgumentException
     */
    protected function populate(
        $value,
        ? array $parameters = [],
        ? string $valueType = null,
        ? string $group = null
    ) : void
    {
        if( ! empty( $group )) {
            $this->setGroup( $group );
        }
        if( ! empty( $parameters )) {
            $this->setParameters( $parameters );
        }
        switch( true ) {
            case ! empty( $valueType ) :
                break;
            case $this->hasValueParameter() :
                $valueType = (string) $this->getParameters( self::VALUE );
                break;
            default :
                $valueType = static::getAcceptedValueTypes( true );
                break;
        } // end switch
        $this->setValueType( $valueType );
        $this->setValue( $value );
    }

    /**
     * @inheritDoc
     */
    public function getGroup() : ? string
    {
        return $this->group;
    }

    /**
     * @inheritDoc
     */
    public function isGroupSet() : bool
    {
        return ( null !== $this->group );
    }

    /**
     * @param null $group
     * @return static
     */
    public function setGroup( $group ) : PropertyInterface
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGroupPropName() : string
    {
        $propName   = $this->getPropName();
        return $this->isGroupSet() ? $this->getGroup() . StringUtil::$DOT . $propName : $propName;
    }

    /**
     * Return all parameters as array or spec parameter key value (false if not found)
     *
     * @param null|string $key
     * @return bool|string|array
     */
    public function getParameters( ? string $key = null )
    {
        if( null !== $key ) {
            return $this->hasParameter( $key ) ? $this->parameters[strtoupper( $key )] : false; 
        }
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function hasParameter( string $key ) : bool
    {
        return isset( $this->parameters[strtoupper( $key )] );
    }

    /**
     * @return bool
     */
    public function hasValueParameter() : bool
    {
        return isset( $this->parameters[self::VALUE] );
    }

    /**
     * @param null|string $typeValue
     * @return bool
     */
    public function hasTypeParameter( ? string $typeValue = null) : bool
    {
        return ( isset( $this->parameters[self::TYPE] ) &&
            ( empty( $typeValue ) || in_array( $typeValue, $this->parameters[self::TYPE], true ) ));
    }

    /**
     * @inheritDoc
     */
    public function isParametersSet() : bool
    {
        return ! empty( $this->parameters );
    }

    /**
     * @inheritDoc
     */
    abstract public static function getAcceptedParameterKeys() : array;

    /**
     * Return bool true if iana- or X-prefixed parameters are allowed
     *
     * @return bool
     */
    abstract public static function isAnyParameterAllowed(): bool;

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function addParameter( string $key, $value ) : PropertyInterface
    {
        static $ERR1 = 'Rejected parameter key : ';
        static $ERR2 = 'Expect scalar or array parameter value, got ';
        static $NoTypeProps = [
            self::ANNIVERSARY,
            self::BDAY,
            self::CLIENTPIDMAP,
            self::FN,
            self::GENDER,
            self::KIND,
            self::MEMBER,
            self::N,
            self::REV,
            self::SOURCE,
            self::TZ,
            self::UID,
            self::XML
        ];
        $key = strtoupper( $key );
        if( ! static::isAnyParameterAllowed() &&
            ! in_array( $key, static::getAcceptedParameterKeys(), true )) {
            throw new InvalidArgumentException( $ERR1 . $key );
        }
        if( ! is_scalar( $value ) && ! is_array( $value )) {
            throw new InvalidArgumentException( $ERR2 . gettype( $value ));
        }
        if( is_string( $value )) {
            $value = trim( $value, StringUtil::$QQ );
        }
        if( ( self::PID === $key ) &&
            is_string( $value ) &&
            ( false !== strpos( $value, StringUtil::$COMMA ))) {
            $value = explode( StringUtil::$COMMA, $value );
        }
        switch( true ) {
            case (( self::SORT_AS === $key ) && is_array( $value )):
                $value = explode( StringUtil::$COMMA, $value );
                break;
            case ( self::TYPE !== $key ) :
                break;
            case in_array( $this->getPropName(), $NoTypeProps, true ) :
                return $this; // skip TYPE
            case ! is_array( $value ) :
                $value = explode( StringUtil::$COMMA, $value );
                break;
        } // end swich
        if(( self::VALUE === $key ) &&
            in_array( strtolower( $value ), static::getAcceptedValueTypes(), true )) {
            $value = strtolower( $value );
        }
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function setParameters( array $parameters ) : PropertyInterface
    {
        foreach( $parameters as $key => $value ) {
            $this->addParameter( $key, $value );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsetParameter( string $key ) : PropertyInterface
    {
        unset( $this->parameters[strtoupper( $key )] );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedValueTypes( ? bool $default = false )
    {
        return $default ? self::TEXT : [ self::TEXT ];
    }

    /**
     * @inheritDoc
     */
    public function getValueType() : ? string
    {
        return $this->valueType;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function setValueType( string $valueType ) : PropertyInterface
    {
        static $ERR1 = 'Unknown value type \'%s\', %s expected for %s';
        $acceptedValueTypes = static::getAcceptedValueTypes();
        if( ! in_array( $valueType, $acceptedValueTypes, true )) {
            throw new InvalidArgumentException(
                sprintf(
                    $ERR1,
                    $valueType,
                    implode( StringUtil::$COMMA, $acceptedValueTypes ),
                    $this->getPropName()
                )
            );
        }
        $this->valueType = in_array( strtolower( $valueType ), static::getAcceptedValueTypes(), true )
            ? strtolower( $valueType )
            : $valueType;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isValueSet() : bool
    {
        return ( null !== $this->value );
    }
    
    /**
     * @inheritDoc
     */
    public function setValue( $value ) : PropertyInterface
    {
        $this->value = self::trimSub( $value );
        return $this;
    }

    /**
     * @param string|string[] $value
     * @return string|string[]
     */
    protected static function trimSub( $value )
    {
        return is_array( $value )
            ? array_map(
                static function( $g ) {
                    if( ! is_string( $g )) {
                        return $g;
                    }
                    return empty( $g ) ? StringUtil::$SP0 : trim( $g );
                },
                $value
            )
            : $value;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        static $WORDs = [
            '--CLASS--      : ',
            'propName       : ',
            'Acc parKeys    : ',
            'parameter      : ',
            ' => ',
            '-',
            'Acc valueTypes : ',
            'valueType      : ',
            'value          : '
        ];
        $output   = [ $WORDs[0] . get_class( $this ) ];
        $output[] = $WORDs[1] . $this->getGroupPropName();
        $output[] = $WORDs[2] . implode( StringUtil::$COMMA, static::getAcceptedParameterKeys());
        if( $this->isParametersSet()) {
            foreach( $this->parameters as $pKey => $pValue ) {
                $output[] = $WORDs[3] . str_pad( $pKey, 10 ) . $WORDs[4] .
                    ( is_array( $pValue ) ? implode( StringUtil::$COMMA, $pValue ) : $pValue );
            }
        }
        else {
            $output[] = $WORDs[3] . $WORDs[5];
        }
        $output[] = $WORDs[6] . implode( StringUtil::$COMMA, static::getAcceptedValueTypes());
        $output[] = $WORDs[7] . $this->getValueType();
        $value    = $this->getValue();
        $output[] = $WORDs[8] . ( is_array( $value ) ? implode( StringUtil::$SEMIC, $value ) : $value );
        return implode( PHP_EOL, $output ) . PHP_EOL;
    }
}
