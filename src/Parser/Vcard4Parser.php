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
        static $ERR       = 'expect VERSION 4.0, got ';
        static $UNKNOWN   = 'unknown';
        static $TRIMCHARS = "\x00..\x1F";
        static $SQ        = "'";
        $rows  = self::concatRows( $vCardRows );
        $vCard = new Vcard();
        foreach( $rows as $row ) {
            // separate property name and row
            [ $group, $propName, $row ] = self::getPropName( $row );
            $property   = self::newProperty( $propName );
            if( ! empty( $group )) {
                $property->setGroup( $group );
            }
            // separate parameters from value
            [ $value, $parameters ] = self::splitContent( $row );
            $parameters = array_change_key_case( $parameters, CASE_UPPER );
            if( isset( $parameters[self::VALUE] ) && ( $UNKNOWN === strtolower( $parameters[self::VALUE] ))) {
                unset( $parameters[self::VALUE] );
            }
            $valueType = $parameters[self::VALUE] ?? $property::getAcceptedValueTypes( true );
            $property->setParameters( $parameters );
            $property->setValueType( $valueType );
            switch( $propName ) {
                case self::VERSION :
                    if( self::VERSION4 !== $value ) {
                        throw new RuntimeException( $ERR . var_export( $value, true ));
                    }
                    // fall through
                case self::PRODID :
                    continue 2;
                case self::CLIENTPIDMAP : // fall through
                case self::EMAIL :        // fall through
                case self::FN :           // fall through
                case self::KIND :         // fall through
                case self::NICKNAME :     // fall through
                case self::NOTE :         // fall through
                case self::ORG :          // fall through
                case self::ROLE :         // fall through
                case self::TITLE :        // fall through
                case self::TZ :
                    if( self::TEXT === $valueType ) {
                        $value = self::strunrep( rtrim( $value, $TRIMCHARS ));
                    }
                    $property->setValue( $value );
                    break;
                case self::ADR :          // fall through
                case self::ANNIVERSARY :  // fall through
                case self::BDAY :         // fall through
                case self::CALADRURI :    // fall through
                case self::CALURI :       // fall through
                case self::CATEGORIES :   // fall through
                case self::FBURL :        // fall through
                case self::GENDER :       // fall through
                case self::GEO :          // fall through
                case self::IMPP :         // fall through
                case self::KEY :          // fall through
                case self::LANG  :        // fall through
                case self::LOGO :         // fall through
                case self::MEMBER :       // fall through
                case self::N :            // fall through
                case self::PHOTO :        // fall through
                case self::RELATED :      // fall through
                case self::REV :          // fall through
                case self::SOUND :        // fall through
                case self::SOURCE :       // fall through
                case self::TEL :          // fall through
                case self::UID :          // fall through
                case self::URL :
                    $property->setValue( $value );
                    break;
                case self::XML :
                    // replace single quotes by double ones
                    if( str_contains( $value, $SQ )) {
                        $value = str_replace( $SQ, StringUtil::$QQ, $value );
                    }
                    $property->setValue( $value );
                    break;
                default :
                    if( ! StringUtil::isXprefixed( $propName )) {
                        continue 2;
                    }
                    $property->setPropName( $propName )->setValue( $value );
            } // end switch
            $vCard->addProperty( $property );
        } // end foreach
        return $vCard;
    }
}
