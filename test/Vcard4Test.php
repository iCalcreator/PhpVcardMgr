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
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\VcardLoad\Vcard as VcardLoader;
use RuntimeException;

class Vcard4Test extends BaseTest
{
    /**
     * @var string
     */
    public static $prodidLabel = 'PRODID:';

    /**
     * @test
     */
    public function vcardcreatorTest() : void
    {
        $phpVcardMgr = new PhpVcardMgr( VcardLoader::load());
        $vcards      = $phpVcardMgr->getVCards();
        $this->assertCount( 1, $vcards );
        $this->assertTrue(
            ( 3 < $vcards[0]->count())
        );
    }

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
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vcardString ),
            __FUNCTION__ . ' #1 Error in case ' . $case
        );

        $vcardString = self::conformEols( $vcardString );

        $vCards = PhpVcardMgr::factory()->vCard4Parse( $vcardString )->getVCards();
        $this->assertIsArray( $vCards );
        // display prop
        /*
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) {
            self::propDisp( __METHOD__, $case, $vCards, PhpVcardMgr::Related ); // test ###
        }
        */

        $vcardString2 = PhpVcardMgr::factory()->setVCards( $vCards )->vCard4Format();

        $this->assertSame(
            $vcardString,
            $vcardString2,
            __FUNCTION__ . ' #2 Error in case ' . $case
        );
    }

    /**
     * testing empty leading and trailing rows
     *
     * @test
     */
    public function parseTest2() : void
    {
        $vcardString  =             "\r\n" .
            "\r\n" .
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
            'UID:uid123' . "\r\n" .
            'FN:Mr. John Q. Public\, Esq.' . "\r\n" .
            'END:VCARD' . "\r\n" .
            "\r\n" .
            "\r\n";

        $vCards       = PhpVcardMgr::factory()->vCard4Parse( $vcardString )->getVCards();
        $vcardString2 = PhpVcardMgr::factory()->setVCards( $vCards )->vCard4Format();
        $this->assertSame(
            trim( $vcardString ) . "\r\n",
            $vcardString2,
            __FUNCTION__ . ' Error in case '
        );
    }

    /**
     * @test
     */
    public function parseTest3() : void
    {
        $ok = false;
        try {
            PhpVcardMgr::factory()->vCard4Parse(
                'BEGIN:VCARD' . "\r\n" . 'END:VCARD' . "\r\n"
            );
        }
        catch ( RuntimeException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * format vCards into VcardString
     * parse vCardString into vCards
     * format vCards into VcardString
     *
     * @test
     */
    public function fakerTest() : void
    {
        $vCards1 = self::getFakerVcards( 100 );
        // format vCards into VcardString
        $vCardString1 = PhpVcardMgr::factory()->setVCards( $vCards1 )->vCard4Format();

        // parse vCardString into vCards
        $vCards2 = PhpVcardMgr::factory()->vcard4Parse( $vCardString1 )->getVCards();

        // format vCards into VcardString
        $vCardString2 = PhpVcardMgr::factory()->setVCards( $vCards2 )->vCard4Format();

        $this->assertSame(
            $vCardString1,
            $vCardString2
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

        // format vCards into Vcard4String
        $vCard4String = PhpVcardMgr::factory( $vCard )->vCard4Format();

        // parse vCard4String into vCards
        $vCards = PhpVcardMgr::factory()->vcard4Parse( $vCard4String )->getVCards();

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
