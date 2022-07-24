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

// use Faker;
use Kigkonsult\PhpVcardMgr\BaseInterface;
use Kigkonsult\PhpVcardMgr\Vcard as Dto;

class Vcard implements BaseInterface
{
    /**
     * Use faker to populate new Vcard
     *
     * @param null|bool $datetimeDateOnly
     * @return Dto
     */
    public static function load( ? bool $datetimeDateOnly = false ) : Dto
    {
        // $faker = Faker\Factory::create();
        $dto   = new Dto();

        $dto->addProperty( Adr::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Adr::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Anniversary::load( $datetimeDateOnly ));

        $dto->addProperty( Bday::load( $datetimeDateOnly ));

        $dto->addProperty( CalAdrUri::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( CalAdrUri::load()->unsetParameter( self::PREF ));

        $dto->addProperty( CalUri::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( CalUri::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Categories::load());
        $dto->addProperty( Categories::load());

        $dto->addProperty( ClientPidMap::load( 0 ));
        $dto->addProperty( ClientPidMap::load( 1 ));

        $dto->addProperty( Email::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Email::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Fburl::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Fburl::load()->unsetParameter( self::PREF ));

        $dto->addProperty( FullName::load());
        $dto->addProperty( FullName::load());

        $dto->addProperty( Gender::load());

        $dto->addProperty( Geo::load());
        $dto->addProperty( Geo::load());

        $dto->addProperty( Impp::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Impp::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Key::load());
        $dto->addProperty( Key::load());

        $dto->addProperty( Kind::load());
        $dto->addProperty( Kind::load());

        $dto->addProperty( Lang::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Lang::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Logo::load());
        $dto->addProperty( Logo::load());

        $dto->addProperty( Member::load());
        $dto->addProperty( Member::load());

        $dto->addProperty( Nickname::load());
        $dto->addProperty( Nickname::load());

        $dto->addProperty( Note::load());
        $dto->addProperty( Note::load());

        $dto->addProperty( N::load());

        $dto->addProperty( Org::load());
        $dto->addProperty( Org::load());

        $dto->addProperty( Photo::load());
        $dto->addProperty( Photo::load());

        $dto->addProperty( Related::load());
        $dto->addProperty( Related::load());

        $dto->addProperty( Rev::load());

        $dto->addProperty( Role::load());
        $dto->addProperty( Role::load());

        $dto->addProperty( Sound::load());
        $dto->addProperty( Sound::load());

        $dto->addProperty( Source::load());
        $dto->addProperty( Source::load());

        $dto->addProperty( Tel::load()->addParameter( self::PREF, 1 ));
        $dto->addProperty( Tel::load()->unsetParameter( self::PREF ));

        $dto->addProperty( Title::load());
        $dto->addProperty( Title::load());

        $dto->addProperty( Tz::load());
        $dto->addProperty( Tz::load());

        $dto->addProperty( Uid::load());

        $dto->addProperty( Url::load());
        $dto->addProperty( Url::load());

        $dto->addProperty( Xml::load());
        $dto->addProperty( Xml::load());

        $dto->addProperty( Xprop::load());
        $dto->addProperty( Xprop::load());

        return $dto;
    }
}
