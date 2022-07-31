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
namespace Kigkonsult\PhpVcardMgr\Formatter;

use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use Kigkonsult\PhpVcardMgr\Util\Json;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class JcardFormatter implements FormatterInterface
{
    /**
     * @inheritDoc
     * @todo json flags ?
     */
    public function format( array $vCards ) : string
    {
        static $VCARD  = 'vcard';
        $cardsArray    = [];
        foreach( $vCards as $vCard ) {
            $jsonArray = [];
            foreach( $vCard->getProperties() as $property ) {
                $propName   = $property->getPropName();
                $propName2  = StringUtil::isXprefixed( $propName ) ? self::XPREFIX : $propName;
                $jsonName   = strtolower( $propName );
                $parameters = self::prepParameters( $property );
                $valueType  = $property->getValueType();
                $value      = $property->getValue();
                switch( $propName2 ) {
                    case self::ANNIVERSARY :  // fall through
                    case self::BDAY :         // fall through
                        $jsonArray[] = [
                            $jsonName,
                            $parameters,
                            $valueType,
                            DateUtil::convertVcard2JcardDates( $value, $valueType )
                        ];
                        break;
                    case self::REV :
                        $jsonArray[] = [
                            $jsonName,
                            $parameters,
                            $valueType,
                            DateUtil::convertVcard2JcardTimestamp( $value )
                        ];
                        break;
                    case self::TZ :
                        $jsonArray[] = [
                            $jsonName,
                            $parameters,
                            $valueType,
                            DateUtil::convertVcard2JcardZone( $value )
                        ];
                        break;
                    case self::ADR :          // seven elements in value array, fall through
                    case self::N :            // five elements in value array, fall through
                        foreach( $value as $vix => $valuePart ) {
                            if( empty( $valuePart )) {
                                continue;
                            }
                            if( false !== strpos( $valuePart, StringUtil::$COMMA )) {
                                $value[$vix] = explode( StringUtil::$COMMA, $valuePart );
                            }
                        } // end foreach      // fall through
                    case self::GENDER :       // 1-2 elements in value array, fall through
                    case self::ORG :          // 1* elements in value array
                        if( 1 === count( $value )) { // if one element in value array
                            $value = reset( $value );
                        }                     // fall through
                    case self::CALADRURI :    // fall through
                    case self::CALURI :       // fall through
                    case self::CATEGORIES :   // fall through
                    case self::CLIENTPIDMAP : // fall through
                    case self::EMAIL :        // fall through
                    case self::FBURL :        // fall through
                    case self::FN :           // fall through
                    case self::GEO :          // fall through
                    case self::IMPP :         // fall through
                    case self::KEY :          // fall through
                    case self::KIND :         // fall through
                    case self::LANG  :        // fall through
                    case self::LOGO :         // fall through
                    case self::MEMBER :       // fall through
                    case self::NICKNAME :     // fall through
                    case self::NOTE :         // fall through
                    case self::PHOTO :        // fall through
                    case self::PRODID :       // fall through
                    case self::RELATED :      // fall through
                    case self::ROLE :         // fall through
                    case self::SOUND :        // fall through
                    case self::SOURCE :       // fall through
                    case self::TEL :          // fall through
                    case self::TITLE :        // fall through
                    case self::UID :          // fall through
                    case self::URL :          // fall through
                    case self::VERSION :      // fall through
                    case self::XML :          // fall through
                    case self::XPREFIX :
                        $jsonArray[] = [ $jsonName, $parameters, $valueType, $value ];
                        break;
                } // end switch
            } // end foreach
            $cardsArray[] = $jsonArray;
        } // end foreach
        return Json::jsonEncode( [ $VCARD, $cardsArray ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    }

    /**
     * @param PropertyInterface $property
     * @return object
     */
    private static function prepParameters( PropertyInterface $property )
    {
        $parameters = VcardFormatterUtil::prepParameters( $property );
        if( $property->isGroupSet()) {
            $parameters[self::GROUP] = $property->getGroup();
        }
        if( isset( $parameters[self::LABEL] ) &&
            ( false !== strpos( $parameters[self::LABEL], StringUtil::$STREOL ))) {
            $parameters[self::LABEL] = str_replace(
                StringUtil::$STREOL,
                StringUtil::$NEWLINE,
                $parameters[self::LABEL]
            );
        }
        if( isset( $parameters[self::PID] )) {
            if( is_array( $parameters[self::PID] )) {
                $parameters[self::PID] = implode( StringUtil::$COMMA, $parameters[self::PID] );
            }
            elseif( $parameters[self::PID] == (int) $parameters[self::PID] ) { // note ==
                $parameters[self::PID] = (int) $parameters[self::PID];
            }
        }
        if( isset( $parameters[self::PREF] ) ) {
            $parameters[self::PREF] = (int) $parameters[self::PREF];
        }
        if( isset( $parameters[self::SORT_AS] ) &&
            ( false !== strpos( $parameters[self::SORT_AS], StringUtil::$COMMA ))) {
            $parameters[self::SORT_AS] = explode( StringUtil::$COMMA, $parameters[self::SORT_AS] );
        }
        if( isset( $parameters[self::TYPE] ) && ( 1 === count( $parameters[self::TYPE] ))) {
            $parameters[self::TYPE] = reset( $parameters[self::TYPE] );
        }

        return (object) array_change_key_case( $parameters );
    }
}
