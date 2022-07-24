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

use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Categories;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\N;
use Kigkonsult\PhpVcardMgr\Property\Nickname;
use Kigkonsult\PhpVcardMgr\Property\Org;

trait StructTestProviderTrait
{
    /**
     * DataProvider with Vcard and Properties with structured TEXT content
     *
     * ADR
     * CATEGORIES
     * CLIENTPIDMAP
     * GENDER
     * N
     * NICKNAME
     * ORG
     *
     * @return array
     */
    public function structTestProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [ // ADR
            100,
            Vcard::factory()
                ->addProperty( Adr::factory(
                    [
                        'Box 123,the blue one',
                        'suite 123,President suite',
                        '123 Main Street,Market Plaza',
                        'Anytown,Great City',
                        'Wild County,Mountain District',
                        '12345,67890',
                        'Country,Europe'
                    ]
                ))
        ];
        $dataArr[] = [ // ADR
            110,
            Vcard::factory()
                ->addProperty( Adr::factory(
                    [
                        '',
                        '',
                        '123 Main Street,Market Plaza',
                        'Anytown,Great City',
                        'Wild County,Mountain District',
                        '12345,67890',
                        'Country,Europe'
                    ]
                ))
        ];
        $dataArr[] = [ // ADR
            120,
            Vcard::factory()
                ->addProperty( Adr::factory(
                    [
                        '',
                        'suite 123,President suite',
                        '',
                        'Anytown,Great City',
                        '',
                        '12345,67890',
                        ''
                    ]
                ))
        ];
        $dataArr[] = [ // ADR
            100,
            Vcard::factory()
                ->addProperty( Adr::factory(
                    [
                        'Box 123,the blue one',
                        '',
                        '123 Main Street,Market Plaza',
                        '',
                        'Wild County,Mountain District',
                        '',
                        'Country,Europe'
                    ]
                ))
        ];

        $dataArr[] = [ // CATEGORIES
            200,
            Vcard::factory()
                ->addProperty( Categories::factory( 'INTERNET' ))
        ];
        $dataArr[] = [ // CATEGORIES
            210,
            Vcard::factory()
                ->addProperty( Categories::factory( 'INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY' ))
        ];

        $dataArr[] = [ // CLIENTPIDMAP
            300,
            Vcard::factory()
                ->addProperty( ClientPidMap::factory( '2;urn:uuid:d89c9c7a-2e1b-4832-82de-7e992d95faa5' ))
        ];

        $dataArr[] = [ // GENDER
            400,
            Vcard::factory()
                ->addProperty( Gender::factory( 'O;it\'s complicated 1' ))
        ];

        $dataArr[] = [ // GENDER
            410,
            Vcard::factory()
                ->addProperty( Gender::factory( ';it\'s complicated 2' ))
        ];

        $dataArr[] = [ // N
            500,
            Vcard::factory()
                ->addProperty( N::factory(
                    [
                        'Stevenson,Lord',
                        'John,Eric',
                        'Philip,Paul',
                        'Colonel,Dr.',
                        'Jr.,M.D.,A.C.P.'
                    ]
                ))
        ];
        $dataArr[] = [ // N
            510,
            Vcard::factory()
                ->addProperty( N::factory(
                    [
                        '',
                        'John,Eric',
                        'Philip,Paul',
                        '',
                        ''
                    ]
                ))
        ];
        $dataArr[] = [ // N
            520,
            Vcard::factory()
                ->addProperty( N::factory(
                    [
                        'Stevenson,Lord',
                        '',
                        'Philip,Paul',
                        '',
                        'Jr.,M.D.,A.C.P.'
                    ]
                ))
        ];

        $dataArr[] = [ // NICKNAME
            600,
            Vcard::factory()
                ->addProperty( Nickname::factory( 'Jim' ))
        ];
        $dataArr[] = [ // NICKNAME
            610,
            Vcard::factory()
                ->addProperty( Nickname::factory( 'Jim,Jimmie' ))
        ];

        $dataArr[] = [ // ORG
            700,
            Vcard::factory()
                ->addProperty( Org::factory( 'ABC,Inc.' ))
        ];
        $dataArr[] = [ // ORG
            710,
            Vcard::factory()
                ->addProperty( Org::factory( 'ABC,Inc.;North American Division;Marketing' ))
        ];

        return $dataArr;
    }
}
