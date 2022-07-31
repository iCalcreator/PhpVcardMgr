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
namespace Kigkonsult\PhpVcardMgr;

use Exception;
use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Property\Version;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

/**
 * Vcard
 *
 * A collection of vCard properties
 */
final class Vcard implements BaseInterface
{
    /**
     * @var string[]
     */
    private static $FIXEDPROPS = [ self::PRODID, self::VERSION ];

    /**
     * @var callable
     */
    private static $SORTER = [ __CLASS__, 'propSorter' ];


    /**
     * @var PropertyInterface[]
     */
    private $properties;

    /**
     * Class constructor
     * @throws Exception
     */
    public function __construct() {
        $this->initProperties();
    }

    /**
     * Class factory method
     *
     */
    public static function factory() : Vcard
    {
        return new self();
    }

    /**
     * Init Vcard for new properties
     *
     * @return void
     * @throws Exception
     */
    private function initProperties() : void
    {
        $this->properties =
            [
                Version::factory(),
                Prodid::factory(),
                Uid::factory( StringUtil::getNewUid())
            ];
    }

    /**
     * Return count properties, opt propNamed ones (if found, otherwise false)
     *
     * @param null|string $propName
     * @return bool|int
     */
    public function count( ? string $propName = null )
    {
        return empty( $propName )
            ? count( $this->properties )
            : $this->hasProperty( $propName );
    }

    /**
     * Return PropertyInterface[], all properties with group
     *
     * @param string $group
     * @return PropertyInterface[]
     */
    public function getGroupProperties( string $group ) : array
    {
        $output = [];
        foreach( $this->properties as $property ) {
            if( $property->isGroupSet() && ( $group === $property->getGroup())) {
                $output[] = $property;
            }
        } // end foreach
        return $output;
    }

    /**
     * Return array, groups
     *
     * @return string[]
     */
    public function getGroups() : array
    {
        $output = [];
        foreach( $this->properties as $property ) {
            if( $property->isGroupSet()) {
                $group  = $property->getGroup();
                $output[$group] = $group;
            }
        } // end foreach
        return array_values( $output );
    }

    /**
     * Return mixed|array, all properties or spec propNamed [with type] ones, opt only property [with type] values
     *
     * propname ANNIVERSARY, BDAY, GENDER, N, PRODID, REV, UID, VERSION returns mixed|PropertyInterface
     * All other returns array|PropertyInterface[]
     * Propname 'X-' returns all X-props
     * NOT found propName returns []
     *
     * @param null|string $propName
     * @param bool $valuesOnly
     * @param null|string $typeValue  only properties with TYPE=typeValue returned
     * @return mixed|PropertyInterface|PropertyInterface[]
     */
    public function getProperties(
        ? string $propName = null,
        ? bool $valuesOnly = false,
        ? string $typeValue = null
    )
    {
        if( empty( $propName )) {
            $this->sort();
            $isSingleprop = $getXprops = false;
        }
        else {
            $isSingleprop = self::isSingleOccurProp( $propName );
            $getXprops    = ( self::XPREFIX === $propName );
        }
        $output = [];
        foreach( $this->properties as $property ) {
            $propName2 = $property->getPropName();
            if( empty( $propName ) || ( $getXprops && StringUtil::isXprefixed( $propName2 ))) {
                if( ! empty( $typeValue ) && ! $property->hasTypeParameter( $typeValue )) {
                    continue;
                }
                $output[] = ( $valuesOnly ? $property->getValue() : $property );
            }
            elseif( ! $getXprops && ( $propName === $propName2 )) {
                if( ! empty( $typeValue ) && ! $property->hasTypeParameter( $typeValue )) {
                    continue;
                }
                if( $isSingleprop ) {
                    return ( $valuesOnly ? $property->getValue() : $property );
                }
                $output[] = ( $valuesOnly ? $property->getValue() : $property );
            }
        } // end foreach
        return $output;
    }

    /**
     * Return bool false if propName property [+type] not exists, otherwise count of propNamed [with type] properties
     *
     * Propname 'X-' return number of X-props, opt with valueType
     *
     * @param string $propName
     * @param null|string $typeValue
     * @return bool|int
     */
    public function hasProperty( string $propName, ? string $typeValue = null )
    {
        $count        = 0;
        $searchXprops = ( self::XPREFIX === $propName );
        foreach( $this->properties as $property ) {
            $propName2 = $property->getPropName();
            if(( $propName === $propName2 ) ||
                ( $searchXprops && StringUtil::isXprefixed( $propName2 ))) {
                if( ! empty( $typeValue ) && ! $property->hasTypeParameter( $typeValue )) {
                    continue;
                }
                $count++;
            }
        } // end foreach
        return ( 0 === $count ) ? false : $count;
    }

    /**
     * Remove all properties or spec propNamed ones
     *
     * @param null|string $propName
     * @return Vcard
     * @throws Exception
     */
    public function removeProperty( ? string $propName = null ) : Vcard
    {
        if( empty( $propName )) {
            $this->initProperties();
        }
        elseif( ! in_array( $propName, self::$FIXEDPROPS, true )) {
            foreach( $this->properties as $pix => $property ) {
                if( $propName === $property->getPropName()) {
                    unset( $this->properties[$pix] );
                }
            } // end foreach
        }
        return $this;
    }

    /**
     * Replace property/properties
     *
     * Single property input will remove opt existing multiple property occurrence
     *
     *
     * @param PropertyInterface|PropertyInterface[] $property
     * @return Vcard
     * @throws Exception
     */
    public function replaceProperty( $property ) : Vcard
    {
        static $ERR = 'PropertyInterface expected, got ';
        if( ! is_array( $property )) {
            $property = [ $property ];
        }
        elseif( 1 < count( $property )) {
            usort( $property, self::$SORTER );
        }
        $prevPropName = null;
        foreach( $property as $theProperty ) {
            if( ! $theProperty instanceof PropertyInterface ) {
                throw new InvalidArgumentException(
                    $ERR . ( is_object( $theProperty ) ? get_class( $theProperty ) : gettype( $theProperty ))
                );
            }
            $propName = $theProperty->getPropName();
            if( in_array( $propName, self::$FIXEDPROPS, true )) {
                continue;
            }
            if( $prevPropName !== $propName ) {
                $this->removeProperty( $propName );
                $prevPropName = $propName;
            }
            $this->addProperty( $theProperty );
        } // end foreach
        return $this;
    }

    /**
     * @param string $propName
     * @return bool
     */
    private static function isSingleOccurProp( string $propName ): bool
    {
        static $ONEPROPS = [
            self::ANNIVERSARY,
            self::BDAY,
            self::GENDER,
            self::N,
            self::PRODID,
            self::REV,
            self::UID,
            self::VERSION
        ];
        return in_array( $propName, $ONEPROPS, true ) ||
            StringUtil::isXprefixed( $propName );
    }

    /**
     * Add property to collection, opt 'replace' for X-/oneProps, otherwise last in chain
     *
     * @param PropertyInterface $property
     * @return Vcard
     */
    public function addProperty( PropertyInterface $property ) : Vcard
    {
        $propName = $property->getPropName();
        if( in_array( $propName, self::$FIXEDPROPS, true )) {
            return $this;
        }
        $propIx   = $this->count();
        if( self::isSingleOccurProp( $propName )) {
            foreach( array_keys( $this->properties ) as $propIx2 ) {
                if( $propName === $this->properties[$propIx2]->getPropName()) {
                    $propIx = $propIx2;
                    break;
                }
            } // end foreach
        }
        $this->properties[$propIx] = $property;
        return $this;
    }

    /**
     * Add properties to collection

     * @param PropertyInterface[] $properties
     * @return Vcard
     */
    public function setProperties( array $properties ) : Vcard
    {
        foreach( $properties as $property ) {
            $this->addProperty( $property );
        }
        return $this;
    }

    /**
     * Group properties on 1. (group + ) propname 2. param PREFS
     *
     * @return void
     */
    private function sort() : void
    {
        $props = [];
        foreach( $this->properties as $property ) {
            switch( true ) {
                case $property->isGroupSet() :
                    $keyName = $property->getGroup();
                    break;
                case StringUtil::isXprefixed( $property->getPropName()) :
                    $keyName = self::XPREFIX;
                    break;
                default :
                    $keyName = $property->getPropName();
            }
            if( ! isset( $props[$keyName] )) {
                $props[$keyName] = [];
            }
            $props[$keyName][] = $property;
        } // end foreach
        $output = [];
        foreach( $props as $propGroup ) {
            usort( $propGroup, self::$SORTER );
            foreach( $propGroup as $property ) {
                $output[] = $property;
            }
        } // end foreach
        $this->properties = $output;
    }

    /**
     * @param PropertyInterface $a
     * @param PropertyInterface $b
     * @return int
     */
    private static function propSorter( PropertyInterface $a, PropertyInterface $b ) : int
    {
        $aGroup = $a->getGroup() ?? StringUtil::$SP0;
        $bGroup = $b->getGroup() ?? StringUtil::$SP0;
        if( $aGroup < $bGroup ) {
            return -1;
        }
        if( $aGroup > $bGroup ) {
            return 1;
        }
        $aName    = $a->getPropName();
        $bName    = $b->getPropName();
        if( $aName < $bName ) {
            return -1;
        }
        if( $aName > $bName ) {
            return 1;
        }
        if( 0 === ((int) $aPref = $a->getParameters( $a::PREF ))) {
            $aPref = PHP_INT_MAX;
        }
        if( 0 === ((int) $bPref = $b->getParameters( $a::PREF ))) {
            $bPref = PHP_INT_MAX;
        }
        if( $aPref < $bPref ) {
            return -1;
        }
        if( $aPref > $bPref ) {
            return 1;
        }
        return 0;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $output = StringUtil::$SP0;
        foreach( $this->getProperties() as $property ) {
            $output .= $property->__toString();
        }
        return $output;
    }
}
