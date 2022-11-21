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
use Kigkonsult\PhpVcardMgr\Property\Anniversary;
use Kigkonsult\PhpVcardMgr\Property\CalAdrUri;
use Kigkonsult\PhpVcardMgr\Property\CalUri;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Fburl;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\Impp;
use Kigkonsult\PhpVcardMgr\Property\Kind;
use Kigkonsult\PhpVcardMgr\Property\Lang;
use Kigkonsult\PhpVcardMgr\Property\Member;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Property\Related;
use Kigkonsult\PhpVcardMgr\Property\Xml;
use Kigkonsult\PhpVcardMgr\Property\Xprop;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;

class Vcard3Parser extends VcardParserBase
{
    /**
     * @param string[] $vCardRows
     * @return Vcard
     * @throws Exception
     * @throws RuntimeException
     */
    public function vCardParse( array $vCardRows ) : Vcard
    {
        static $ERR        = 'expect VERSION 3.0, got ';
        static $PROFILE    = 'PROFILE';
        static $SORTSTRING = 'SORT-STRING';
        static $TRIMCHARS  = "\x00..\x1F";
        $rows  = self::concatRows( $vCardRows );
        $vCard = new Vcard();
        $nIx = $sortAs = null;
        $properties = [];
        foreach( $rows as $rix => $row ) {
            /* separate property name  and  opt.params and value */
            [ $group, $propName, $row ] = self::getPropName( $row );
            $property   = self::newProperty( $propName );
            if( ! empty( $group )) {
                $property->setGroup( $group );
            }
            /* separate parameters from value */
            [ $value, $parameters ] = self::splitContent( $row );
            $parameters = self::prepParameters( $parameters );
            $valueType = $parameters[self::VALUE] ?? $property::getAcceptedValueTypes( true );
            $property->setParameters( $parameters );
            $property->setValueType( $valueType );
            if(( isset( $parameters[self::VALUE] ) && ( self::TEXT === $parameters[self::VALUE] )) ||
                ( self::TEXT === $valueType )) {
                $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
            }
            switch( $propName ) {
                case self::VERSION :
                    if( self::VERSION3 !== $value ) {
                        throw new RuntimeException( $ERR . var_export( $value, true ));
                    }               // fall through
                case self::PRODID : // fall through
                case $PROFILE :
                    continue 2;
                case $SORTSTRING :
                    $sortAs = $value;
                    continue 2;
                case self::ADR :
                case self::CATEGORIES : // fall through
                case self::EMAIL :      // fall through
                case self::FN :         // fall through
                case self::NICKNAME :   // fall through
                case self::NOTE :       // fall through
                case self::ORG :        // fall through
                case self::ROLE :       // fall through
                case self::SOURCE :     // fall through
                case self::TEL :        // fall through
                case self::TITLE :      // fall through
                case self::URL :
                    $property->setValue( $value );
                    break;
                case self::N :
                    $property->setValue( $value );
                    $nIx = $rix;
                    break;
                case self::BDAY :
                    self::processBday( $property, $value );
                    break;
                case self::GEO :
                    self::processGeo( $property, $value );
                    break;
                case self::KEY :
                    self::processKey( $property, $value );
                    break;
                case self::LOGO :  // fall through
                case self::PHOTO : // fall through
                case self::SOUND : // fall through
                case self::UID :   // fall through
                    self::processUri( $property, $value );
                    break;

                case self::REV :
                    self::processRev( $property, $value );
                    break;
                case self::TZ :
                    self::processTz( $property, $value );
                    break;

                default :
                    $property = self::processXprop( $property, $propName, $value );
                    break;
            } // end switch
            $properties[$rix] = $property;
        } // end foreach
        if( ! empty( $nIx ) && ! empty( $sortAs )) {
            // insert opt sort-as param in N property
            $properties[$nIx]->addParameter( self::SORT_AS, $sortAs );
        }
        $vCard->setProperties( $properties );
        return $vCard;
    }

    /**
     * Set Vcard3 spec parameter keys as x-keys
     *
     * @param string[]  $parameters
     * @return string[]
     */
    private static function prepParameters( array $parameters ) : array
    {
        static $specPkeys  = [ 'CHARSET', 'CONTEXT', 'ENCODING' ];   // Vcard3 param keys only
        static $ADRNOTYPES = [ 'intl', 'dom', 'postal', 'parcel' ]; // Vcard3 types only
        $parameters = array_change_key_case( $parameters, CASE_UPPER );
        if( isset( $parameters[self::TYPE] )) {
            $types = explode( StringUtil::$COMMA, $parameters[self::TYPE] );
            foreach( $types as $tix => $type ) {
                if( in_array( $type, $ADRNOTYPES, true )) {
                    unset( $types[$tix] );
                }
            }
            if( empty( $types )) {
                unset( $parameters[self::TYPE] );
            }
            else {
                $parameters[self::TYPE] = implode( StringUtil::$COMMA, $types );
            }
        } // end if
        foreach( $specPkeys as $specPkey ) {
            if( ! isset( $parameters[$specPkey] )) {
                continue;
            }
            $parameters[self::XPREFIX . $specPkey] = $parameters[$specPkey];
            unset( $parameters[$specPkey] );
        } // end foreach
        return $parameters;
    }

    /**
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     */
    private static function processBday( PropertyInterface $property, string $value ) : void
    {
        $value = trim( str_replace( [ DateUtil::$DS1, StringUtil::$COLON ], StringUtil::$SP0, $value ));
        $hasParameterValue = $property->hasValueParameter();
        switch( true ) {
            case ( $hasParameterValue && ( self::DATE === $property->getParameters( self::VALUE ))) :
                $property->setValueType( self::DATE );
                break;
            case ( $hasParameterValue && ( self::DATETIME === $property->getParameters( self::VALUE ))) :
                $property->setValueType( self::DATETIME );
                break;
            case (( 8 === strlen( $value )) && DateUtil::isVcardDate( $value )) :
                $property->addParameter( self::VALUE, self::DATE )
                    ->setValueType( self::DATE );
                break;
            default :
                $property->addParameter( self::VALUE, self::DATETIME )
                    ->setValueType( self::DATETIME );
                break;
        } // end switch
        $property->setValue( $value );
    }

    /**
     * A single structured value consisting of two float values separated by the SEMI-COLON character
     *
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     */
    private static function processGeo( PropertyInterface $property, string $value ) : void
    {
        static $GEOprefix = 'geo:';
        if( StringUtil::containsSemic( $value )) {
            $value = $GEOprefix . implode( StringUtil::$COMMA, explode( StringUtil::$SEMIC, $value ));
        }
        $property->setValue( $value );
    }

    /**
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     */
    private static function processKey( PropertyInterface $property, string $value ) : void
    {
        $valueType = ( $property->hasValueParameter() &&
            ( self::TEXT === $property->getParameters( self::VALUE )))
            ? self::TEXT
            : self::URI;
        $property->setValueType( $valueType )
            ->setValue( $value );
    }

    /**
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     */
    private static function processUri( PropertyInterface $property, string $value ) : void
    {
        $property->setValueType( self::URI ) // the only one allowed
            ->setValue( $value );
    }

    /**
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     * @throws Exception
     */
    private static function processRev( PropertyInterface $property, string $value ) : void
    {
        static $DATEHISSFX = 'T000000';
        $value      = str_replace( [ DateUtil::$DS1, StringUtil::$COLON ], StringUtil::$SP0, $value );
        if( DateUtil::isVcardDate( $value ) && ! DateUtil::isVcardDateTime( $value )) {
            $value .= $DATEHISSFX;
        }
        $property->unsetParameter( self::VALUE )
            ->setValueType( self::TIMESTAMP )
            ->setValue( $value );
    }

    /**
     * Vcard3 :
     * The default is a single utc-offset value.
     * It can also be reset to a single text value.
     * Vcard4 :
     * The default is a single text value.
     * It can also be reset to a single URI or utc-offset value.
     *
     * @param PropertyInterface $property
     * @param string $value
     * @return void
     */
    private static function processTz( PropertyInterface $property, string $value ) : void
    {
        $hasParameterValue = $property->hasValueParameter();
        if( ! $hasParameterValue && DateUtil::isJcardZone( $value )) {
            $property->addParameter( self::VALUE, self::UTCOFFSET )
                ->setValueType( self::UTCOFFSET );
        }
        elseif( ! $hasParameterValue ) {
            $property->addParameter( self::VALUE, self::TEXT )
                ->setValueType( self::TEXT );
        }
        else {
            $property->setValueType( $property->getParameters( self::VALUE ));
        }
        if(( self::UTCOFFSET === $property->getParameters( self::VALUE )) &&
            ( false !== strpos( $value, StringUtil::$COLON ))) {
            $value = str_replace( StringUtil::$COLON, StringUtil::$SP0, $value  );
        }
        $property->setValue( $value );
    }

    /**
     * Process Vcard3 X-props, first looking for Vcard4-unique ones
     *
     * @param PropertyInterface $property   an Xprop with propName 'X-'
     * @param string      $propName
     * @param string      $value
     * @return PropertyInterface
     */
    private static function processXprop(
        PropertyInterface $property,
        string $propName,
        string $value
    ) : PropertyInterface
    {
        switch( substr( $propName, 2 ) ) {
            case self::ANNIVERSARY :
                return Anniversary::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::CALADRURI :
                return CalAdrUri::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::CALURI :
                return CalUri::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::CLIENTPIDMAP :
                return ClientPidMap::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::FBURL :
                return Fburl::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::GENDER :
                return Gender::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::IMPP :
                return Impp::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::KIND :
                return Kind::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::LANG :
                return Lang::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::MEMBER :
                return Member::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::RELATED :
                return Related::factory( $value, $property->getParameters(), null, $property->getGroup());
            case self::XML :
                return Xml::factory( $value, $property->getParameters(), null, $property->getGroup());
            default :
                break;
        } // end switch
        return self::otherXprop( $property, $propName, $value );
    }

    /**
     * @param PropertyInterface $property
     * @param string $propName
     * @param string $value
     * @return PropertyInterface
     */
    private static function otherXprop(
        PropertyInterface $property,
        string $propName,
        string $value
    ) : PropertyInterface
    {
        $property->setPropName( $propName );
        $property->setValueType(
            ( $property->hasValueParameter() &&
                in_array( $property->getParameters( self::VALUE ), Xprop::getAcceptedValueTypes(), true ))
                ? $property->getParameters( self::VALUE )
                : self::TEXT
        );
        if( ! Xprop::isAnyParameterAllowed()) {
            $allowedPkeys = Xprop::getAcceptedParameterKeys();
            foreach( $property->getParameters() as $pKey => $pValue ) {
                if( in_array( $pKey, $allowedPkeys, true )) {
                    continue;
                }
                $property->unsetParameter( $pKey );
            } // end foreach
        } // end if
        return $property->setValue( $value );
    }
}
