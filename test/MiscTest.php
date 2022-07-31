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
use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Anniversary;
use Kigkonsult\PhpVcardMgr\Property\Bday;
use Kigkonsult\PhpVcardMgr\Property\Categories;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\N;
use Kigkonsult\PhpVcardMgr\Property\Nickname;
use Kigkonsult\PhpVcardMgr\Property\Note;
use Kigkonsult\PhpVcardMgr\Property\Org;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Property\Rev;
use Kigkonsult\PhpVcardMgr\Property\Tz;
use Kigkonsult\PhpVcardMgr\Property\Version;
use Kigkonsult\PhpVcardMgr\Property\Xml;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\VcardLoad\Adr  as AdrLoad;
use Kigkonsult\PhpVcardMgr\VcardLoad\Bday as BdayLoad;
use Kigkonsult\PhpVcardMgr\VcardLoad\N    as NLoad;
use Kigkonsult\PhpVcardMgr\VcardLoad\Org  as OrgLoad;

class MiscTest extends BaseTest
{
    /**
     * @var string
     */
    private static $TEST   = 'test';

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
        $vcardtestString = sprintf( $vcardString, Version::VERSION4 );
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vcardtestString ),
            __FUNCTION__ . ' #1 Error'
        );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString, Version::VERSION3 ),
            __FUNCTION__ . ' #2 Error'
        );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString, '5.2' ),
            __FUNCTION__ . ' #3 Error'
        );

        $vcardtestString = sprintf( $vcardString, Version::VERSION3 );
        $this->assertFalse(
            PhpVcardMgr::isVcardString( $vcardtestString ),
            __FUNCTION__ . ' #1 Error'
        );
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vcardtestString, Version::VERSION3 ),
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
        $testProps = [ AdrLoad::load(), NLoad::load(), BdayLoad::load(), OrgLoad::load(), OrgLoad::load() ];
        foreach( $testProps as $property ) {
            $propString = $property->__toString();
            $this->assertStringContainsString( 'CLASS', $propString );
            if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
                self::propDisp( __METHOD__, $property->getPropName(), $propString ); // test ###
            }
        } // end foreach
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
        static $OTHER  = 'other';
        static $ELSE   = 'else';
        $this->assertSame(
            self::$TEST,
            StringUtil::after( $OTHER, $OTHER . self::$TEST )
        );
        $this->assertEmpty(
            StringUtil::after( $OTHER, self::$TEST )
        );

        $this->assertSame(
            self::$TEST,
            StringUtil::before( $OTHER, self::$TEST . $OTHER )
        );
        $this->assertEmpty(
            StringUtil::before( self::$TEST, $OTHER )
        );

        $this->assertEmpty(
            StringUtil::between( self::$TEST, $OTHER, $ELSE )
        );
        $this->assertSame(
            self::$TEST,
            StringUtil::between( $OTHER, $ELSE, $OTHER . self::$TEST )
        );
        $this->assertSame(
            self::$TEST,
            StringUtil::between( $OTHER, $ELSE, self::$TEST . $ELSE )
        );
        $this->assertSame(
            self::$TEST,
            StringUtil::between( $OTHER, $ELSE, $OTHER . self::$TEST . $ELSE )
        );

        $this->assertTrue(
            StringUtil::isXprefixed( 'X-' . self::$TEST )
        );
        $this->assertFALSE(
            StringUtil::isXprefixed( self::$TEST )
        );
    }

    /**
     * Test property group
     *
     * @test
     */
    public function groupTest() : void
    {
        $bday = Bday::factory( new DateTime(), null, null, self::$TEST );
        $this->assertTrue( $bday->isGroupSet());
        $this->assertSame( self::$TEST, $bday->getGroup());
        $this->assertSame( self::$TEST . '.BDAY', $bday->getGroupPropName());

        $prodid = new Prodid();
        $this->assertSame( [], $prodid->getParameters());
        $prodid->setGroup( self::$TEST );
        $prodid->setParameters( [ self::$TEST, self::$TEST ] );
        $this->assertFalse( $prodid->isGroupSet());
        $this->assertEmpty( $prodid->getGroup());
        $this->assertFalse( $prodid->hasParameter());
        $prodid->unsetParameter( self::$TEST );
        $this->assertFalse( $prodid->hasTypeParameter());
        $this->assertFalse( $prodid->hasValueParameter());
        $prodid->setValueType( self::$TEST );
        $this->assertSame( Prodid::TEXT, $prodid->getValueType());
    }

    /**
     * Property parameters test
     *
     * @test
     */
    public function parameterTest() : void
    {
        $bday = Bday::factory( new DateTime(), null, null, self::$TEST )
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
     * Invalid property valueType test
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

        $ok = false;
        try {
            Categories::factory( new DateTime() );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            Note::factory()->setValue( new DateTime() );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test Adr properties setValue
     *
     * @test
     */
    public function adrSetValueTest() : void
    {
        $ok = false;
        try {
            Adr::factory( array_pad( [], 8, self::$TEST ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            Adr::factory( array_pad( [], 7, StringUtil::$SP0 ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        // seven args
        $property = Adr::factory()->setValue(
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST
        );
        $this->assertSame(
            [
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
            ],
            $property->getValue()
        );

        // six args
        $property = Adr::factory()->setValue(
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST,
            self::$TEST
        );
        $this->assertSame(
            [
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                StringUtil::$SP0
            ],
            $property->getValue()
        );

        // seven element array
        $property = Adr::factory()->setValue( array_pad( [], 7, self::$TEST ));
        $this->assertSame(
            array_pad( [], 7, self::$TEST ),
            $property->getValue()
        );

        // sic element array
        $property = Adr::factory()->setValue( array_pad( [], 6, self::$TEST ));
        $this->assertSame(
            [
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                StringUtil::$SP0
            ],
            $property->getValue()
        );

        // toString
        $property = Adr::factory()->setValue( array_pad( [], 7, self::$TEST ));
        $this->assertSame(
            6,
            substr_count( $property->__toString(), StringUtil::$SEMIC )
        );
    }

    /**
     * Test invalid property ClientPidMap value
     *
     * @test
     */
    public function clientPidMapSetValueTest() : void
    {
        $property = ClientPidMap::factory()->setValue( '1 ;' . self::$TEST );
        $this->assertSame(
            [ 1, self::$TEST ],
            $property->getValue()
        );

        $property = ClientPidMap::factory()->setValue( [ 1, self::$TEST ] );
        $this->assertSame(
            [ 1, self::$TEST ],
            $property->getValue()
        );

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
            ClientPidMap::factory( [ 'value', 1 ] );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    /**
     * Test invalid property Gender value
     *
     * @test
     */
    public function genderSetValueTest() : void
    {
        $property = Gender::factory()->setValue( [ 'M', self::$TEST] );
        $this->assertSame(
            [ 'M', self::$TEST ],
            $property->getValue()
        );

        $ok = false;
        try {
            Gender::factory( 123 );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $property = Gender::factory()->setValue( 'M' );
        $this->assertSame(
            [ 'M' ],
            $property->getValue()
        );

        $property = Gender::factory()->setValue( 'M' . StringUtil::$SP0 . StringUtil::$SEMIC . self::$TEST );
        $this->assertSame(
            [ 'M', self::$TEST],
            $property->getValue()
        );

        $ok = false;
        try {
            Gender::factory( 'ABC' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $property = Gender::factory()->setValue( 'M;' );
        $this->assertSame(
            [ 'M' ],
            $property->getValue()
        );
    }

    /**
     * Test N properties setValue
     *
     * @test
     */
    public function nSetValueTest() : void
    {
        $ok = false;
        try {
            N::factory( array_pad( [], 6, self::$TEST ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $ok = false;
        try {
            N::factory( array_pad( [], 5, StringUtil::$SP0 ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        // five args
        $property = N::factory()->setValue( self::$TEST, self::$TEST, self::$TEST, self::$TEST, self::$TEST );
        $this->assertSame(
            array_pad( [], 5, self::$TEST ),
            $property->getValue()
        );

        // four args
        $property = N::factory()->setValue( self::$TEST, self::$TEST, self::$TEST, self::$TEST );
        $this->assertSame(
            [
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                StringUtil::$SP0
            ],
            $property->getValue()
        );

        // five element array
        $property = N::factory()->setValue( array_pad( [], 5, self::$TEST ));
        $this->assertSame(
            array_pad( [], 5, self::$TEST ),
            $property->getValue()
        );

        // four element array
        $property = N::factory()->setValue( array_pad( [], 4, self::$TEST ));
        $this->assertSame(
            [
                self::$TEST,
                self::$TEST,
                self::$TEST,
                self::$TEST,
                StringUtil::$SP0
            ],
            $property->getValue()
        );

        // toString
        $property = N::factory()->setValue( array_pad( [], 5, self::$TEST ));
        $this->assertSame(
            4,
            substr_count( $property->__toString(), StringUtil::$SEMIC )
        );

        // sort-as parameter as input array
        $property = N::factory(
            array_pad( [], 5, self::$TEST ),
            [ N::SORT_AS => [ N::SORT_AS, self::$TEST ]]
        );
        $this->assertSame(
            N::SORT_AS . StringUtil::$COMMA . self::$TEST,
            $property->getParameters( N::SORT_AS )
        );
    }

    /**
     * Test Nickname properties setValue
     *
     * @test
     */
    public function nicknameSetValueTest() : void
    {
        $property = Nickname::factory()->setValue( [ self::$TEST, self::$TEST ] );
        $this->assertSame(
            [ self::$TEST, self::$TEST ],
            $property->getValue()
        );

        $ok = false;
        try {
            Nickname::factory( 123 );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        $property = Nickname::factory()->setValue( self::$TEST . StringUtil::$COMMA . self::$TEST );
        $this->assertSame(
            [ self::$TEST, self::$TEST ],
            $property->getValue()
        );

        $property = Nickname::factory()->setValue( self::$TEST );
        $this->assertSame(
            [ self::$TEST  ],
            $property->getValue()
        );
    }

    /**
     * Test ORG properties setValue
     *
     * @test
     */
    public function orgSetValueTest() : void
    {
        $ok = false;
        try {
            Org::factory( array_pad( [], 5, self::$TEST ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertfalse( $ok );

        $property = Org::factory()->setValue( implode( StringUtil::$SEMIC, [ self::$TEST, self::$TEST] ));
        $this->assertSame(
            array_pad( [], 2, self::$TEST ),
            $property->getValue()
        );

        $property = Org::factory()->setValue( self::$TEST );
        $this->assertSame(
            [ self::$TEST ],
            $property->getValue()
        );

        $ok = false;
        try {
            Org::factory( array_pad( [], 5, StringUtil::$SP0 ));
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );

        // sort-as parameter as input array
        $property = Org::factory(
            array_pad( [], 5, self::$TEST ),
            [ Org::SORT_AS => [ Org::SORT_AS, self::$TEST ]]
        );
        $this->assertSame(
            Org::SORT_AS .StringUtil::$COMMA . self::$TEST,
            $property->getParameters( Org::SORT_AS )
        );
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

    /**
     * Test invalid property date
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
}
