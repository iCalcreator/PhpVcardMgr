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
use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Formatter\JcardFormatter;
use Kigkonsult\PhpVcardMgr\Parser\JcardParser;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase implements BaseInterface
{
    /**
     * Vcard
     *   date = year    [month  day]
     *        / year
     *        / year "-" month
     *        / "--"     month
     *        / "--"     month [day]
     *        / "--"      "-"   day
     * Jcard
     *   date-complete = year "-" month "-" day ;YYYY-MM-DD
     *   date-noreduc  = date-complete
     *                 / "--" month "-" day ; --MM-DD
     *                 / "---" day          ; ---DDD
     *   date = date-noreduc
     *        / year; YYYY
     *        / year "-" month ; YYYY-MM
     *        / "--" month     ; --MM
     * @return array
     */
    public function dateProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [
           111,
            self::DATE,
            "19850412",   // Vcard
            "1985-04-12"  // Jcard
        ];

        $dataArr[] = [
            112,
            self::DATE,
            "1985",
            "1985"
        ];

        $dataArr[] = [
            113,
            self::DATE,
            "1985-04",
            "1985-04"
        ];

        $dataArr[] = [
            114,
            self::DATE,
            "--04",
            "--04"
        ];

        $dataArr[] = [
            114,
            self::DATE,
            "--0412",
            "--04-12"
        ];

        $dataArr[] = [
            115,
            self::DATE,
            "---12",
            "---12"
        ];

        return $dataArr;
    }

    /**
     * Jcard
     * time-notrunc =  hour [":" minute [":" second]] [zone]
     * time = time-notrunc
     *      / "-" minute ":" second [zone]; -mm:ss
     *      / "-" minute [zone]; -mm
     *      / "--" second [zone]; --ss
     *
     * @return array
     */
    public function timeProvider() : array
    {
        $zones = $this->zoneProvider();
        $dataArr = [];

        foreach( $zones as $zone ) {
            $dataArr[] = [
                2110 + $zone[0],
                self::TIME,   // time-notrunc
                '23' . $zone[1],
                '23' . $zone[2],
            ];
        }
        foreach( $zones as $zone ) {
            $dataArr[] = [
                2120 + $zone[0],
                self::TIME,   // time-notrunc
                '2320' . $zone[1],
                '23:20' . $zone[2],
            ];
        }
        foreach( $zones as $zone ) {
            $dataArr[] = [
                2130 + $zone[0],
                self::TIME,   // time-notrunc
                '232050' . $zone[1],
                '23:20:50' . $zone[2],
            ];
        }

        foreach( $zones as $zone ) {
            $dataArr[] = [
                2210 + $zone[0],
                self::TIME,   // "-" minute ":" second [zone]; -mm:ss
                '-2320' . $zone[1],
                '-23:20' . $zone[2],
            ];
        }

        foreach( $zones as $zone ) {
            $dataArr[] = [
                2310 + $zone[0],
                self::TIME,   // "-" minute [zone]; -mm
                '-23' . $zone[1],
                '-23' . $zone[2],
            ];
        }

        foreach( $zones as $zone ) {
            $dataArr[] = [
                2410 + $zone[0],
                self::TIME,   // "--" second [zone]; --ss
                '--23' . $zone[1],
                '--23' . $zone[2],
            ];
        }

        return $dataArr;
    }

    /**
     * Vcard
     * zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset     = sign hour [minute]
     *
     * @return array
     */
    public function zoneProvider() : array
    {
        return [
            [ 1, '', '' ],
            [ 2, self::Zfmt, self::Zfmt ],
            [ 3, '+01', '+01'],
            [ 4, '-01', '-01'],
            [ 5, '-0000', '-00:00' ],
            [ 6, '+0000', '+00:00' ],
            [ 7, '+0130', '+01:30'],
            [ 8, '-0130', '-01:30' ],
        ];
    }

    /**
     *
     * Vcard
     *    date-time = date-noreduc  time-designator time-notrunc
     *    date-noreduc  = year     month  day
     *                  / "--"     month  day
     *                  / "--"      "-"   day
     *    time-designator = %x54  ; uppercase "T"
     *    time-notrunc  = hour [minute [second]] [zone]
     * @return array
     */
    public function dateTimeProvider() : array
    {
        $zones   = $this->zoneProvider();
        $dataArr = [];

        $date_noreduc = [
            [ 1, '19841125', '1984-11-25' ],
            [ 1, '--1125',   '--11-25' ],
            [ 1, '---25',    '---25' ],
        ];

        $time_notrunc = [
            [ 1, 'T23',     'T23'],
            [ 1, 'T2311',   'T23:11'],
            [ 1, 'T231123', 'T23:11:23']
        ];
        
        foreach( $date_noreduc as $date ) {
            foreach( $time_notrunc as $time ) {
                foreach( $zones as $zone ) {
                    $dataArr[] = [
                        (int) ( 3 . $date[0] . $time[0] . $zone[0] ),
                        self::DATETIME,
                        $date[1] . $time[1] . $zone[1],
                        $date[2] . $time[2] . $zone[2]
                    ];
                } // end foreach
            } // end foreach
        } // end foreach

        return $dataArr;
    }

    /**
     * Vcard
     *   timestamp = date-complete time-designator time-complete
     *   date-complete = year     month  day
     *   time-designator = %x54  ; uppercase "T"
     *   time-complete = hour  minute  second   [zone]
     *
     * @return array
     */
    public function timestampProvider() : array
    {
        $zones    = $this->zoneProvider();
        $dateTime = new DateTime();
        $ymdHisV  = $dateTime->format( 'Ymd\THis');
        $ymdHisJ  = $dateTime->format( 'Y-m-d\TH:i:s');
        $dataArr  = [];

        foreach( $zones as $zone ) {
            $dataArr[] = [
                (int) ( 400 . $zone[0] ),
                self::TIMESTAMP,
                $ymdHisV . $zone[1],
                $ymdHisJ . $zone[2]
            ];
        }

        return $dataArr;
    }

    /**
     * dataProvider, start case number by order number
     *
     * @return array
     */
    public function dataProvider() : array
    {
        $counter = 0;
        $output  = [];
        foreach( $this->dateProvider() as $dataSet ) {
            $dataSet[0] = $counter++ . '-' . $dataSet[0];
            $output[]   = $dataSet;
        }
        foreach( $this->timeProvider() as $dataSet ) {
            $dataSet[0] = $counter++ . '-' . $dataSet[0];
            $output[]   = $dataSet;
        }
        foreach( $this->dateTimeProvider() as $dataSet ) {
            $dataSet[0] = $counter++ . '-' . $dataSet[0];
            $output[]   = $dataSet;
        }
        foreach( $this->timestampProvider() as $dataSet ) {
            $dataSet[0] = $counter++ . '-' . $dataSet[0];
            $output[]   = $dataSet;
        }
        return $output;
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $case
     * @param string $valueType
     * @param string $vCardValue
     * @param string $jCardValue
     */
    public function isValueTypeTest( string $case, string $valueType, string $vCardValue, string $jCardValue ) : void
    {
//      error_log( __METHOD__ . ' start #' . $case . ' value : ' . $vCardValue ); // test ###

        switch( $valueType ) {
            case self::DATE :
                $this->assertTrue(
                    DateUtil::isVcardDate( $vCardValue ),
                    __FUNCTION__ . ' error case #' . $case . '-1 : ' . $vCardValue
                );
                break;
            case self::TIME :
                $this->assertTrue(
                    DateUtil::isVcardTime( $vCardValue ),
                    __FUNCTION__ . ' error case #' . $case . '-3 : ' . $vCardValue
                );
                break;
            case self::DATETIME :
                $this->assertTrue(
                    DateUtil::isVcardDateTime( $vCardValue ),
                    __FUNCTION__ . ' error case #' . $case . '-5 : ' . $vCardValue
                );
                break;
            case self::TIMESTAMP :
                $this->assertTrue(
                    DateUtil::isVcardTimestamp( $vCardValue ),
                    __FUNCTION__ . ' error case #' . $case . '-6 : ' . $vCardValue
                );
                break;
            default :
                $this->assertTrue(
                    (
                        DateUtil::isVcardDate( $vCardValue ) ||
                        DateUtil::isVcardTime( $vCardValue ) ||
                        DateUtil::isVcardDateTime( $vCardValue )
                    ),
                    __FUNCTION__ . ' error case #' . $case . '-7 : ' . $vCardValue
                );
                break;
        } // end switch
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function jsonTest( string $case, string $valueType, string $vCardValue, string $jCardValue ) : void
    {
        $tmpl1 =
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            'PRODID:' . Prodid::factory()->getValue() . "\r\n" .
            '%s;VALUE=%s:%s' . "\r\n" .
            'FN:Kalle Test' . "\r\n" .
            'END:VCARD' . "\r\n";
        $tmpl2 = ';VALUE=%s';

//      error_log( 'START #' . $case . ' type ' . $valueType . ' vcard ' . $vCardValue ); // test ###

        $isTimeStamp =  ( self::TIMESTAMP === $valueType );
        $vCardString1 = sprintf(
            $tmpl1,
            $isTimeStamp ? self::REV : self::BDAY,
            $valueType,
            $vCardValue
        );
        // parse vCardString till vCards
        $vCards = PhpVcardMgr::factory()->parse( $vCardString1 )->getVCards();

        if( $isTimeStamp ) {
            $vCardString1 = str_replace( sprintf( $tmpl2, $valueType ), '', $vCardString1 );
        }

        // remove auto-created UID
        foreach( $vCards as $vCard ) {
            $vCard->removeProperty( self::UID );
        }
        // format jsonString from Vcards
        $jsonString = PhpVcardMgr::factory()->setVCards( $vCards )->format( new JcardFormatter());

        $this->assertStringContainsString(
            $jCardValue,
            $jsonString,
            __FUNCTION__ . ' case ' . $case . '#1, valueType ' . $valueType . ', jcard ' . $jCardValue . ' NOT found'
        );

        // parse jsonString into (vCards into) vCardString
        $vCards = PhpVcardMgr::factory()->parse( $jsonString, new JcardParser())->getVCards();
        // remove auto-created UID
        foreach( $vCards as $vCard ) {
            $vCard->removeProperty( Vcard::UID );
        }

        $vCardString2 = PhpVcardMgr::factory()->setVCards( $vCards )->format();

        $this->assertSame(
            $vCardString1,
            $vCardString2,
            __FUNCTION__ . ' case ' . $case . '#2, test ' . $vCardValue
        );
    }

    /**
     * Test DateUtil, exceptions
     *
     * @test
     */
    public function exceptionsTest() : void
    {
        $ok = false;
        try {
            DateUtil::assertVcardDateAndOrTime( 'Bday', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 1 );

        $ok = false;
        try {
            DateUtil::assertVcardDateTime( 'Bday', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 2 );

        $ok = false;
        try {
            DateUtil::assertVcardDate( 'Bday', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 3 );

        $ok = false;
        try {
            DateUtil::assertVcardTime( 'Bday', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 4 );

        $ok = false;
        try {
            DateUtil::assertVcardTimestamp( 'Rev', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 4 );

        $ok = false;
        try {
            DateUtil::assertVcardOffset( 'TZ', 'test' );
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, __FUNCTION__ . '#' . 5 );
    }

    /**
     * Test DateUtil::convertVcard2JcardDates
     *
     * @test
     */
    public function convertVcard2JcardDatesTest() : void
    {
        $this->assertSame(
            '2022-12-12',
            DateUtil::convertVcard2JcardDates( '20221212', DateUtil::DATETIME ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            '2022-12-12',
            DateUtil::convertVcard2JcardDates( '2022-12-12', DateUtil::TEXT ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertVcard2JcardDate
     *
     * @test
     */
    public function convertVcard2JcardDateTest() : void
    {
        $this->assertSame(
            '2022',
            DateUtil::convertVcard2JcardDate( '2022' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            'ABC',
            DateUtil::convertVcard2JcardDate( 'ABC' ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertVcard2JcardTime
     *
     * @test
     */
    public function convertVcard2JcardTimeTest() : void
    {
        $time = '--22';
        $this->assertSame(
            $time,
            DateUtil::convertVcard2JcardTime( $time ),
            __FUNCTION__ . '#' . 1
        );
        $time = '9876543210';
        $this->assertSame(
            $time, // return as-is
            DateUtil::convertVcard2JcardTime( $time ),
            __FUNCTION__ . '#' . 2
        );
        $time = '9876ABC';
        $this->assertSame(
            $time, // return as-is
            DateUtil::convertVcard2JcardTime( $time ),
            __FUNCTION__ . '#' . 2
        );
        $time = '98ABC';
        $this->assertSame(
            $time, // return as-is
            DateUtil::convertVcard2JcardTime( $time ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertVcard2JcardZone
     *
     * @test
     */
    public function convertVcard2JcardZoneTest() : void
    {
        $this->assertSame(
            'ABC',
            DateUtil::convertVcard2JcardZone( 'ABC' ),
            __FUNCTION__ . '#' . 1
        );
    }

    /**
     * Test DateUtil::convertVcard2JcardTimestamp
     *
     * @test
     */
    public function convertVcard2JcardTimestampTest() : void
    {
        $this->assertSame(
            'ABC',
            DateUtil::convertVcard2JcardTimestamp( 'ABC' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            '2022-12-12T12:12:12',
            DateUtil::convertVcard2JcardTimestamp( '20221212T121212' ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertJcard2VcardDate
     *
     * @test
     */
    public function convertJcard2VcardDateTest() : void
    {
        $this->assertSame(
            'ABC',
            DateUtil::convertJcard2VcardDate( 'ABC' ),
            __FUNCTION__ . '#' . 1
        );
    }

    /**
     * Test DateUtil::convertJcard2VcardDates
     *
     * @test
     */
    public function convertJcard2VcardDatesTest() : void
    {
        $this->assertSame(
            '20221212',
            DateUtil::convertJcard2VcardDates( '2022-12-12', self::DATETIME ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            '2022-12-12T12:12:12',
            DateUtil::convertJcard2VcardDates( '2022-12-12T12:12:12', self::TEXT ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertJcard2VcardTime
     *
     * @test
     */
    public function convertJcard2VcardTimeTest() : void
    {
        $this->assertSame(
            '--12',
            DateUtil::convertJcard2VcardTime( '--12' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            '-ABC',
            DateUtil::convertJcard2VcardTime( '-ABC' ),
            __FUNCTION__ . '#' . 2
        );
        $this->assertSame(
            '221212',
            DateUtil::convertJcard2VcardTime( '22:12:12' ),
            __FUNCTION__ . '#' . 3
        );
        $this->assertSame(
            '2212',
            DateUtil::convertJcard2VcardTime( '22:12' ),
            __FUNCTION__ . '#' . 4
        );
        $this->assertSame(
            '22',
            DateUtil::convertJcard2VcardTime( '22' ),
            __FUNCTION__ . '#' . 5
        );
    }

    /**
     * Test DateUtil::convertJcard2VcardZone
     *
     * @test
     */
    public function convertJcard2VcardZoneTest() : void
    {
        $this->assertSame(
            '',
            DateUtil::convertJcard2VcardZone( '' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertSame(
            'ABC',
            DateUtil::convertJcard2VcardZone( 'ABC' ),
            __FUNCTION__ . '#' . 2
        );
    }

    /**
     * Test DateUtil::convertJcard2VcardTimestamp
     *
     * @test
     */
    public function convertJcard2VcardTimestampTest() : void
    {
        $this->assertSame(
            'QWERTYUIOPASDFGHJKZ',
            DateUtil::convertJcard2VcardTimestamp( 'QWERTYUIOPASDFGHJKZ' ),
            __FUNCTION__ . '#' . 1
        );
    }

    /**
     * Test DateUtil::isVcardDate
     *
     * @test
     */
    public function isVcardDateTest() : void
    {
        $this->assertFalse(
            DateUtil::isVcardDate( '20221313' ), // case 1
            __FUNCTION__ . '#' . 1
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '20221232' ), // case 1
            __FUNCTION__ . '#' . 2
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '202212XX' ), // case 1
            __FUNCTION__ . '#' . 3
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '2022-13' ),  // case 2
            __FUNCTION__ . '#' . 4
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '--13' ),     // case 3
            __FUNCTION__ . '#' . 5
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '--1232' ),   // case 3
            __FUNCTION__ . '#' . 6
        );
        $this->assertFalse(
            DateUtil::isVcardDate( '---32' ),    // case 4
            __FUNCTION__ . '#' . 7
        );
    }

    /**
     * Test DateUtil::isVcardOffset
     *
     * @test
     */
    public function isVcardOffsetTest() : void
    {
        $this->assertFalse(
            DateUtil::isVcardOffset( '0130' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertTrue(
            DateUtil::isVcardOffset( '+01' ),
            __FUNCTION__ . '#' . 2
        );
        $this->assertTrue(
            DateUtil::isVcardOffset( '+0130' ),
            __FUNCTION__ . '#' . 3
        );
        $this->assertFalse(
            DateUtil::isVcardOffset( '+3330' ),
            __FUNCTION__ . '#' . 4
        );
        $this->assertFalse(
            DateUtil::isVcardOffset( '+0166' ),
            __FUNCTION__ . '#' . 5
        );
        $this->assertFalse(
            DateUtil::isVcardOffset( '+ABC' ),
            __FUNCTION__ . '#' . 6
        );
    }

    /**
     * Test DateUtil::isVcardZone / isJcardZone
     *
     * @test
     */
    public function isZoneTest() : void
    {
        $this->assertTrue(
            DateUtil::isVcardZone( '' ),
            __FUNCTION__ . '#' . 1
        );
        $this->assertfalse(
            DateUtil::isJcardZone( '' ),
            __FUNCTION__ . '#' . 2
        );

        foreach( [ 1 => 'isVcardZone', 2 => 'isJcardZone' ] as $zix => $method ) {
            $this->assertTrue(
                DateUtil::{$method}( '+0000' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertTrue(
                DateUtil::{$method}( self::Zfmt ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( '0000' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( 'ABC' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertTrue(
                DateUtil::{$method}( '+01' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( '+33' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertTrue(
                DateUtil::{$method}( '+0130' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( '+3330' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( '+0166' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
            $this->assertFalse(
                DateUtil::{$method}( '+ABC' ),
                __FUNCTION__ . '#' . 3 . '-' . $method . '-' . $zix
            );
        } // end foreach
    }

    /**
     * Test DateUtil::isVcardTimestamp
     *
     * @test
     */
    public function isVcardTimestampTest() : void
    {
        $this->assertFalse(
            DateUtil::isVcardTimestamp( '99999999T999999' ),
            __FUNCTION__ . '#' . 1
        );
        $timestamp = new DateTime();
        $this->assertTrue(
            DateUtil::isVcardTimestamp( $timestamp->format( 'Ymd\THis' )),
            __FUNCTION__ . '#' . 2
        );
        $this->assertTrue(
            DateUtil::isVcardTimestamp( $timestamp->format( 'Ymd\THis\Z' )),
            __FUNCTION__ . '#' . 3
        );
    }
}
