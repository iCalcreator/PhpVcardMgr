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
namespace Kigkonsult\PhpVcardMgr\Parser;

use Kigkonsult\PhpVcardMgr\BaseInterface;
use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Anniversary;
use Kigkonsult\PhpVcardMgr\Property\Bday;
use Kigkonsult\PhpVcardMgr\Property\CalAdrUri;
use Kigkonsult\PhpVcardMgr\Property\CalUri;
use Kigkonsult\PhpVcardMgr\Property\Categories;
use Kigkonsult\PhpVcardMgr\Property\ClientPidMap;
use Kigkonsult\PhpVcardMgr\Property\Email;
use Kigkonsult\PhpVcardMgr\Property\Fburl;
use Kigkonsult\PhpVcardMgr\Property\FullName;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\Geo;
use Kigkonsult\PhpVcardMgr\Property\Impp;
use Kigkonsult\PhpVcardMgr\Property\Key;
use Kigkonsult\PhpVcardMgr\Property\Kind;
use Kigkonsult\PhpVcardMgr\Property\Lang;
use Kigkonsult\PhpVcardMgr\Property\Logo;
use Kigkonsult\PhpVcardMgr\Property\Member;
use Kigkonsult\PhpVcardMgr\Property\N;
use Kigkonsult\PhpVcardMgr\Property\Nickname;
use Kigkonsult\PhpVcardMgr\Property\Note;
use Kigkonsult\PhpVcardMgr\Property\Org;
use Kigkonsult\PhpVcardMgr\Property\Photo;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Property\Related;
use Kigkonsult\PhpVcardMgr\Property\Rev;
use Kigkonsult\PhpVcardMgr\Property\Role;
use Kigkonsult\PhpVcardMgr\Property\Sound;
use Kigkonsult\PhpVcardMgr\Property\Source;
use Kigkonsult\PhpVcardMgr\Property\Tel;
use Kigkonsult\PhpVcardMgr\Property\Title;
use Kigkonsult\PhpVcardMgr\Property\Tz;
use Kigkonsult\PhpVcardMgr\Property\Uid;
use Kigkonsult\PhpVcardMgr\Property\Url;
use Kigkonsult\PhpVcardMgr\Property\Xml;
use Kigkonsult\PhpVcardMgr\Property\Xprop;

abstract class ParserBase implements BaseInterface
{
    /**
     * Return new property class instance for propName
     *
     * @param string $propName
     * @return PropertyInterface
     */
    protected static function newProperty( string $propName ) : PropertyInterface
    {
        switch( $propName ) {
            case self::ADR :          return new Adr();
            case self::ANNIVERSARY :  return new Anniversary();
            case self::BDAY :         return new Bday();
            case self::CALADRURI :    return new CalAdrUri();
            case self::CALURI :       return new CalUri();
            case self::CATEGORIES :   return new Categories();
            case self::CLIENTPIDMAP : return new ClientPidMap();
            case self::EMAIL :        return new Email();
            case self::FBURL :        return new Fburl();
            case self::FN :           return new FullName();
            case self::GENDER :       return new Gender();
            case self::GEO :          return new Geo();
            case self::IMPP :         return new Impp();
            case self::KEY :          return new Key();
            case self::KIND :         return new Kind();
            case self::LANG  :        return new Lang();
            case self::LOGO :         return new Logo();
            case self::MEMBER :       return new Member();
            case self::N :            return new N();
            case self::NICKNAME :     return new Nickname();
            case self::NOTE :         return new Note();
            case self::ORG :          return new Org();
            case self::PHOTO :        return new Photo();
            case self::RELATED :      return new Related();
            case self::REV :          return new Rev();
            case self::ROLE :         return new Role();
            case self::SOUND :        return new Sound();
            case self::SOURCE :       return new Source();
            case self::TEL :          return new Tel();
            case self::TITLE :        return new Title();
            case self::TZ :           return new Tz();
            case self::UID :          return new Uid();
            case self::URL :          return new Url();
            case self::XML :          return new Xml();
            default :                 return new Xprop( self::XPREFIX ); // to overwrite later
        } // end switch
    }
}
