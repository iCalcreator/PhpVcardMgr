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
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;

class Vcard4Parser extends VcardParserBase
{
    /**
     * @param string[] $vCardRows
     * @return Vcard
     * @throws Exception
     * @throws RuntimeException
     */
    public function vCardParse( array $vCardRows ) : Vcard
    {
        static $VERSIONno  = '4.0';
        static $ERR       = 'VERSION not 4.0, got ';
        static $UNKNOWN   = 'unknown';
        static $TRIMCHARS = "\x00..\x1F";
        static $SQ        = "'";
        $rows  = self::concatRows( $vCardRows );
        $vCard = new Vcard();
        foreach( $rows as $row ) {
            /* separate property name  and  opt.params and value */
            [ $group, $propName, $row ] = self::getPropName( $row );
                    /* separate parameters from value */
            [ $value, $parameters ] = self::splitContent( $row );
            $parameters = array_change_key_case( $parameters, CASE_UPPER );
            if( isset( $parameters[self::VALUE] ) && ( $UNKNOWN === strtolower( $parameters[self::VALUE] ))) {
                unset( $parameters[self::VALUE] );
            }
            switch( $propName ) {
                case self::VERSION :
                    if( $VERSIONno !== $value ) {
                        throw new RuntimeException( $ERR . var_export( $value, true ));
                    }
                    // fall through
                case self::PRODID :
                    continue 2;
                case self::ADR :
                    $valueType = $parameters[self::VALUE] ?? Adr::getAcceptedValueTypes( true );
                    $property  = Adr::factory(
                        StringUtil::semicSplit( $value, 7 ), 
                        $parameters, 
                        $valueType, 
                        $group
                    );
                    break;
                case self::ANNIVERSARY :
                    $valueType = $parameters[self::VALUE] ?? Anniversary::getAcceptedValueTypes( true );
                    $property  = Anniversary::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::BDAY :
                    $valueType = $parameters[self::VALUE] ?? Bday::getAcceptedValueTypes( true );
                    $property  = Bday::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::CALADRURI :
                    $valueType = $parameters[self::VALUE] ?? CalAdrUri::getAcceptedValueTypes( true );
                    $property  = CalAdrUri::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::CALURI :
                    $valueType = $parameters[self::VALUE] ?? CalUri::getAcceptedValueTypes( true );
                    $property  = CalUri::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::CATEGORIES :
                    $valueType = $parameters[self::VALUE] ?? Categories::getAcceptedValueTypes( true );
                    $property  = Categories::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::CLIENTPIDMAP :
                    $valueType = $parameters[self::VALUE] ?? ClientPidMap::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = ClientPidMap::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::EMAIL :
                    $valueType = $parameters[self::VALUE] ?? Email::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Email::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::FBURL :
                    $valueType = $parameters[self::VALUE] ?? Fburl::getAcceptedValueTypes( true );
                    $property  = Fburl::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::FN :
                    $valueType = $parameters[self::VALUE] ?? FullName::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = FullName::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::GENDER :
                    $valueType = $parameters[self::VALUE] ?? Gender::getAcceptedValueTypes( true );
                    $property  = Gender::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::GEO :
                    $valueType = $parameters[self::VALUE] ?? Geo::getAcceptedValueTypes( true );
                    $property  = Geo::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::IMPP :
                    $valueType = $parameters[self::VALUE] ?? Impp::getAcceptedValueTypes( true );
                    $property  = Impp::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::KEY :
                    $valueType = $parameters[self::VALUE] ?? Key::getAcceptedValueTypes( true );
                    $property  = Key::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::KIND :
                    $valueType = $parameters[self::VALUE] ?? Kind::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Kind::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::LANG  :
                    $valueType = $parameters[self::VALUE] ?? Lang::getAcceptedValueTypes( true );
                    $property  = Lang::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::LOGO :
                    $valueType = $parameters[self::VALUE] ?? Logo::getAcceptedValueTypes( true );
                    $property  = Logo::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::MEMBER :
                    $valueType = $parameters[self::VALUE] ?? Member::getAcceptedValueTypes( true );
                    $property  = Member::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::N :
                    $valueType = $parameters[self::VALUE] ?? N::getAcceptedValueTypes( true );
                    $property  = N::factory(
                        StringUtil::semicSplit( $value, 5 ),
                        $parameters,
                        $valueType,
                        $group
                    );
                    break;
                case self::NICKNAME :
                    $valueType = $parameters[self::VALUE] ?? Nickname::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Nickname::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::NOTE :
                    $valueType = $parameters[self::VALUE] ?? Note::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Note::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::ORG :
                    $valueType = $parameters[self::VALUE] ?? Org::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Org::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::PHOTO :
                    $valueType = $parameters[self::VALUE] ?? Photo::getAcceptedValueTypes( true );
                    $property  = Photo::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::RELATED :
                    $valueType = $parameters[self::VALUE] ?? Related::getAcceptedValueTypes( true );
                    $property  = Related::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::REV :
                    $valueType = $parameters[self::VALUE] ?? Rev::getAcceptedValueTypes( true );
                    $property  = Rev::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::ROLE :
                    $valueType = $parameters[self::VALUE] ?? Role::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Role::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::SOUND :
                    $valueType = $parameters[self::VALUE] ?? Sound::getAcceptedValueTypes( true );
                    $property  = Sound::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::SOURCE :
                    $valueType = $parameters[self::VALUE] ?? Source::getAcceptedValueTypes( true );
                    $property  = Source::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::TEL :
                    $valueType = $parameters[self::VALUE] ?? Tel::getAcceptedValueTypes( true );
                    $property  = Tel::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::TITLE :
                    $valueType = $parameters[self::VALUE] ?? Title::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Title::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::TZ :
                    $valueType = $parameters[self::VALUE] ?? Tz::getAcceptedValueTypes( true );
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property  = Tz::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::UID :
                    $valueType = $parameters[self::VALUE] ?? Uid::getAcceptedValueTypes( true );
                    $property  = Uid::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::URL :
                    $valueType = $parameters[self::VALUE] ?? Url::getAcceptedValueTypes( true );
                    $property  = Url::factory( $value, $parameters, $valueType, $group );
                    break;
                case self::XML :
                    $valueType = $parameters[self::VALUE] ?? Xml::getAcceptedValueTypes( true );
                    // replace single quotes by double ones
                    if( str_contains( $value, $SQ )) {
                        $value = str_replace( $SQ, StringUtil::$QQ, $value );
                    }
                    $property  = Xml::factory( $value, $parameters, $valueType, $group );
                    break;
                default :
                    $valueType = $parameters[self::VALUE] ?? Xprop::getAcceptedValueTypes( true );
                    if( ! StringUtil::isXprefixed( $propName )) {
                        continue 2;
                    }
                    $property = Xprop::factory( $propName, $value, $parameters, $valueType, $group );
            } // end switch
            $vCard->addProperty( $property );
        } // end foreach
        return $vCard;
    }
}
