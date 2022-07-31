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

use Faker;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\VcardLoad\Vcard as VcardLoader;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    public static function conformEols( string $string ) : string
    {
        $repl   = StringUtil::getRandChars( 32 );
        $string = str_replace( StringUtil::$CRLF, $repl, $string );
        $string = str_replace( StringUtil::$NEWLINE, $repl,   $string );
        return str_replace( $repl, StringUtil::$CRLF, $string );
    }
    /**
     * Return 'FakerPHP'-ed Vcards[]
     *
     * trailing UID based on 'case' and Vcard-order-number
     * some multi-props with group or not
     *
     * @param int|string $case
     * @param null|bool $datetimeDateOnly
     * @return Vcard[]
     */
    public static function getFakerVcards( $case, ? bool $datetimeDateOnly = false ) : array
    {
        static $DS1 = '-';
        $faker  = Faker\Factory::create();
        $fakerVcardLoads = (int) ( $GLOBALS['fakerVcardLoads'] ?? 3 );
        $case   = str_pad((string) $case, 5, $DS1, STR_PAD_LEFT );
        $vCards = [];
        for( $x = 1; $x <= $fakerVcardLoads; $x++ ) {
            $groups = array_pad( $faker->words( 2), 4, null );
            $xDisp  = ( 10 > $x ) ? $DS1 . $x : $x;
            $uidStr = substr( StringUtil::getNewUid(), 0, -8 ) . $case . $DS1 . $xDisp;
            $vCard  = VcardLoader::load( $datetimeDateOnly )->addProperty( Uid::factory( $uidStr ));
            // supply some multi-props with group or not
            $props  = [];
            foreach( [ Vcard::ADR, Vcard::EMAIL, Vcard::ORG, Vcard::TEL, Vcard::XPREFIX] as $propName ) {
                foreach( $vCard->getProperties( $propName ) as $property ) {
                    $group  = $faker->randomElement( $groups );
                    if( ! empty( $group ) ) {
                        $property->setGroup( $group );
                    }
                    $props[] = $property;
                } // end foreach
            } // end foreach
            $vCards[$x] = $vCard->replaceProperty( $props );
        } // end for
        return $vCards;
    }

    /**
     * @param string $method
     * @param int|string $spec
     * @param mixed|Vcard|Vcard[] $value
     * @param null|string $propName
     */
    public static function propDisp( string $method, $spec, $value, ? string $propName = null ) : void
    {
        if( $value instanceof Vcard ) {
            $value = [ $value ];
        }
        if( is_array( $value )) {
            foreach( $value as $vCard ) {
                if( ! $vCard->hasProperty( $propName )) {
                    continue;
                }
                foreach( $vCard->getProperties( $propName ) as $property ) {
                    if( ! is_array( $property )) {
                        $property = [ $property ];
                    }
                    foreach( $property as $theProp ) {
                        error_log( $method . ' ' . $spec . ' ' . ( $propName ?? '-' ) .
                            PHP_EOL . $theProp->__toString());
                    } // end foreach
                } // end foreach
            } // end foreach
        } // end if
        else {
            error_log( $method . ' ' . $spec . PHP_EOL . var_export( $value, true ));
        }
    }
}
