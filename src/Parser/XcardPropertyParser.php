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
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use XMLReader;

class XcardPropertyParser extends XcardParserBase
{
    /**
     * XCard Properties : constants
     */
    public const XVCARDS        = 'vcards';
    public const XVCARD         = 'vcard';
    public const XGROUP         = 'group';
    public const XNAME          = 'name';
    public const XPARAMETERS    = 'parameters';

    /**
     * @inheritDoc
     * @param string $source  property name
     */
    public function parse( string $source ) : PropertyInterface
    {
        static $ARRAYPROPS1 = [ PropertyInterface::ADR, PropertyInterface::GENDER, PropertyInterface::N ];
        static $ARRAYPROPS2 = [ PropertyInterface::CLIENTPIDMAP, PropertyInterface::ORG ];
        static $ARRAYPROPS3  = [ PropertyInterface::CATEGORIES, PropertyInterface::NICKNAME ];
        $isArrayProp1 = in_array( $source, $ARRAYPROPS1, true );
        $isArrayProp2 = in_array( $source, $ARRAYPROPS2, true );
        $isArrayProp3 = in_array( $source, $ARRAYPROPS3, true );
        $propNameLc   = strtolower( $source );
        $class        = self::getClass( $source );
        $parameters   = [];
        $valueType    = $class::getAcceptedValueTypes( true );
        $value = $sep = $prevKey = $vx = null;
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
        while( @$this->reader->read() ) {
            $isNodeTypeTEXT = ( XMLReader::TEXT === $this->reader->nodeType );
            switch( true ) {
                case ( XMLReader::SIGNIFICANT_WHITESPACE === $this->reader->nodeType ) :
                    break;
                case ( XMLReader::END_ELEMENT === $this->reader->nodeType ):
                    if( $propNameLc === $this->reader->localName ) {
                        break 2; // end propName
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
                case ( $isNodeTypeTEXT && $isArrayProp1 ) :
                    switch( true ) {
                        case ( ! $this->reader->hasValue ) :
                            break;
                        case empty( $value[$vx] ) :
                            $value[$vx] = $this->reader->value;
                            break;
                        default :
                            $value[$vx] = $value[$vx] . StringUtil::$COMMA . $this->reader->value;
                            break;
                    } // end switch
                    break;
                case ($isNodeTypeTEXT && ( $isArrayProp2 || $isArrayProp3 )) :
                    $subValue = $this->reader->hasValue ? $this->reader->value : StringUtil::$SP0;
                    $value    = ( null === $value )
                        ? $subValue
                        : $value . $sep . $subValue;
                    break;
                case ( $isNodeTypeTEXT && ! empty( $valueType )) :
                    $value = $this->reader->value;
                    break;
            } // end switch
        } // end while
        if( ! empty( $valueType )) {
            $parameters[PropertyInterface::VALUE] = $valueType;
        }
        return ( Xprop::class === $class )
            ? $class::factory( $source, $value, $parameters, $valueType )
            : $class::factory( $value, $parameters, $valueType );
    }

    /**
     * Return property class for propName
     *
     * @param string $propName
     * @return string
     */
    private static function getClass( string $propName ) : string
    {
        switch( $propName ) {
            case self::ADR :
                return Adr::class;
            case self::ANNIVERSARY :
                return Anniversary::class;
            case self::BDAY :
                return Bday::class;
            case self::CALADRURI :
                return CalAdrUri::class;
            case self::CALURI :
                return CalUri::class;
            case self::CATEGORIES :
                return Categories::class;
            case self::CLIENTPIDMAP :
                return ClientPidMap::class;
            case self::EMAIL :
                return Email::class;
            case self::FBURL :
                return Fburl::class;
            case self::FN :
                return FullName::class;
            case self::GENDER :
                return Gender::class;
            case self::GEO :
                return Geo::class;
            case self::IMPP :
                return Impp::class;
            case self::KEY :
                return Key::class;
            case self::KIND :
                return Kind::class;
            case self::LANG  :
                return Lang::class;
            case self::LOGO :
                return Logo::class;
            case self::MEMBER :
                return Member::class;
            case self::N :
                return N::class;
            case self::NICKNAME :
                return Nickname::class;
            case self::NOTE :
                return Note::class;
            case self::ORG :
                return Org::class;
            case self::PHOTO :
                return Photo::class;
            case self::RELATED :
                return Related::class;
            case self::REV :
                return Rev::class;
            case self::ROLE :
                return Role::class;
            case self::SOUND :
                return Sound::class;
            case self::SOURCE :
                return Source::class;
            case self::TEL :
                return Tel::class;
            case self::TITLE :
                return Title::class;
            case self::TZ :
                return Tz::class;
            case self::UID :
                return Uid::class;
            case self::URL :
                return Url::class;
            case self::XML :
                return Xml::class;
            default :
                return Xprop::class;
        } // end switch
    }

    /**
     * Parse property parameters
     *
     * $XMLTYPES MUST exist in XcardPropertyFormatter::writeParameters
     *
     * @return array
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
                ! in_array( $this->reader->localName, $XMLTYPES, true ) ) {
                $pKey = $this->reader->localName;
            }
            elseif( XMLReader::TEXT === $this->reader->nodeType ) {
                $parameters[$pKey] = isset( $parameters[$pKey] )
                    ? $parameters[$pKey] . StringUtil::$COMMA . $this->reader->value // type?
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
