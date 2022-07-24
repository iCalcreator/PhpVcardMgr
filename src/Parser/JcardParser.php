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
use Kigkonsult\PhpVcardMgr\Util\Json;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;

class JcardParser implements ParserInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     * @throws RuntimeException
     */
    public function parse( string $source ) : array
    {
        static $VCARD  = 'vcard';
        static $ERR1   = 'First element \'vcard\' not found';
        static $ERR2   = 'No vcard (array) found';
        static $ERR3   = 'VERSION not 4.0';

        $jsonArray = Json::jsonDecode( $source );

        $item = array_shift( $jsonArray );
        if( $VCARD !== $item ) {
            throw new RuntimeException( $ERR1 );
        }
        $item = array_shift( $jsonArray ); // single Vcard or [ *Vcard ]
        if( ! is_array( $item )) {
            throw new RuntimeException( $ERR2 );
        }
        if( isset( $item[0][0] ) && is_string( $item[0][0] )) {
            $item = [ $item ]; // single Vcard to [ *Vcard ]
        }
        $vCards = [];
        foreach( $item as $vCardArray ) {
            $vCard = new Vcard();
            foreach( $vCardArray as $propArray ) {
                $group      = null;
                [ $propName, $parameters, $valueType, $value ] = $propArray;
                $propName   = strtoupper( $propName );
                $parameters = self::prepParameters( $parameters, $group );
                switch( $propName ) {
                    case self::VERSION :
                        if( '4.0' !== $value ) {
                            throw new RuntimeException( $ERR3 );
                        }
                    // fall through
                    case self::PRODID :
                        continue 2;
                    case self::ADR :
                        $property = ADR::factory( 
                            self::concatSubArrToCommaString( $value ), 
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::ANNIVERSARY :
                        $property = Anniversary::factory(
                            DateUtil::convertJcard2VcardDates( $value, $valueType ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::BDAY :
                        $property = Bday::factory(
                            DateUtil::convertJcard2VcardDates( $value, $valueType ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::CALADRURI :
                        $property = CalAdrUri::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::CALURI :
                        $property = CalUri::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::CATEGORIES :
                        $property = Categories::factory(
                            implode( StringUtil::$COMMA, $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::CLIENTPIDMAP :
                        $property = ClientPidMap::factory(
                            self::concatToSemicString( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::EMAIL :
                        $property = Email::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::FBURL :
                        $property = Fburl::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::FN :
                        $property = FullName::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::GENDER :
                        $property = Gender::factory(
                            self::concatToSemicString( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::GEO :
                        $property = Geo::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::IMPP :
                        $property = Impp::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::KEY :
                        $property = Key::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::KIND :
                        $property = Kind::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::LANG  :
                        $property = Lang::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::LOGO :
                        $property = Logo::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::MEMBER :
                        $property = Member::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::NICKNAME :
                        $property = Nickname::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::NOTE :
                        $property = Note::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::N :
                        $property = N::factory(
                            self::concatSubArrToCommaString( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::ORG :
                        $property = Org::factory(
                            self::concatToSemicString( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::PHOTO :
                        $property = Photo::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::RELATED :
                        $property = Related::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::REV :
                        $property = Rev::factory(
                            DateUtil::convertJcard2VcardTimestamp( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::ROLE :
                        $property = Role::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::SOUND :
                        $property = Sound::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::SOURCE :
                        $property = Source::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::TEL :
                        $property = Tel::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::TITLE :
                        $property = Title::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::TZ :
                        $property = Tz::factory(
                            DateUtil::convertJcard2VcardZone( $value ),
                            $parameters,
                            $valueType,
                            $group
                        );
                        break;
                    case self::UID :
                        $property = Uid::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::URL :
                        $property = Url::factory( $value, $parameters, $valueType, $group );
                        break;
                    case self::XML :
                        $property = Xml::factory( $value, $parameters, $valueType, $group );
                        break;
                    default :
                        if( ! StringUtil::isXprefixed( $propName )) {
                            continue 2;
                        }
                        $property = Xprop::factory( $propName, $value, $parameters, $valueType, $group );
                } // end switch
                $vCard->addProperty( $property );
            }
            $vCards[] = $vCard;
        }
        return $vCards;
    }

    /**
     * @param array $value
     * @return array
     */
    private static function concatSubArrToCommaString( array $value ) : array
    {
        foreach( $value as $fnIx => $fnPart ) {
            if( is_array( $fnPart )) {
                $value[$fnIx] = implode( StringUtil::$COMMA, $fnPart );
            }
        } // end foreach
        return $value;
    }

    /**
     * @param string|array $value
     * @return string
     */
    private static function concatToSemicString( $value ) : string
    {
        return is_array( $value ) ? implode( StringUtil::$SEMIC, $value ) : $value;
    }

    /**
     * @param array $parameters
     * @param null|string $group
     * @return array
     */
    private static function prepParameters( array $parameters, ? string & $group ) : array
    {
        $parameters = array_change_key_case( $parameters, CASE_UPPER );
        if( isset( $parameters[self::GROUP] )) {
            $group  = $parameters[self::GROUP];
            unset( $parameters[self::GROUP] );
        }
        if( isset( $parameters[self::LABEL] ) &&
            ( false !== strpos( $parameters[self::LABEL], StringUtil::$NEWLINE ))) {
            $parameters[self::LABEL] = str_replace(
                StringUtil::$NEWLINE,
                StringUtil::$STREOL,
                $parameters[self::LABEL]
            );
        }
        if( isset( $parameters[self::PID] ) && is_array( $parameters[self::PID] )) {
            $parameters[self::PID] = implode( StringUtil::$COMMA, $parameters[self::PID] );
        }
        return $parameters;
    }
}
