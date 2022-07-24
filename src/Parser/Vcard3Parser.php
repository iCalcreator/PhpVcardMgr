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
use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Anniversary;
use Kigkonsult\PhpVcardMgr\Property\Bday;
use Kigkonsult\PhpVcardMgr\Property\CalAdrUri;
use Kigkonsult\PhpVcardMgr\Property\CalUri;
use Kigkonsult\PhpVcardMgr\Property\Categories;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Email;
use Kigkonsult\PhpVcardMgr\Property\Fburl;
use Kigkonsult\PhpVcardMgr\Property\FullName;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\Geo;
use Kigkonsult\PhpVcardMgr\Property\Impp;
use Kigkonsult\PhpVcardMgr\Property\Key;
use Kigkonsult\PhpVcardMgr\Property\Kind;
use Kigkonsult\PhpVcardMgr\Property\Lang;
use Kigkonsult\PhpVcardMgr\Property\Logo;
use Kigkonsult\PhpVcardMgr\Property\Member;
use Kigkonsult\PhpVcardMgr\Property\N;
use Kigkonsult\PhpVcardMgr\Property\Nickname;
use Kigkonsult\PhpVcardMgr\Property\Note;
use Kigkonsult\PhpVcardMgr\Property\Org;
use Kigkonsult\PhpVcardMgr\Property\Photo;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Property\Related;
use Kigkonsult\PhpVcardMgr\Property\Rev;
use Kigkonsult\PhpVcardMgr\Property\Role;
use Kigkonsult\PhpVcardMgr\Property\Sound;
use Kigkonsult\PhpVcardMgr\Property\Source;
use Kigkonsult\PhpVcardMgr\Property\Tel;
use Kigkonsult\PhpVcardMgr\Property\Title;
use Kigkonsult\PhpVcardMgr\Property\Tz;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Property\Url;
use Kigkonsult\PhpVcardMgr\Property\Xml;
use Kigkonsult\PhpVcardMgr\Property\Xprop;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;

class Vcard3Parser extends VcardParserBase
{
    /**
     * @var string
     */
    private static $ENCODING   = 'ENCODING';

    /**
     * @param string[] $vCardRows
     * @return Vcard
     * @throws Exception
     * @throws RuntimeException
     */
    public function vCardParse( array $vCardRows ) : Vcard
    {
        static $VERSIONno  = '3.0';
        static $ERR        = 'VERSION not 3.0, got ';
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
                    /* separate parameters from value */
            [ $value, $parameters ] = self::splitContent( $row );
            $parameters = self::prepParameters( $parameters );
            if( isset( $parameters[self::VALUE] ) && ( self::TEXT === $parameters[self::VALUE] )) {
                $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
            }
            switch( $propName ) {
                case self::VERSION :
                    if( $VERSIONno !== $value ) {
                        throw new RuntimeException( $ERR . var_export( $value, true ));
                    }               // fall through
                case self::PRODID : // fall through
                case $PROFILE :
                    continue 2;
                case self::ADR :
                    $property = self::processAdr( $value, $parameters, $group );
                    break;
                case self::BDAY :
                    $property = self::processBday( $value, $parameters, $group );
                    break;
                case self::CATEGORIES :
                    $property = Categories::factory( $value, $parameters, null, $group );
                    break;
                case self::EMAIL :
                    $property = Email::factory( $value, $parameters, null, $group );
                    break;
                case self::FN :
                    $property = FullName::factory( $value, $parameters, null, $group );
                    break;
                case self::GEO :
                    $property = self::processGeo( $value, $parameters, $group );
                    break;
                case self::KEY :
                    $property = self::processKey( $value, $parameters, $group );
                    break;
                case self::LOGO :
                    $property = self::processLogo( $value, $parameters, $group );
                    break;
                case self::N :
                    $property = self::processN( $value, $parameters, $group );
                    $nIx = $rix;
                    break;
                case self::NICKNAME :
                    $property = Nickname::factory( StringUtil::unEscapeComma( $value ), $parameters, null, $group );
                    break;
                case self::NOTE :
                    $property = Note::factory( $value, $parameters, null, $group );
                    break;
                case self::ORG :
                    $property = Org::factory( StringUtil::unEscapeComma( $value ), $parameters, null, $group );
                    break;
                case self::PHOTO :
                    $property = self::processPhoto( $value, $parameters, $group );
                    break;
                case self::REV :
                    $property = self::processRev( $value, $parameters, $group );
                    break;
                case self::ROLE :
                    $property = Role::factory( $value, $parameters, null, $group );
                    break;
                case $SORTSTRING :
                    $sortAs = $value;
                    continue 2;
                case self::SOUND :
                    $property = self::processSound( $value, $parameters, $group );
                    break;
                case self::SOURCE :
                    $valueType = self::URI;
                    $property  = Source::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::TEL :
                    $property = Tel::factory( $value, $parameters, null, $group );
                    break;
                case self::TITLE :
                    $property = Title::factory( $value, $parameters, null, $group );
                    break;
                case self::TZ :
                    $property = self::processTz( $value, $parameters, $group );
                    break;
                case self::UID :
                    $property = self::processUid( $value, $parameters, $group );
                    break;
                case self::URL :
                    $property = Url::factory( $value, $parameters, null, $group );
                    break;
                default :
                    $property = self::processXprop( $propName, $value, $parameters, $group );
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
        $parameters = array_change_key_case( $parameters, CASE_UPPER );
        static $specPkeys = [
            'CHARSET',
            'CONTEXT',
            'ENCODING',
        ];
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
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Adr
     */
    private static function processAdr( string $value, ? array $parameters = [], ? string $group = null ) : Adr
    {
        static $ADRNOTYPES = [ 'intl', 'dom', 'postal', 'parcel' ]; // Vcard3 types only
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
        return Adr::factory( StringUtil::semicSplit( $value, 7 ), $parameters, null, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Bday
     */
    private static function processBday( string $value, ? array $parameters = [], ? string $group = null ) : Bday
    {
        $value = trim( str_replace( [ DateUtil::$DS1, StringUtil::$COLON ], StringUtil::$SP0, $value ));
        $hasParameterValue = isset( $parameters[self::VALUE] );
        switch( true ) {
            case ( $hasParameterValue && ( self::DATE === $parameters[self::VALUE] )) ;
                $valueType = self::DATE;
                break;
            case ( $hasParameterValue && ( self::DATETIME === $parameters[self::VALUE] )) ;
                $valueType = self::DATETIME;
                break;
            case (( 8 === strlen( $value )) && DateUtil::isVcardDate( $value )) :
                $parameters[self::VALUE] = $valueType = self::DATE;
                break;
            default :
                $parameters[self::VALUE] = $valueType = self::DATETIME;
                break;
        } // end switch
        return Bday::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * A single structured value consisting of two float values separated by the SEMI-COLON character
     *
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Geo
     */
    private static function processGeo( string $value, ? array $parameters = [], ? string $group = null ) : Geo
    {
        static $GEOprefix = 'geo:';
        if( false !== strpos( $value, StringUtil::$SEMIC )) {
            $value = $GEOprefix . implode( StringUtil::$COMMA, explode( StringUtil::$SEMIC, $value ));
        }
        return Geo::factory( $value, $parameters, null, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Key
     */
    private static function processKey( string $value, ? array $parameters = [], ? string $group = null ) : Key
    {
        $valueType = ( isset( $parameters[self::VALUE] ) && ( self::TEXT === $parameters[self::VALUE] ))
            ? self::TEXT
            : self::URI;
        unset( $parameters[self::$ENCODING] );
        return Key::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Logo
     */
    private static function processLogo( string $value, ? array $parameters = [], ? string $group = null ) : Logo
    {
        $valueType = self::URI; // the only one allowed
        unset( $parameters[self::$ENCODING] );
        return Logo::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return N
     */
    private static function processN( string $value, ? array $parameters = [], ? string $group = null ) : N
    {
        return N::factory( StringUtil::semicSplit( $value, 5 ), $parameters, null, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Photo
     */
    private static function processPhoto( string $value, ? array $parameters = [], ? string $group = null ) : Photo
    {
        $valueType = self::URI; // the only one allowed
        unset( $parameters[self::$ENCODING] );
        return Photo::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * @param string $value
     * @param array|null $parameters
     * @param string|null $group
     * @return Rev
     * @throws Exception
     */
    private static function processRev( string $value, ? array $parameters = [], ? string $group = null ) : Rev
    {
        static $DATEHISSFX = 'T000000';
        $value      = str_replace( [ DateUtil::$DS1, StringUtil::$COLON ], StringUtil::$SP0, $value );
        if( DateUtil::isVcardDate( $value ) && ! DateUtil::isVcardDateTime( $value )) {
            $value .= $DATEHISSFX;
        }
        unset($parameters[self::VALUE] );
        $valueType  = self::TIMESTAMP;
        return Rev::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Sound
     */
    private static function processSound( string $value, ? array $parameters = [], ? string $group = null ) : Sound
    {
        $valueType = self::URI;
        unset( $parameters[self::$ENCODING], $parameters[self::VALUE] );
        return Sound::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * Vcard3 :
     * The default is a single utc-offset value.
     * It can also be reset to a single text value.
     * Vcard4 :
     * The default is a single text value.
     * It can also be reset to a single URI or utc-offset value.
     *
     * @param string $value
     * @param array|null   $parameters
     * @param string|null  $group
     * @return Tz
     */
    private static function processTz( string $value, ? array $parameters = [], ? string $group = null ) : Tz
    {
        $hasParameterValue = isset( $parameters[self::VALUE] );
        if( ! $hasParameterValue &&
            DateUtil::isJcardZone( $value )) {
            $parameters[self::VALUE] = $valueType = self::UTCOFFSET;
        }
        elseif( ! $hasParameterValue ) {
            $parameters[self::VALUE] = $valueType = self::TEXT;
        }
        else {
            $valueType = $parameters[self::VALUE];
        }
        if(( self::UTCOFFSET === $parameters[self::VALUE] ) &&
            ( false !== strpos( $value, StringUtil::$COLON ))) {
            $value = str_replace( StringUtil::$COLON, StringUtil::$SP0, $value  );
        }
        return Tz::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * @param string $value
     * @param array|null $parameters
     * @param string|null $group
     * @return Uid
     * @throws Exception
     */
    private static function processUid( string $value, ? array $parameters = [], ? string $group = null ) : Uid
    {
        $parameters[self::VALUE] = $valueType = self::URI;
        return Uid::factory( $value, $parameters, $valueType, $group );
    }

    /**
     * Process Vcard3 X-props, first looking for Vcard4-unique ones
     *
     * @param string      $propName
     * @param string      $value
     * @param array|null  $parameters
     * @param string|null $group
     * @return PropertyInterface
     */
    private static function processXprop(
        string $propName,
        string $value,
        ? array $parameters = [],
        ? string $group = null
    ) : PropertyInterface
    {
        switch( substr( $propName, 2 ) ) {
            case self::ANNIVERSARY :
                return Anniversary::factory( $value, $parameters, null, $group );
            case self::CALADRURI :
                return CalAdrUri::factory( $value, $parameters, null, $group );
            case self::CALURI :
                return CalUri::factory( $value, $parameters, null, $group );
            case self::CLIENTPIDMAP :
                return ClientPidMap::factory( $value, $parameters, null, $group );
            case self::FBURL :
                return Fburl::factory( $value, $parameters, null, $group );
            case self::GENDER :
                return Gender::factory( $value, $parameters, null, $group );
            case self::IMPP :
                return Impp::factory( $value, $parameters, null, $group );
            case self::KIND :
                return Kind::factory( $value, $parameters, null, $group );
            case self::LANG :
                return Lang::factory( $value, $parameters, null, $group );
            case self::MEMBER :
                return Member::factory( $value, $parameters, null, $group );
            case self::RELATED :
                return Related::factory( $value, $parameters, null, $group );
            case self::XML :
                return Xml::factory( $value, $parameters, null, $group );
            default :
                break;
        } // end switch
        $valueType = ( isset( $parameters[self::VALUE] ) &&
            in_array( $parameters[self::VALUE], Xprop::getAcceptedValueTypes(), true ))
            ? $parameters[self::VALUE]
            : self::TEXT;
        if( ! Xprop::isAnyParameterAllowed()) {
            $allowedPkeys = Xprop::getAcceptedParameterKeys();
            foreach( $parameters as $pKey => $pValue ) {
                if( in_array( $pKey, $allowedPkeys, true )) {
                    continue;
                }
                unset( $parameters[$pKey] );
            } // end foreach
        } // end if
        return Xprop::factory(
            $propName,
            $value,
            $parameters,
            $valueType,
            $group
        );
    }
}
