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

use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Property\Note;
use Kigkonsult\PhpVcardMgr\Property\Prodid;
use Kigkonsult\PhpVcardMgr\Property\Role;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Property\Version;
use Kigkonsult\PhpVcardMgr\Property\Xprop;
use PHPUnit\Framework\TestCase;

class VcardTest extends TestCase implements BaseInterface
{
    /**
     * @test
     */
    public function vcardTest1() : void
    {
        $vcard = Vcard::factory();
        $this->assertSame(
            3,
            $vcard->count(),
            'Error #1'
        );
        $prop = $vcard->getProperties( self::VERSION );
        $this->assertInstanceOf(
            Version::class,
            $prop,
            'Error #2, got ' . var_export( $prop, true )
        );
        $propValue = $vcard->getProperties( self::VERSION, true );
        $this->assertSame(
            '4.0',
            $propValue,
            'Error #3, got ' . var_export( $propValue, true )
            . PHP_EOL . var_export( $vcard, true ) // test ###
        );

        $vcard->addProperty( Version::factory());
        $prop = $vcard->getProperties( self::VERSION );
        $this->assertInstanceOf(
            Version::class,
            $prop,
            'Error #4, got ' . var_export( $prop, true )
        );
        $propValue = $vcard->getProperties( self::VERSION, true );
        $this->assertSame(
            '4.0',
            $propValue,
            'Error #5, got ' . var_export( $propValue, true )
        );


        $vcard->addProperty( Note::factory( 'Note 1' ));
        $vcard->addProperty( Note::factory( 'Note 2', [ self::TYPE => 'work' ] ));
        $this->assertSame(
            5,
            $vcard->count(),
            'Error #6'
        );
        $this->assertSame(
            2,
            $vcard->hasProperty( self::NOTE ),
            'Error #7'
        );
        $propValues = $vcard->getProperties( self::NOTE, true );
        $this->assertSame(
            [ 'Note 1', 'Note 2' ],
            $propValues,
            'Error #8, got ' . var_export( $propValues, true )
        );
        $this->assertNotFalse(
            $vcard->hasProperty( self::NOTE, 'work' ),
            'Error #9, got ' . var_export( $vcard->getProperties( self::NOTE ), true )
        );
        $this->assertSame(
            [ 'Note 2' ],
            $vcard->getProperties( self::NOTE, true, 'work' ),
            'Error #10, got ' . var_export( $vcard->getProperties( self::NOTE ), true )
        );

        $vcard = Vcard::factory();
        $vcard->setProperties(
            [
                Note::factory( 'Note 1' ),
                Note::factory( 'Note 2' )
            ]
        );
        $this->assertSame(
            5,
            $vcard->count(),
            'Error #11'
        );
        $this->assertSame(
            2,
            $vcard->hasProperty( self::NOTE ),
            'Error #12'
        );
        $propValues = $vcard->getProperties( self::NOTE, true );
        $this->assertSame(
            [ 'Note 1', 'Note 2' ],
            $propValues,
            'Error #13, got ' . var_export( $propValues, true )
        );


        $vcard->replaceProperty(
            [
                Note::factory( 'Note 4', [ self::PREF => 4 ] ),
                Note::factory( 'Note 3', [ self::PREF => 3 ] ),
                Note::factory( 'Note 6' ),
                Note::factory( 'Note 5' ),
                Xprop::factoryX( 'X-prop1', 'X-prop 1' )
            ]
        );
        $this->assertSame(
            4,
            $vcard->hasProperty( self::NOTE ),
            'Error #14'
        );
        $propValues = $vcard->getProperties();
        $this->assertSame(
            8,
            $vcard->count(),
            'Error #15, ' . var_export( $propValues, true )
        );
        $propValues = $vcard->getProperties( self::NOTE, true );
        $this->assertSame(
            [ 'Note 3', 'Note 4', 'Note 6', 'Note 5' ],
            $propValues,
            'Error #16, got ' . var_export( $propValues, true )
        );


        $vcard->addProperty( Xprop::factoryX( 'X-prop1', 'X-prop 1' )); // i.e. replace
        $this->assertSame(
            8,
            $vcard->count(),
            'Error #17, ' . var_export( $vcard->getProperties( null, true ), true )
        );
        $this->assertCount(
            8,
            $vcard->getProperties(),
            'Error #18, ' . var_export( $vcard->getProperties( self::NOTE, true ), true )
        );

        $vcard->removeProperty( self::NOTE );
        $this->assertSame(
            4,
            $vcard->count(),
            'Error #19, ' . var_export( $vcard->getProperties( self::NOTE, true ), true )
        );

        $vcard->addProperty( Uid::factory( 'Uid1' ));
        $this->assertSame(
            4,
            $vcard->count(),
            'Error #20'
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::UID ),
            'Error #21'
        );
        $propValue = $vcard->getProperties( self::UID, true );
        $this->assertSame(
            'Uid1',
            $propValue,
            'Error #22, got ' . var_export( $propValue, true )
        );
        $vcard->addProperty( Uid::factory( 'Uid2' ));
        $this->assertSame(
            4,
            $vcard->count(),
            'Error #23'
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::UID ),
            'Error #24'
        );
        $propValue = $vcard->getProperties( self::UID, true );
        $this->assertSame(
            'Uid2',
            $propValue,
            'Error #25, got ' . var_export( $propValue, true )
        );

        $newUid      = 'Uid3';
        $vcard->replaceProperty( Uid::factory( $newUid ));
        $prodidValue = $vcard->getProperties( self::PRODID, true );
        $vcard->replaceProperty( Prodid::factory()->setValue( 'test' ));
        $this->assertSame(
            4,
            $vcard->count(),
            'Error #26'
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::UID ),
            'Error #27'
        );
        $propValue = $vcard->getProperties( self::UID, true );
        $this->assertSame(
            $newUid,
            $propValue,
            'Error #28, got ' . var_export( $propValue, true )
        );
        $propValue = $vcard->getProperties( self::PRODID, true );
        $this->assertSame(
            $prodidValue,
            $propValue,
            'Error #29, got ' . var_export( $propValue, true )
        );

        $vcard->addProperty( Note::factory( 'Note X', [ self::TYPE => self::WORK ] ));
        $vcard->addProperty( Xprop::factoryX( 'X-2', 'X-2', [ self::TYPE => 'wor, work2' ]  ));
        $vcard->addProperty( Xprop::factoryX( 'X-3', 'X-3', [ self::TYPE => self::WORK ] ));

        $this->assertSame(
            1,
            $vcard->hasProperty( 'X-2' ),
            'Error #31, got ' . var_export( $vcard->getProperties( self::XPREFIX ), true )
        );
        $this->assertSame(
            3,
            $vcard->hasProperty( self::XPREFIX ),
            'Error #32, got ' . var_export( $propValues, true )
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::XPREFIX, self::WORK ),
            'Error #33, got ' . var_export( $propValues, true )
        );
        $propValues = $vcard->getProperties( self::XPREFIX, true );
        $this->assertSame(
            [ 'X-prop 1', 'X-2', 'X-3' ],
            $propValues,
            'Error #34, got ' . var_export( $propValues, true )
        );
        $propValues = $vcard->getProperties( self::XPREFIX, true, self::WORK );
        $this->assertSame(
            [ 'X-3' ],
            $propValues,
            'Error #35, got ' . var_export( $propValues, true )
        );
        $propValues = $vcard->getProperties( self::XPREFIX, true );
        $this->assertSame(
            [ 'X-prop 1', 'X-2', 'X-3' ],
            $propValues,
            'Error #36, got ' . var_export( $propValues, true )
        );

        $vcard->removeProperty();
        $this->assertSame(
            3,
            $vcard->count(),
            'Error #98'
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::PRODID ),
            'Error #99'
        );
        $this->assertSame(
            1,
            $vcard->hasProperty( self::VERSION ),
            'Error #99'
        );
    }

    /**
     * @test
     */
    public function replacePropertyTest() : void
    {
        $ok = false;
        try {
            Vcard::factory()->replaceProperty( 'error');
        }
        catch ( InvalidArgumentException $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in ' . __METHOD__ );
    }

    /**
     * @test
     */
    public function getTypedPropertyTest() : void
    {
        $vcard = Vcard::factory()
            ->setProperties(
                [
                    Xprop::factoryX( 'X-prop1', self::VALUE . 1 ),
                    Xprop::factoryX( 'X-prop2', self::VALUE . 2, [ self::TYPE => self::WORK ] ),
                    Role::factory( self::VALUE . 3, [ self::TYPE => self::WORK ] ),
                    Xprop::factoryX( 'X-prop4', self::VALUE . 4 ),
                    Role::factory( self::VALUE . 5, [ self::TYPE => self::HOME ] ),
                    Xprop::factoryX( 'X-prop6', self::VALUE . 6, [ self::TYPE => self::WORK ] ),
                    Xprop::factoryX( 'X-prop7', self::VALUE . 7, [ self::TYPE => self::HOME ] ),
                    Role::factory( self::VALUE . 8 ),
                ]
            );
        $workXpropValues = $vcard->getProperties( null, true, self::WORK );
        sort( $workXpropValues );
        $this->assertSame(
            [ self::VALUE . 2, self::VALUE . 3, self::VALUE . 6 ],
            $workXpropValues
        );
        $workXpropValues = $vcard->getProperties( self::XPREFIX, true, self::WORK );
        sort( $workXpropValues );
        $this->assertSame(
            [ self::VALUE . 2, self::VALUE . 6 ],
            $workXpropValues
        );
        $workXpropValues = $vcard->getProperties( self::ROLE, true, self::HOME );
        $this->assertSame(
            [ self::VALUE . 5 ],
            $workXpropValues
        );
        $workXpropValues = $vcard->getProperties( self::XPREFIX, true, self::HOME );
        $this->assertSame(
            [ self::VALUE . 7 ],
            $workXpropValues
        );
    }

    /**
     * @test
     */
    public function getGroupPropertyTest() : void
    {
        $vcard = Vcard::factory();
        $vcard->setProperties(
            [
                Xprop::factoryX( 'X-prop1', self::VALUE . 1 ),
                Xprop::factoryX( 'X-prop2', self::VALUE . 2, null, null, self::WORK ),
                Xprop::factoryX( 'X-prop3', self::VALUE . 3, null, null, self::HOME ),
                Role::factory( self::VALUE . 4 ),
                Role::factory( self::VALUE . 5, null, null, self::WORK ),
                Role::factory( self::VALUE . 6, null, null, self::HOME ),
            ]
        );
        $this->assertSame(
            [ self::WORK, self::HOME ],
            $vcard->getGroups()
        );
        foreach( $vcard->getGroupProperties( self::WORK ) as $property ) {
            $this->assertContains(
                $property->getPropName(),
                [ 'X-prop2', self::ROLE ]
            );
            $this->assertContains(
                $property->getValue(),
                [ self::VALUE . 2, self::VALUE . 5 ]
            );
        } // end foreach
    }
}
