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

class Vcard3Test extends BaseTest
{
    /**
     * @var string
     */
    public static $prodidLabel = 'PRODID:';

    /**
     *
     * @return array
     */
     public function parseTestProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [ // RFC2426, collected examples
            100,
'BEGIN:VCARD
VERSION:3.0' . "\r\n" .
self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
'CLASS:PUBLIC
FN:Mr. John Q. Public\, Esq.
N:Stevenson;John;Philip,Paul;Dr.;Jr.,M.D.,A.C.P.
SORT-STRING:Stevenson\, John
NICKNAME;TYPE=work:Jim,Jimmie
PHOTO;VALUE=uri:http://www.abc.com/pub/photos/jqpublic.gif
PHOTO;TYPE=JPEG;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzEL
 MAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIENvcnBvcmF0aW
 z9uMRwwGgYDVQQLExNJbmZvcm1hdG12345678901 <...remainder of "B" encoded bina
 ry data...>' . "\r\n" .
// BDAY:1996-04-15
'BDAY;VALUE=date-time;ALTID=c9e12760-0b69-3197-bfef-191f11c39a15;CALSCALE=gr
 egorian;LANGUAGE=gv_GB;X-REPUDIANDAE=vero:2013-07-19T03:58:56+00:00
ADR;TYPE="dom,home,postal,parcel":;;123 Main Street;Any Town;CA;91921-1234
LABEL;TYPE="dom,home,postal,parcel":Mr.John Q. Public\, Esq.\nMail Drop: TNE 
 QB\n123 Main Street\nAny Town\, CA  91921-1234 \nU.S.A.
TEL;TYPE="work,voice,pref,msg":+1-213-555-1234
EMAIL;TYPE=internet:jqpublic@xyz.dom1.com
EMAIL;TYPE=internet:jdoe@isp.net
EMAIL;TYPE="internet,pref":jane_doe@abc.com
MAILER:PigeonMail 2.1
TZ:-05:00
TZ;VALUE=text:-05:00; EST; Raleigh/North America
GEO:37.386013;-122.082932
TITLE:Director\, Research and Development
ROLE:Programmer
LOGO;VALUE=uri:http://www.abc.com/pub/logos/abccorp.jpg
LOGO;TYPE=JPEG;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzELM
 AkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIENvcnBvcmF0aW9
 uMRwwGgYDVQQLExNJbmZvcm1h <...the remainder of "B" encoded binary data...>
AGENT;VALUE=uri:CID:JQPUBLIC.part3.960129T083020.xyzMail@host3.com
ORG:ABC\, Inc.;North American Division;Marketing
CATEGORIES:TRAVEL AGENT
CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY
NOTE:This fax number is operational 0800 to 1715 EST\, Mon-Fri.
NOTE:All rfc2426 formtted type eamples
REV:1997-11-15
SOUND;TYPE=BASIC;VALUE=uri:CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@ho
 st1.com
SOUND;TYPE=BASIC;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzE
 LMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIENvcnBvcmF0a
 xW9uMRwwGgYDVQQLExNJbmZvc <...the remainder of "B" encoded binary data...>
URL:http://www.swbyps.restaurant.french/~chezchic.html
KEY;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQAwdzELMAkGA1UEBhMC
 VVMxLDAqBgNVBAoTI05ldHNjYXBlIENbW11bmljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQ
 LExNJbmZvcm1hdGlvbiBTeXN0ZW1zMRwwGgYDVQQDExNyb290Y2EubmV0c2NhcGUuY29tMB4XD
 Tk3MDYwNjE5NDc1OVoXDTk3MTIwMzE5NDc1OVowgYkxCzAJBgNVBAYTAlVTMSYwJAYDVQQKEx1
 OZXRzY2FwZSBDb21tdW5pY2F0aW9ucyBDb3JwLjEYMBYGA1UEAxMPVGltb3RoeSBBIEhvd2VzM
 SEwHwYJKoZIhvcNAQkBFhJob3dlc0BuZXRzY2FwZS5jb20xFTATBgoJkiaJk/IsZAEBEwVob3d
 lczBcMA0GCSqGSIb3DQEBAQUAA0sAMEgCQQC0JZf6wkg8pLMXHHCUvMfL5H6zjSk4vTTXZpYyr
 dN2dXcoX49LKiOmgeJSzoiFKHtLOIboyludF90CgqcxtwKnAgMBAAGjNjA0MBEGCWCGSAGG+EI
 BAQQEAwIAoDAfBgNVHSMEGDAWgBT84FToB/GV3jr3mcau+hUMbsQukjANBgkqhkiG9w0BAQQFA
 AOBgQBexv7o7mi3PLXadkmNP9LcIPmx93HGp0Kgyx1jIVMyNgsemeAwBM+MSlhMfcpbTrONwNj
 ZYW8vJDSoi//yrZlVt9bJbs7MNYZVsyF1unsqaln4/vy6Uawfg8VUMk1U7jt8LYpo4YULU7UZH
 PYVUaSgVttImOHZIKi4hlPXBOhcUQ==
END:VCARD
BEGIN:VCARD
VERSION:3.0' . "\r\n" .
self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
'FN:Frank Dawson
ORG:Lotus Development Corporation
ADR;TYPE="WORK,POSTAL,PARCEL":;;6544 Battleford Drive;Raleigh;NC;27613-3502
 ;U.S.A.
TEL;TYPE="VOICE,MSG,WORK":+1-919-676-9515
TEL;TYPE="FAX,WORK":+1-919-676-9564
EMAIL;TYPE="INTERNET,PREF":Frank_Dawson@Lotus.com
EMAIL;TYPE=INTERNET:fdawson@earthlink.net
URL:http://home.earthlink.net/~fdawson
NOTE:7.  Authors\' Addresses\, page 38
END:VCARD
BEGIN:VCARD
VERSION:3.0' . "\r\n" .
self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
'FN:Tim Howes
ORG:Netscape Communications Corp.
ADR;TYPE=WORK:;;501 E. Middlefield Rd.;Mountain View;CA; 94043;U.S.A.
TEL;TYPE="VOICE,MSG,WORK":+1-415-937-3419
TEL;TYPE="FAX,WORK":+1-415-528-4164
EMAIL;TYPE=INTERNET:howes@netscape.com
NOTE:7.  Authors\' Addresses\, page 38
END:VCARD
'
        ];

        $dataArr[] = [ // Bday with date
            130,
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:3.0' . "\r\n" .
            self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
            'BDAY:1996-04-15' . "\r\n" .
            'END:VCARD' . "\r\n"
        ];
        $dataArr[] = [ // Bday with date and default-value DATE
            131,
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:3.0' . "\r\n" .
            self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
            'BDAY:1996-04-15' . "\r\n" .
            'END:VCARD' . "\r\n"
        ];

        $dataArr[] = [ // Bday with dateTime and TZ with text-value
            140,
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:3.0' . "\r\n" .
            self::$prodidLabel . Prodid::factory()->getValue() . "\r\n" .
            'BDAY;VALUE=date-time:1996-04-15T12:12:12Z' . "\r\n" .
            'TZ;VALUE=text:Stockholm/Europe' . "\r\n" .
            'END:VCARD' . "\r\n"
        ];

        return $dataArr;
    }

    /**
     * @test
     * @dataProvider parseTestProvider
     *
     * @param int $case
     * @param string $vCard3String
     * @throws Exception
     */
    public function parseTest( int $case, string $vCard3String ) : void
    {
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vCard3String, '3.0' ),
            __FUNCTION__ . ' #1 Error in case ' . $case
        );
        $vCard3String = self::conformEols( $vCard3String );

        $vCards        = PhpVcardMgr::factory()->Vcard3Parse( $vCard3String )->getVCards();
        $this->assertIsArray( $vCards );
        // remove auto-created UID
        foreach( $vCards as $vCard ) {
            $vCard->removeProperty( Vcard::UID );
            /*
            if( $vCard->hasProperty( Vcard::BDAY ) &&
                ( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] ))) {  // test ###
                self::propDisp( __METHOD__, $case, $vCard(), PhpVcardMgr::BDAY ); // test ###
            }                                                   // test ###
            */
        }

        $propCnt = [];
        foreach( $vCards as $vCard ) {
            foreach( $vCard->getProperties() as $property ) {
                $propName = $property->getPropName();
                if( Vcard::N === $propName ) { // give problems with BEGIN:VCARD etc
                    continue;
                }
                if( StringUtil::isXprefixed( $propName )) {
                    $propName = substr( $propName, 2 );
                }
                if( ! isset( $propCnt[$propName] )) {
                    $propCnt[$propName] = 0;
                }
                ++$propCnt[$propName];
            } // end foreach
        } // end foreach


        $vCard3String2 = PhpVcardMgr::factory()->setVCards( $vCards )->vCard3Format();

        // check count of all properties without N
        foreach( $propCnt as $propName => $cnt ) {
            $foundCnt = substr_count( $vCard3String2, ( $propName . StringUtil::$COLON )) +
                substr_count( $vCard3String2, ( $propName . StringUtil::$SEMIC ));
            $this->assertSame(
                $cnt,
                $foundCnt,
                __FUNCTION__ . ' Error in case ' . $case .
                ' for propName ' . $propName . ', exp : ' . $cnt . ', found : ' .$foundCnt .
                PHP_EOL . $vCard3String2
            );
        }
        /*
        $this->assertSame(
            $vCard3String,
            $vCard3String2,
            __FUNCTION__ . ' Error in case ' . $case
        );
        */
    }

    /**
     * Format Vcards into Vcard3 string1
     * Parse Vcard3 string1 into Vcard4 Vcards
     * Format Vcards into Vcard3 string2
     * compare string1 and string 2
     *
     * @test
     */
    public function fakerTest1() : void
    {
        $vCards = self::getFakerVcards( 100, true );

        // format vCards into Vcard3String
        $vCard3String1 = PhpVcardMgr::factory()->setVCards( $vCards )->vCard3Format();
        $this->assertTrue(
            PhpVcardMgr::isVcardString( $vCard3String1, '3.0' ),
            __FUNCTION__ . ' #1 Error in case '
        );

        // parse vCardString into vCards and format vCards into Vcard3String
        $vCard3String2 = PhpVcardMgr::factory()->vCard3Parse( $vCard3String1 )->Vcard3Format();

        $this->assertSame(
            $vCard3String1,
            $vCard3String2,
            __FUNCTION__ . ' #2 Error'
        );
    }

    /**
     * Vcard4 Vcards into Vcard4 string1
     * Vcard4 Vcards into Vcard3 string
     * parse Vcard3 string into Vcard4 Vcards
     * Vcard4 Vcards into Vcard4 string2
     * compare string1 and string 2
     *
     * @test
     */
    public function fakerTest2() : void
    {
        $vCards = self::getFakerVcards( 100, true );
        // load PhpVcardMgr
        $vcardCreator1 = PhpVcardMgr::factory()->setVCards( $vCards );

        // format vCards into Vcard4String
        $vCard4String1 = $vcardCreator1->vCard4Format();

        // format vCards into Vcard3String
        $vCard3String  = $vcardCreator1->vCard3Format();

        // parse vCard3String into vCards and format vCards into Vcard4String
        $vCard4String2 = PhpVcardMgr::factory()->vCard3Parse( $vCard3String)->Vcard4Format();

        $this->assertSame(
            $vCard4String1,
            $vCard4String2,
            __FUNCTION__ . ' #1 Error'
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

        // format vCards into Vcard3String
        $vCard3String = PhpVcardMgr::factory( $vCard )->vCard3Format();

        // parse vCard3String into vCards
        $vCards = PhpVcardMgr::factory()->vcard3Parse( $vCard3String )->getVCards();

        $vCardString2 = StringUtil::$SP0;
        foreach( $vCards as $vCard2 ) {
            if(( $property = $vCard2->getProperties( PhpVcardMgr::UID )) &&
                ( PhpVcardMgr::UID === $property->getPropName())) {
                $property->unsetParameter( PhpVcardMgr::VALUE ); // @todo outo-remove in Vcard3Parser of default VALUE?
            }
            $vCardString2 .= $vCard2->__toString();
        }

        $this->assertSame(
            $vCardString1,
            $vCardString2,
            __FUNCTION__ . ' error in case ' . $case
        );
    }
}
