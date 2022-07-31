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

use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

trait Vcard4StringProviderTrait
{
    /**
     * @return array
     */
    public function vCard4StringProvider()
    {
        $dataArr = [];

        $dataArr[] = [ // RFC6350, page 54-56 BUT with Prodid included, TEL params order changed
            100,
            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b1' . "\r\n" .
            'FN;PID=1.1:J. Doe' . "\r\n" .
            'N:Doe;J.;;;' . "\r\n" .
            'EMAIL;PID=1.1:jdoe@example.com' . "\r\n" .
            'EMAIL;PID=2.1:boss@example.com' . "\r\n" .
            'TEL;PID=1.1:tel:+1-555-555-5555' . "\r\n" .
            'TEL;PID=2.1:tel:+1-666-666-6666' . "\r\n" .
            'CLIENTPIDMAP:1;urn:uuid:53e374d9-337e-4727-8803-a1e9c14e0556' . "\r\n" .
            'END:VCARD' . "\r\n" .

            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b1' . "\r\n" .
            'FN;PID=1.1:J. Doe' . "\r\n" .
            'N:Doe;J.;;;' . "\r\n" .
            'EMAIL;PID=1.1:jdoe@example.com' . "\r\n" .
            'EMAIL;PID=2.2:ceo@example.com' . "\r\n" .
            'TEL;PID=1.1:tel:+1-555-555-5555' . "\r\n" .
            'TEL;PID=2.2:tel:+1-666-666-6666' . "\r\n" .
            'CLIENTPIDMAP:1;urn:uuid:53e374d9-337e-4727-8803-a1e9c14e0556' . "\r\n" .
            'CLIENTPIDMAP:2;urn:uuid:1f762d2b-03c4-4a83-9a03-75ff658a6eee' . "\r\n" .
            'END:VCARD' . "\r\n" .

            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b1' . "\r\n" .
            'FN:J. Doe' . "\r\n" .
            'N:Doe;J.;;;' . "\r\n" .
            'EMAIL;PID=1.1:jdoe@example.com' . "\r\n" .
            'EMAIL;PID=2.1:boss@example.com' . "\r\n" .
            'EMAIL;PID=2.2:ceo@example.com' . "\r\n" .
            'TEL;PID=1.1:tel:+1-555-555-5555' . "\r\n" .
            'TEL;PID=2.1,2.2:tel:+1-666-666-6666' . "\r\n" .
            'CLIENTPIDMAP:1;urn:uuid:53e374d9-337e-4727-8803-a1e9c14e0556' . "\r\n" .
            'CLIENTPIDMAP:2;urn:uuid:1f762d2b-03c4-4a83-9a03-75ff658a6eee' . "\r\n" .
            'END:VCARD' . "\r\n" .

            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b1' . "\r\n" .
            'FN:J. Doe' . "\r\n" .
            'N:Doe;J.;;;' . "\r\n" .
            'EMAIL;PID=1.1:jdoe@example.com' . "\r\n" .
            'EMAIL;PID=2.1:boss@example.com' . "\r\n" .
            'EMAIL;PID=3.1:ceo@example.com' . "\r\n" .
            'TEL;PID=1.1:tel:+1-555-555-5555' . "\r\n" .
            'TEL;PID=2.1:tel:+1-666-666-6666' . "\r\n" .
            'CLIENTPIDMAP:1;urn:uuid:53e374d9-337e-4727-8803-a1e9c14e0556' . "\r\n" .
            'END:VCARD' . "\r\n" .

            'BEGIN:VCARD' . "\r\n" .
            'VERSION:4.0' . "\r\n" .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b2' . "\r\n" .
            'FN:Simon Perreault' . "\r\n" .
            'N:Perreault;Simon;;;ing. jr,M.Sc.' . "\r\n" .
            'NICKNAME;TYPE=work:Jim,Jimmie' . "\r\n" .
// 'BDAY:--0203' . "\r\n" .
            'BDAY:20200203' . "\r\n" .
            'ANNIVERSARY:20090808T1430-0500' . "\r\n" .
            'GENDER:M;comment' . "\r\n" .
            'LANG;PREF=1:fr' . "\r\n" .
            'LANG;PREF=2:en' . "\r\n" .
            'ORG;TYPE=work:Viagenie' . "\r\n" .
            'ADR;TYPE=work:;Suite D2-630;2875 Laurier;Quebec;QC;G1V 2M2;Canada' . "\r\n" .
            'TEL;TYPE="work,voice";PREF=1:tel:+1-418-656-9254;ext=102' . "\r\n" .
            'TEL;TYPE="work,cell,voice,video,text":tel:+1-418-262-6501' . "\r\n" .
            'TEL:tel:+1-418-262-6501' . "\r\n" .
            'EMAIL;TYPE=work:simon.perreault@viagenie.ca' . "\r\n" .
            'GEO;TYPE=work:geo:46.772673,-71.282945' . "\r\n" .
            'KEY;TYPE=work:http://www.viagenie.ca/simon.perreault/simon.asc' . "\r\n" .
            'TZ:-0500' . "\r\n" .
            'URL;TYPE=home:http://nomis80.org' . "\r\n" .
            'END:VCARD' . "\r\n"
        ];

        $dataArr[] = [ // RFC6350, page 41 with Prodid included
            110,
            'BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b3
KIND:group
FN:The Doe family
MEMBER:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
MEMBER:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
FN:John Doe
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
FN:Jane Doe
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b6
KIND:group
FN:Funky distribution list
MEMBER:mailto:subscriber1@example.com
MEMBER:xmpp:subscriber2@example.com
MEMBER:sip:subscriber3@example.com
MEMBER:tel:+1-418-555-5555
X-PROP1:x-property1
X-PROP2;VALUE=date:20220615
END:VCARD
'
        ];

        $dataArr[] = [ // RFC6350, All 'Example:' in one with Prodid included
            120,
            'BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6
KIND:group
REV:19951031T222710Z
FN:Mr. John Q. Public\, Esq.
ADR;LABEL="Mr. John Q. Public, Esq. Mail Drop: TNE QB\n123 Main Street\nAny
  Town, CA  91921-1234 U.S.A.";GEO="geo:12.3457,78.910":;;123 Main Street;A
 ny Town;CA;91921-1234;U.S.A.
LANG;PREF=1;TYPE=work:en
LANG;PREF=2;TYPE=work:fr
LANG;TYPE=home:fr
TITLE:Research Scientist
ROLE:Project Leader
LOGO:http://www.example.com/pub/logos/abccorp.jpg
LOGO:data:image/jpeg;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcAQEEBQAwdzEL
 MAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIENvcnBvcmF0aW
 <...the remainder of base64-encoded data...>
ORG:ABC\, Inc.;North American Division;Marketing
MEMBER:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
MEMBER:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
RELATED;TYPE=friend:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6
RELATED;TYPE=contact:http://example.com/directory/jdoe.vcf
RELATED;VALUE=text;TYPE=co-worker:Please contact my assistant Jane Doe for ' . "\r\n" .
            ' any inquiries.
TEL;TYPE="voice,home";PREF=1:tel:+1-555-555-5555;ext=5555
TEL;TYPE=home;PREF=2:tel:+33-01-23-45-67
TEL;PID=3.1,4.2:tel:+1-555-555-5555
EMAIL;PREF=1:jane_doe@example.com
EMAIL;TYPE=work:jqpublic@xyz.example.com
IMPP;PREF=1:xmpp:alice@example.com
GEO:geo:37.386013,-122.082932
TZ:Raleigh/North America
TZ;VALUE=utc-offset:-0500' . // ; Note: utc-offset format is NOT RECOMMENDED.
            '
KEY:http://www.example.com/keys/jdoe.cer
KEY;MEDIATYPE=application/pgp-keys:ftp://example.com/keys/jdoe
KEY:data:application/pgp-keys;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQ
 <... remainder of base64-encoded data ...>
CLIENTPIDMAP:1;urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b
CLIENTPIDMAP:2;urn:uuid:d89c9c7a-2e1b-4832-82de-7e992d95faa5
URL:http://example.org/restaurant.french/~chezchic.html
CATEGORIES:TRAVEL AGENT
CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY
NOTE:This fax number is operational 0800 to 1715 EST\, Mon-Fri.
SOUND:CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@example.com
SOUND:data:audio/basic;base64,MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhAQEEBQAwdzEL
 MAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bmljYXRpb25zIENvcnBvcmF0aW
  <...the remainder of base64-encoded data...>
FBURL;PREF=1:http://www.example.com/busy/janedoe
FBURL;MEDIATYPE=text/calendar:ftp://example.com/busy/project-a.ifb
CALADRURI;PREF=1:mailto:janedoe@example.com
CALADRURI:http://example.com/calendar/jdoe
CALURI;PREF=1:http://cal.example.com/calA
CALURI;MEDIATYPE=text/calendar:ftp://ftp.example.com/calA.ics
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af
FN:John Doe
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:b8767877-b4a1-4c70-9acc-505d3819e519
FN:Jane Doe
END:VCARD
BEGIN:VCARD
VERSION:4.0
' .
            Prodid::PRODID . StringUtil::$COLON . Prodid::factory()->getValue() . "\r\n" .
            'UID:urn:uuid:4fbe8971-0bc3-424c-9c26-36c3e1eff6b8
KIND:group
FN:Funky distribution list
group1.MEMBER:mailto:subscriber1@example.com
group1.MEMBER:xmpp:subscriber2@example.com
group2.MEMBER:sip:subscriber3@example.com
group2.MEMBER:tel:+1-418-555-5555
END:VCARD
'
        ];

        return $dataArr;
    }
}
