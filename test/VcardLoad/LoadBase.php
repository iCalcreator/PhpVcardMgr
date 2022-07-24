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
namespace Kigkonsult\PhpVcardMgr\VcardLoad;

use Faker;
use Kigkonsult\FakerLocRelTypes\Provider\MediaTypes;
use Kigkonsult\FakerLocRelTypes\Provider\SchemaURI;
use Kigkonsult\PhpVcardMgr\BaseInterface;

abstract class LoadBase implements BaseInterface
{
    /**
     * @var string
     */
    protected static $MTany          = 'any';
    /**
     * @var string
     */
    protected static $MTapplication  = 'application';
    /**
     * @var string
     */
    protected static $MTaudio        = 'audio';
    /**
     * @var string
     */
    protected static $MTimage        = 'image';
    /**
     * @var string
     */
    protected static $MTmessage      = 'message';
    /**
     * @var string
     */
    protected static $MTtext         = 'text';
    /**
     * @var string
     */
    protected static $MTtextCalendar = 'text/calendar';

    /**
     * @param array  $parameterKeys
     * @param string $valueType
     * @param bool   $anyParameter
     * @param null|string $mtGroup
     * @return array
     */
    public static function loadParameters(
        array $parameterKeys,
        string $valueType,
        bool $anyParameter,
        ? string $mtGroup = null
    ) : array
    {
        $faker = Faker\Factory::create();
        $parameters = [];
        foreach( $parameterKeys as $parameterKey ) {
            switch( $parameterKey ) {
                case self::VALUE :
                    $parameters[self::VALUE] = $valueType;
                    break;
                case self::LANGUAGE :
                    $parameters[self::LANGUAGE] = $faker->locale();
                    break;
                case self::PREF :
                    $parameters[self::PREF] = $faker->numberBetween( 1, 9 );
                    break;
                case self::ALTID :
                    $parameters[self::ALTID] = $faker->uuid();
                    break;
                case self::PID :
                    $parameters[self::PID] = $faker->randomDigitNotNull();
                    break;
                case self::TYPE :
                    $parameters[self::TYPE] = $faker->word();
                    break;
                case self::MEDIATYPE :
                    $faker->addProvider( new MediaTypes( $faker ));
                    switch( $mtGroup ) {
                        case ( self::URI !== $valueType ) :
                            break;
                        case self::$MTtextCalendar :
                            $parameters[self::MEDIATYPE] = self::$MTtextCalendar;
                            break;
                        case self::$MTapplication :
                            $parameters[self::MEDIATYPE] = $faker->applicationMediaType();
                            break;
                        case self::$MTaudio :
                            $parameters[self::MEDIATYPE] = $faker->audioMediaType();
                            break;
                        case self::$MTimage :
                            $parameters[self::MEDIATYPE] = $faker->imageMediaType();
                            break;
                        case self::$MTmessage :
                            $parameters[self::MEDIATYPE] = $faker->messageMediaType();
                            break;
                        case self::$MTtext :
                            $parameters[self::MEDIATYPE] = $faker->textMediaType();
                            break;
                        default :
                            $parameters[self::MEDIATYPE] = $faker->anyMediaType();
                            break;
                    }
                    break;
                case self::CALSCALE :
                    $parameters[self::CALSCALE] = 'gregorian';
                    break;
                case self::SORT_AS :
                    $parameters[self::SORT_AS] = $faker->words( 2, true );
                    break;
                case self::GEO :
                    $faker->addProvider( new SchemaURI( $faker ));
                    $parameters[self::GEO] = $faker->geoUri();
                    break;
                case self::TZ :
                    $parameters[self::TZ] =
                        $faker->randomElement( [ '+', '-' ] ) . $faker->time( 'Hi', 43200 );
                    break;
            } // end switch
        } // end foreach
        if( $anyParameter ) {
            $parameters[self::XPREFIX . $faker->word()] = $faker->word();
        }
        return $parameters;
    }
}
