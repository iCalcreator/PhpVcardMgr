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

use DOMNode;
use Exception;
use Kigkonsult\PhpVcardMgr\Parser\XcardParser;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use RuntimeException;

class XcardTest extends BaseTest
{
    use Vcard4StringProviderTrait;

    /**
     * Parse rfc6350 vCardString into vCards
     * Format vCards into xCardXml
     * Parse xCardXml into vCards
     * Format vCards into vCardString
     *
     * @test
     * @dataProvider vCard4StringProvider
     *
     * @param int $case
     * @param string $vcardString
     * @throws Exception
     */
    public function parse1Test( int $case, string $vcardString ) : void
    {
        $vcardString = self::conformEols( $vcardString );

        // parse vCardString into vCards
        $phpVcardMgr = PhpVcardMgr::factory()->vCard4Parse( $vcardString );
        // display props
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, $case, $phpVcardMgr->getVCards(), PhpVcardMgr::GENDER ); // test ##
        }
        // Format vCards into xCardXml
        $xCardXml    = $phpVcardMgr->xCardFormat();

        // display xml
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, $case, $xCardXml,  PhpVcardMgr::GENDER ); // test ###
        }

        // parse xCardXml into vCards and format vCards into vCardStringg1
        $phpVcardMgr = PhpVcardMgr::factory()->xCardParse( $xCardXml );

        // display props
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, $case, $phpVcardMgr->getVCards(), PhpVcardMgr::GENDER ); // test ###
        }
        $vcardString2 = $phpVcardMgr->vCard4Format();

        $this->assertSame(
            $vcardString,
            $vcardString2,
            __FUNCTION__ . ' #2 Error in case ' . $case
        );
    }

    /**
     * @return array
     */
    public function parseTest2Provider() : array
    {
        $dataArr = [];

        $dataArr[] = [ // RFC6351, 4.  Example: Author's XML vCard with added propdid/uid, corr adr:street
            100,
            '<?xml version="1.0" encoding="UTF-8"?>
   <vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
     <vcard>
       <prodid>
         <text>' . Prodid::factory()->getValue() . '</text>
       </prodid>
       <uid>
         <uri>urn:uuid:bfbe74ef-47c4-43c7-bab8-b06467ca7fcc</uri>
       </uid>
       <fn>
        <text>Simon Perreault</text>
       </fn>
       <n>
         <surname>Perreault</surname>
         <given>Simon</given>
         <additional/>
         <prefix/>
         <suffix>ing. jr</suffix>
         <suffix>M.Sc.</suffix>
       </n>
       <bday>
        <date>--0203</date>
       </bday>
       <anniversary>
         <date-time>20090808T1430-0500</date-time>
       </anniversary>
       <gender>
        <sex>M</sex>
       </gender>
       <lang>
         <parameters>
          <pref>
           <integer>1</integer>
          </pref>
         </parameters>
         <language-tag>fr</language-tag>
       </lang>
       <lang>
         <parameters><pref><integer>2</integer></pref></parameters>
         <language-tag>en</language-tag>
       </lang>
       <org>
         <parameters><type><text>work</text></type></parameters>
         <text>Viagenie</text>
       </org>
       <adr>
         <parameters>
           <label><text>Simon Perreault
   2875 boul. Laurier, suite D2-630
   Quebec, QC, Canada
   G1V 2M2</text></label>
           <type><text>work</text></type>
         </parameters>
         <pobox/>
         <ext/>
         <street>2875 boul. Laurier</street>
         <street>suite D2-630</street>
         <locality>Quebec</locality>
         <region>QC</region>
         <code>G1V 2M2</code>
         <country>Canada</country>
       </adr>
       <tel>
         <parameters>
           <type>
             <text>work</text>
             <text>voice</text>
           </type>
         </parameters>
         <uri>tel:+1-418-656-9254;ext=102</uri>
       </tel>
       <tel>
         <parameters>
           <type>
             <text>work</text>
             <text>text</text>
             <text>voice</text>
             <text>cell</text>
             <text>video</text>
           </type>
         </parameters>
         <uri>tel:+1-418-262-6501</uri>
       </tel>
       <email>
         <parameters><type><text>work</text></type></parameters>
         <text>simon.perreault@viagenie.ca</text>
       </email>
       <geo>
         <parameters><type><text>work</text></type></parameters>
         <uri>geo:46.766336,-71.28955</uri>
       </geo>
       <key>
         <parameters><type><text>work</text></type></parameters>
         <uri>http://www.viagenie.ca/simon.perreault/simon.asc</uri>
       </key>
       <tz><text>America/Montreal</text></tz>
       <url>
         <parameters><type><text>home</text></type></parameters>
         <uri>http://nomis80.org</uri>
       </url>
     </vcard>
   </vcards>
'
        ];

        return $dataArr;
    }

    /**
     * Parse rfc6351 xml into vCards
     * Format Vcards into Vcard4 vCard4String
     * Parse vCard4String into Vcard4 Vcards
     * Format vCards into xml
     *
     * @test
     * @dataProvider parseTest2Provider
     *
     * @param int $case
     * @param string $xml
     * @throws Exception
     */
    public function parse2Test( int $case, string $xml ) : void
    {
        // parse xCardXml into vCards
        $vCards = PhpVcardMgr::factory()->xCardParse( $xml )->getVCards();
        /*
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) {
            self::propDisp( __METHOD__, $case . '-1', $vCards, PhpVcardMgr::ADR ); // test ###
        }
        */

        // format vCards into vCardString
        $vCardString = PhpVcardMgr::factory()->setVCards( $vCards )->vCard4Format();

        // parse vCardString into vCards
        $vCards = PhpVcardMgr::factory()->vCard4Parse( $vCardString )->getVCards();
        /*
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) {
            self::propDisp( __METHOD__, $case . '-2', $vCards, PhpVcardMgr::ADR ); // test ###
        }
        */

        // format vCards into xml
        $xml2 = PhpVcardMgr::factory()->setVCards( $vCards )->xCardFormat();

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $xml2,
            __FUNCTION__ . ' #1 Error, case ' . $case
        );

    }

    /**
     * Format (faker) Vcards into vCard4String1
     * Format Vcards into xCardXml
     * Parse xCardXml into Vcard4 Vcards
     * Format Vcards into Vcard4 vCard4String2
     * compare string1 and string 2
     *
     * @test
     */
    public function fakerTest() : void
    {
        $vCards = self::getFakerVcards( 100 );
        /*
        foreach( $vCards as $vCard ) {
            foreach( $vCard->getProperties() as $property ) {
                if( Vcard::XML === $property->getPropName()) {
                    $vCard->removeProperty( Vcard::XML );
                }
            } // end foreach
        } // end foreach
        */
        $phpVcardMgr = PhpVcardMgr::factory()->setVCards( $vCards );

        // display prop
        /*
        $propToTest = PhpVcardMgr::GENDER;
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, 1, $phpVcardMgr->getVCards(), PhpVcardMgr::GENDER ); // test ###
        }
        */

        // format vCards into vCardString
        $vCardString1 = $phpVcardMgr->vCard4Format();
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, 1, $vCardString1 ); // test ###
        }
        // format vCards into xCardXml
        $xCardXml = $phpVcardMgr->xCardFormat();

        // display prop
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, 1, $xCardXml ); // test ###
        }

        // parse xCardXml get DomNode
        /*
        $this->assertInstanceOf(
            DOMNode::class,
            XcardParser::factory()->parse( $xCardXml, true )
        );
        */

        // parse xCardXml into vCards
        $phpVcardMgr = PhpVcardMgr::factory()->xCardParse( $xCardXml );

        // display prop
        /*
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) {
            self::propDisp( __METHOD__, $case, $phpVcardMgr->getVCards(), PhpVcardMgr::GENDER ); // test ##
        }
        */

        // format vCards into vCardString
        $vCardString2 = $phpVcardMgr->vCard4Format();

        $this->assertSame(
            $vCardString1,
            $vCardString2,
            __FUNCTION__ . ' #1 Error'
        );
    }

    /**
     * Test unvalid xml...
     *
     * @test
     */
    public function xmlTest() : void
    {
        $ok = false;
        try {
            PhpVcardMgr::factory()->xCardParse( 'ABC' );
        }
        catch ( RuntimeException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok );
    }

    use StructTestProviderTrait;

    /**
     * @dataProvider structTestProvider
     * @test
     */
    public function structTest( int $case, Vcard $vCard ) : void
    {
        $vCardString1 = $vCard->__toString();

        // format vCards into xCardXml
        $xCardXml = PhpVcardMgr::factory( $vCard )->xCardFormat();

        // display
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, $case . '-1', $xCardXml, 'xCardXml' ); // test ###
        }

        // parse xCardXml into vCards
        $vCards2 = PhpVcardMgr::factory()->xCardParse( $xCardXml )->getVCards();

        // remove parameter[VALUE]
        foreach( $vCards2 as $vCard2 ) {
            foreach( [ Vcard::GENDER, Vcard::N, Vcard::UID ] as $propName ) {
                if( $vCard2->hasProperty( $propName )) {
                    $vCard2->getProperties( $propName )->unsetParameter( Vcard::VALUE );
                }
            } // emd foreach
            foreach( [ Vcard::ADR, Vcard::CATEGORIES, Vcard::CLIENTPIDMAP, Vcard::NICKNAME, Vcard::ORG ] as $propName ) {
                if( $vCard2->hasProperty( $propName )) {
                    foreach( $vCard2->getProperties( $propName ) as $propName2 ) {
                        $propName2->unsetParameter( Vcard::VALUE );
                    }
                }
            }
        } // emd foreach

        $vCardString2 = StringUtil::$SP0;
        foreach( $vCards2 as $vCard2 ) {
            $vCardString2 .= $vCard2->__toString();
        }

        // display
        if( isset( $GLOBALS['dispInErrLog'] ) && ( 1 == $GLOBALS['dispInErrLog'] )) { // note ==
            self::propDisp( __METHOD__, $case . '-2', $xCardXml, 'xCardXml' ); // test ###
        }

        $this->assertSame(
            $vCardString1,
            $vCardString2,
            __FUNCTION__ . ' error in case ' . $case
        );
    }
}
