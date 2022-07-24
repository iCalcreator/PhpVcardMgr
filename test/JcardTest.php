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

use Exception;
use Faker;
use Kigkonsult\FakerLocRelTypes\Provider\SchemaURI;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Property\Url;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class JcardTest extends BaseTest
{
    use Vcard4StringProviderTrait;

    /**
     * @test
     * @dataProvider vCard4StringProvider
     *
     * @param int $case
     * @param string $vcardString
     * @throws Exception
     */
    public function parseTest( int $case, string $vcardString ) : void
    {
        $vcardString = self::conformEols( $vcardString );
        // parse vCardString into vCards
        $vCards = PhpVcardMgr::factory()->vCard4Parse( $vcardString )->getVCards();

        $this->jsonTest1( $case, $vCards );
        $this->jsonTest2( $case, $vCards );
    }

    /**
     * @test
     */
    public function fakerTest2() : void
    {
        $vCards = self::getFakerVcards( 200 );
        $this->jsonTest1( 200, $vCards );
        $this->jsonTest2( 200, $vCards );
    }

    /**
     * Test uri with password and special chars
     *
     * @test
     */
    public function fakerTest3() : void
    {
        $faker = Faker\Factory::create();
        $faker->addProvider( new SchemaURI( $faker ));
        $toExamine = "\"\\?*:/@|<>'.,\\";
        for( $x = 301; $x < 311; $x++ ) {
            do {
                $value = $faker->telnetUri( true );
            } while( strlen( $value ) === strcspn( $value, $toExamine ));
            $vcard = Vcard::factory()->addProperty( Url::factory( $value ));
            $case = (int) ( $x . '1' );
            $this->jsonTest1( $case, [ $vcard->addProperty( Uid::factory( (string) $case )) ] );
            $case = (int) ( $x . '2' );
            $this->jsonTest2( $case, [ $vcard->addProperty( Uid::factory( (string) $case )) ] );
        } // end for
        /*
         * BUT  [ ... \\\\ ... ] (i.e. double-\) could make problem..
        */
        $vcard = Vcard::factory()
            ->replaceProperty( Url::factory( 'telnet://schultz.sanford:Ll8xLYE\\\\,Sn@porro.fugit.info:23/' ));
        $this->jsonTest1( 4001, [ $vcard->addProperty( Uid::factory( '3001' )) ] );
        $this->jsonTest2( 4202, [ $vcard->addProperty( Uid::factory( '3202' )) ] );
    }

    /**
     * Simpler tests, from Vcard to Jcards and back again
     *
     * @param int $case
     * @param Vcard[] $vCards
     */
    public function jsonTest1( int $case, array $vCards ) : void
    {
        // format jsonString from Vcards
        $jsonString1 = PhpVcardMgr::factory()->setVCards( $vCards )->jCardFormat();

        // parse jsonString into vCards
        $phpVcardMgr = PhpVcardMgr::factory()->jcardParse( $jsonString1 );

        // format jsonString from Vcards
        $jsonString2 = $phpVcardMgr->jCardFormat();

        $this->assertJsonStringEqualsJsonString(
            $jsonString1,
            $jsonString2,
            __FUNCTION__ . ' Error case ' . $case
        );
    }

    /**
     * format jsonString from Vcards
     * parse jsonString into vCards
     * format vCards till vCardString
     * format jsonString from Vcards
     *
     * @param int $case
     * @param Vcard[] $vCards
     */
    public function jsonTest2( int $case, array $vCards ) : void
    {
        // format jsonString from Vcards
        $jsonString1 = PhpVcardMgr::factory()->setVCards( $vCards )->jCardFormat();

        // parse jsonString into vCards
        $vCards = PhpVcardMgr::factory()->jcardParse( $jsonString1 )->getVCards();

        // format vCards till vCardString
        $vCardString1 = PhpVcardMgr::factory()->setVCards( $vCards )->vCard4Format();

        // parse vCardString till vCards
        $vCards = PhpVcardMgr::factory()->vCard4Parse( $vCardString1 )->getVCards();

        // format jsonString from Vcards
        $jsonString2 = PhpVcardMgr::factory()->setVCards( $vCards )->jCardFormat();

        $this->assertJsonStringEqualsJsonString(
            $jsonString1,
            $jsonString2,
            __FUNCTION__ . ' Error case ' . $case
        );
    }

    use StructTestProviderTrait;

    /**
     * @dataProvider structTestProvider
     * @test
     */
    public function structTest( int $case, Vcard $vCard ) : void
    {
        $vCardString1 = $vCard->__toString();

        // format vCards into jCardString
        $jCardString = PhpVcardMgr::factory( $vCard )->jCardFormat();

        // parse jCardString into vCards
        $vCards = PhpVcardMgr::factory()->jcardParse( $jCardString )->getVCards();

        $vCardString2 = StringUtil::$SP0;
        foreach( $vCards as $vCard2 ) {
            $vCardString2 .= $vCard2->__toString();
        }

        $this->assertSame(
            $vCardString1,
            $vCardString2,
            __FUNCTION__ . ' error in case ' . $case
        );
    }
}
