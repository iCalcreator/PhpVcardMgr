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
namespace Kigkonsult\PhpVcardMgr\Parser;

use Exception;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use XMLReader;

class XcardPropertyParser extends XcardParserBase
{
    /**
     * @inheritDoc
     * @param string $source property name
     * @throws Exception
     */
    public function parse( string $source ) : PropertyInterface
    {
        static $ARRAYPROPS1 = [ PropertyInterface::ADR, PropertyInterface::GENDER, PropertyInterface::N ];
        static $ARRAYPROPS2 = [ PropertyInterface::CLIENTPIDMAP, PropertyInterface::ORG ];
        static $ARRAYPROPS3 = [ PropertyInterface::CATEGORIES, PropertyInterface::NICKNAME ];
        $isArrayProp1 = in_array( $source, $ARRAYPROPS1, true );
        $isArrayProp2 = in_array( $source, $ARRAYPROPS2, true );
        $isArrayProp3 = in_array( $source, $ARRAYPROPS3, true );
        $isXmlProp    = ( PropertyInterface::XML === $source );
        $propNameLc   = strtolower( $source );
        $property     = self::newProperty( $source );
        $parameters   = [];
        $valueType    = $property::getAcceptedValueTypes( true ); // property default valueType
        $prevKey = $sep = $value = $vx = null;
        switch( true ) {
            case $isArrayProp1 :
                $value   = [];
                $vx      = -1;
                // fall through
            case $isArrayProp3 :
                $sep      = StringUtil::$COMMA;
                break;
            case $isArrayProp2 :
                $sep      = StringUtil::$SEMIC;
                break;
        } // end switch
        while( @$this->reader->read()) {
            switch( true ) {
                case ( XMLReader::SIGNIFICANT_WHITESPACE === $this->reader->nodeType ) :
                    break;
                case ( XMLReader::END_ELEMENT === $this->reader->nodeType ) :
                    if( $propNameLc === $this->reader->localName ) {
                        break 2; // break switch and while for propName
                    }
                    break;
                case ( XMLReader::ELEMENT === $this->reader->nodeType ) :
                    switch( true ) {
                        case ( self::XPARAMETERS === $this->reader->localName ) :
                            $parameters = $this->parseParameters();
                            break;
                        case $isArrayProp1  :
                            if( $prevKey !== $this->reader->localName ) {
                                $value[++$vx] = StringUtil::$SP0;
                                $prevKey      = $this->reader->localName;
                            } // fall through
                        case $isArrayProp2 :
                            $valueType = PropertyInterface::TEXT;
                            break;
                        default :
                            $valueType = $this->reader->localName;
                    } // end switch
                    break;
                case ( XMLReader::TEXT !== $this->reader->nodeType ) :
                    break;
                case $isArrayProp1 :
                    switch( true ) {
                        case ( ! $this->reader->hasValue ) :
                            break;
                        case empty( $value[$vx] ) :
                            $value[$vx] = $this->reader->value;
                            break;
                        default :
                            $value[$vx] .= StringUtil::$COMMA . $this->reader->value;
                            break;
                    } // end switch
                    break;
                case ( $isArrayProp2 || $isArrayProp3 ) :
                    $subValue = $this->reader->hasValue ? $this->reader->value : StringUtil::$SP0;
                    $value    = ( null === $value )
                        ? $subValue
                        : $value . $sep . $subValue;
                    break;
                default :
                    $value = $isXmlProp
                        ? htmlspecialchars_decode( $this->reader->value )
                        : $this->reader->value;
                    break;
            } // end switch
        } // end while
        if( ! empty( $valueType )) {
            $parameters[PropertyInterface::VALUE] = $valueType;
        }
        $property->setvalue( $value )
            ->setParameters( $parameters )
            ->setValueType( $valueType );
        return ( StringUtil::isXprefixed( $source ))
            ? $property->setPropName( $source )
            : $property;
    }

    /**
     * Parse property parameters
     *
     * $XMLTYPES MUST exist in XcardPropertyFormatter::writeParameters
     *
     * @return array
     * @throws Exception
     */
    private function parseParameters() : array
    {
        static $XMLTYPES = [ 'integer', self::LANGUAGETAG, self::XTEXT, self::URI ];
        static $LCLABEL  = 'label';
        $parameters      = [];
        $pKey            = null;
        while( @$this->reader->read()) {
            if( XMLReader::SIGNIFICANT_WHITESPACE === $this->reader->nodeType ) {
                continue;
            }
            if( XMLReader::END_ELEMENT === $this->reader->nodeType ) {
                if( self::XPARAMETERS === $this->reader->localName ) {
                    break;
                }
                continue;
            }
            if(( XMLReader::ELEMENT === $this->reader->nodeType ) &&
                ! in_array( $this->reader->localName, $XMLTYPES, true )) {
                $pKey = $this->reader->localName;
            }
            elseif( XMLReader::TEXT === $this->reader->nodeType ) {
                $parameters[$pKey] = isset( $parameters[$pKey] )
                    ? ( $parameters[$pKey] . StringUtil::$COMMA . $this->reader->value ) // type
                    : $this->reader->value;
            }
        } // end while
        if( isset( $parameters[$LCLABEL] )) {
            $parameters[$LCLABEL] = implode(
                StringUtil::$STREOL,
                StringUtil::convEolChar( $parameters[$LCLABEL], false )
            );
        }
        return $parameters;
    }
}
