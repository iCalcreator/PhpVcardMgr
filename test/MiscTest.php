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

use DateTime;
use Faker;
use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Property\Anniversary;
use Kigkonsult\PhpVcardMgr\Property\Bday;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\Note;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Property\Rev;
use Kigkonsult\PhpVcardMgr\Property\Tz;
use Kigkonsult\PhpVcardMgr\Property\Version;
use Kigkonsult\PhpVcardMgr\Property\Xml;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\VcardLoad\Adr  as AdrLoad;
use Kigkonsult\PhpVcardMgr\VcardLoad\Bday as BdayLoad;
use Kigkonsult\PhpVcardMgr\VcardLoad\N    as NLoad;

class MiscTest extends BaseTest
{
    /**
     * Test PhpVcardMgr::isVcardString()
     *
     * @test
     */
    public function isVcardStringTest() : void
    {
        $vcardString =
            'BEGIN:VCARD
VERSION:%s
FN:John Doe
UID:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
END:VCARD
';
        $vcardtestString = sprintf( $vcardString, '4.0' );
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vcardtestString ),
            __FUNCTION__ . ' #1 Error'
        );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString, '3.0' ),
            __FUNCTION__ . ' #2 Error'
        );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString, '5.2' ),
            __FUNCTION__ . ' #3 Error'
        );

        $vcardtestString = sprintf( $vcardString, '3.0' );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString ),
            __FUNCTION__ . ' #1 Error'
        );
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vcardtestString, '3.0' ),
            __FUNCTION__ . ' #2 Error'
        );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString, '5.2' ),
            __FUNCTION__ . ' #3 Error'
        );
    }

    /**
     * Test property::__toString
     *
     * @test
     */
    public function propertyToStringTest() : void
    {
        foreach( [ AdrLoad::class, NLoad::class, BdayLoad::class ] as $class ) {
            $propString = $class::load()->__toString();
            $this->assertStringContainsString( 'CLASS', $propString );
            if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
                self::propDisp( __METHOD__, $class, $propString ); // test ###
            }
        }
    }

    /**
     * Tests VcardFormatterUtil::size75()
     *
     * @test
     */
    public function utf8Test() : void
    {
        $string1 = self::getUtf8String();
        $vCard   = Vcard::factory()->addProperty( Note::factory( $string1 ));

        // format vCards into VcardString
        $vCardString = PhpVcardMgr::factory()->addVCard( $vCard )->vCard4Format();

        // parse vCardString into vCards
        $vCard = PhpVcardMgr::factory()->vcard4Parse( $vCardString )->getVCards()[0];

        $string2 = $vCard->getProperties( 'NOTE', true )[0];
        $this->assertSame(
            $string1,
            $string2
        );
    }

    /**
     * https://stackoverflow.com/questions/2748956/how-would-you-create-a-string-of-all-utf-8-characters
     *
     * @param int $i
     * @return string
     */
    private static function unichr( int $i ) : string
    {
        static $UCS = 'UCS-4LE';
        static $UTF = 'UTF-8';
        static $V   = 'V';
        return iconv($UCS, $UTF, pack($V, $i));
    }

    /**
     * @return string
     */
    private static function getUtf8String() : string
    {
        $codeunits = [];
        for( $i = 0x0080; $i < 0x07FF; ( $i += 0xFF )) { // two bytes char
            $codeunits[] = self::unichr( $i );
        }
        for( $i = 0xF900; $i < 0xFFFF; ( $i += 0xFFF )) {  // three bytes chars
            $codeunits[] = self::unichr( $i );
        }
        for( $i = 0x10000; $i < 0x1FFFF; ( $i += 0xFFFF )) {  // four bytes chars
            $codeunits[] = self::unichr( $i );
        }
        for( $i = 0x200000; $i < 0x3FFFFFF; ( $i += 0xFFFF )) {  // five bytes chars
            $codeunits[] = self::unichr( $i );
        }
        for( $i = 0x4000000; $i < 0x7FFFFFFF; ( $i += 0xFFFF )) {  // six bytes chars
            $codeunits[] = self::unichr( $i );
        }
        shuffle( $codeunits );
        // test 75-char-row-split at eol-chars, i.e on split inside '\n'-shars
        //        123456789012345678901234567890123456789012345678901234567890123456789012345
        //                 1         2         3         4         5         6         7         8
        //        NOTE:
        $intro = '----------------------------------------------------------------------\n\n\n\n';
        return $intro . implode( $codeunits );
    }

    /**
     * Test StringUtil
     *
     * @test
     */
    public function stringUtilTest() : void
    {
        static $TEST   = 'test';
        static $OTHER  = 'other';
        static $ELSE   = 'else';
        $this->assertSame(
            $TEST,
            StringUtil::after( $OTHER, $OTHER . $TEST )
        );
        $this->assertEmpty(
            StringUtil::after( $OTHER, $TEST )
        );

        $this->assertSame(
            $TEST,
            StringUtil::before( $OTHER, $TEST . $OTHER )
        );
        $this->assertEmpty(
            StringUtil::before( $TEST, $OTHER )
        );

        $this->assertEmpty(
            StringUtil::between( $TEST, $OTHER, $ELSE )
        );
        $this->assertSame(
            $TEST,
            StringUtil::between( $OTHER, $ELSE, $OTHER . $TEST )
        );
        $this->assertSame(
            $TEST,
            StringUtil::between( $OTHER, $ELSE, $TEST . $ELSE )
        );
        $this->assertSame(
            $TEST,
            StringUtil::between( $OTHER, $ELSE, $OTHER . $TEST . $ELSE )
        );

        $this->assertTrue(
            StringUtil::isXprefixed( 'X-' . $TEST )
        );
        $this->assertFALSE(
            StringUtil::isXprefixed( $TEST )
        );
    }

    /**
     * Test property group
     *
     * @test
     */
    public function groupTest() : void
    {
        static $TEST = 'test';
        $bday = Bday::factory( new DateTime(), null, null, $TEST );
        $this->assertTrue( $bday->isGroupSet());
        $this->assertSame( $TEST, $bday->getGroup());
        $this->assertSame( $TEST . '.BDAY', $bday->getGroupPropName());
    }

    /**
     * Test property parameter
     *
     * @test
     */
    public function parameterTest() : void
    {
        static $TEST = 'test';
        $bday = Bday::factory( new DateTime(), null, null, $TEST )
            ->addParameter( Bday::VALUE, Bday::DATETIME );
        $this->assertTrue( $bday->isValueSet());
        $this->assertTrue( $bday->isParametersSet());
        $bday->unsetParameter( Bday::VALUE );
        $this->assertFalse( $bday->isParametersSet());

        $ok = false;
        try {
            $bday->addParameter( Bday::VALUE, new DateTime() );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            Xml::factory( '<html lang="en">XML</html>', [ Xml::TYPE => 'test' ]  );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            Prodid::factory()->addParameter( 'test', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $prop  = Version::factory();
        $this->assertFalse( $prop::isAnyParameterAllowed());
        $prop2 = $prop->addParameter( 'test', 'test' );
        $this->assertFalse( $prop2->isParametersSet());

        $value = $prop2->getValue();
        $prop3 = $prop2->setValue( '4.1' );
        $this->assertSame(
            $value,
            $prop3->getValue()
        );
    }

    /**
     * Test invalid property valueType
     *
     * @test
     */
    public function invalidValueTypeTest() : void
    {
        $ok = false;
        try {
            Bday::factory( new DateTime(), [], Bday::URI );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test invalid property ClientPidMap value
     *
     * @test
     */
    public function clientPidMapTest() : void
    {
        $ok = false;
        try {
            ClientPidMap::factory( 'value;1' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            $clientPidMap = ClientPidMap::factory( '1;value' );
            $clientPidMap->setValue( [ 'value', 1 ] );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test invalid property date 1
     *
     * @test
     */
    public function datePropertyTest() : void
    {
        $ok = false;
        try {
            Bday::factory( 'ABC' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test invalid property date 2
     *
     * @test
     */
    public function invalidDateTest() : void
    {
        foreach( [ Bday::class, Anniversary::class, Rev::class ] as $propName ) {
            $ok = false;
            try {
                $propName::factory( 'ABC' );
            } catch( InvalidArgumentException $e ) {
                $ok = true;
            }
            $this->assertTrue( $ok );
        }
    }

    /**
     * Test property DateTime input
     *
     * @test
     */
    public function dateTimeTest() : void
    {
        $faker = Faker\Factory::create();
        foreach( [ Bday::class, Anniversary::class, Rev::class ] as $propName ) {
            $dateTime = $faker->dateTime( null, $faker->timezone());
            $offset   = $dateTime->format( Rev::OFFSETfmt );
            $value    = $dateTime->format( Rev::DATETIMEfmt ) . ( empty( $offset ) ? Rev::Zfmt : $offset );
            $property = $propName::factory( $dateTime );
            $this->assertSame(
                $value,
                $property->getValue()
            );
        }
    }

    /**
     * Test invalid property gender value
     *
     * @test
     */
    public function genderTest() : void
    {
        $ok = false;
        try {
            Gender::factory( 'ABC' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test invalid property tz value
     *
     * @test
     */
    public function tzTest() : void
    {
        $ok = false;
        try {
            Tz::factory( 'ABC', [], Tz::UTCOFFSET );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }
}
