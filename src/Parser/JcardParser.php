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
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use Kigkonsult\PhpVcardMgr\Util\Json;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;

class JcardParser extends ParserBase implements ParserInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     * @throws RuntimeException
     */
    public function parse( string $source ) : array
    {
        static $VCARD = 'vcard';
        static $ERR1  = 'First element \'vcard\' not found';
        static $ERR2  = 'No vcard (array) found';
        static $ERR3  = 'VERSION not 4.0';
        $jsonArray    = Json::jsonDecode( $source );
        $item         = array_shift( $jsonArray );
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
                $property   = self::newProperty( $propName );
                $parameters = self::prepParameters( $parameters, $group );
                $property->setParameters( $parameters );
                if( ! empty( $group )) {
                    $property->setGroup( $group );
                }
                $property->setValueType( $valueType );
                switch( $propName ) {
                    case self::VERSION :
                        if( self::VERSION4 !== $value ) {
                            throw new RuntimeException( $ERR3 );
                        } // fall through
                    case self::PRODID :
                        continue 2;
                    case self::ADR : // fall through
                    case self::N :
                        $property->setValue( self::concatSubArrToCommaString( $value ));
                        break;
                    case self::ANNIVERSARY : // fall through
                    case self::BDAY :
                        $property->setValue( DateUtil::convertJcard2VcardDates( $value, $valueType ));
                        break;
                    case self::REV :
                        $property->setValue( DateUtil::convertJcard2VcardTimestamp( $value ));
                        break;
                    case self::TZ :
                        $property->setValue( DateUtil::convertJcard2VcardZone( $value ));
                        break;

                    case self::CALADRURI :    // fall through
                    case self::CALURI :       // fall through
                    case self::CATEGORIES :   // fall through
                    case self::CLIENTPIDMAP : // fall through
                    case self::EMAIL :        // fall through
                    case self::FBURL :        // fall through
                    case self::FN :           // fall through
                    case self::GENDER :       // fall through
                    case self::GEO :          // fall through
                    case self::IMPP :         // fall through
                    case self::KEY :          // fall through
                    case self::KIND :         // fall through
                    case self::LANG  :        // fall through
                    case self::LOGO :         // fall through
                    case self::MEMBER :       // fall through
                    case self::NICKNAME :     // fall through
                    case self::NOTE :         // fall through
                    case self::ORG :          // fall through
                    case self::PHOTO :        // fall through
                    case self::RELATED :      // fall through
                    case self::ROLE :         // fall through
                    case self::SOUND :        // fall through
                    case self::SOURCE :       // fall through
                    case self::TEL :          // fall through
                    case self::TITLE :        // fall through
                    case self::UID :          // fall through
                    case self::URL :          // fall through
                    case self::XML :
                        $property->setValue( $value );
                        break;
                    default :
                        if( ! StringUtil::isXprefixed( $propName )) {
                            continue 2;
                        }
                        $property->setPropName( $propName )
                            ->setValue( $value );
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
        if( isset( $parameters[self::LABEL] )) {
            $parameters[self::LABEL] = str_replace(
                StringUtil::$CRLFs,
                StringUtil::$STREOL,
                $parameters[self::LABEL]
            );
        }
        foreach( [ self::PID, self::SORT_AS] as $pKey ) {
            if( isset( $parameters[$pKey] ) && is_array( $parameters[$pKey] ) ) {
                $parameters[$pKey] = implode( StringUtil::$COMMA, $parameters[$pKey] );
            }
        }
        return $parameters;
    }
}
