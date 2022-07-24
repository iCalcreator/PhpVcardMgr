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

interface BaseInterface
{
    /**
     * vCard Properties : constants
     */
    public const BEGIN_VCARD    = 'BEGIN:VCARD';
    public const END_VCARD      = 'END:VCARD';
    public const SOURCE         = 'SOURCE';
    public const KIND           = 'KIND';
    public const XML            = 'XML';

    /**
     * Identification Properties : constants
     */
    public const FN             = 'FN';
    public const N              = 'N';
    public const NICKNAME       = 'NICKNAME';
    public const PHOTO          = 'PHOTO';
    public const BDAY           = 'BDAY';
    public const ANNIVERSARY    = 'ANNIVERSARY';
    public const GENDER         = 'GENDER';

    /**
     * Delivery Addressing Properties : constants
     */
    public const ADR            = 'ADR';

    /**
     * Communications Properties : constants
     */
    public const TEL            = 'TEL';
    public const EMAIL          = 'EMAIL';
    public const IMPP           = 'IMPP';
    public const LANG           = 'LANG';

    /**
     * Geographical Properties : constants
     */
    public const TZ             = 'TZ';
    public const GEO            = 'GEO';

    /**
     * Organizational Properties : constants
     */
    public const TITLE          = 'TITLE';
    public const ROLE           = 'ROLE';
    public const LOGO           = 'LOGO';
    public const ORG            = 'ORG';
    public const MEMBER         = 'MEMBER';
    public const RELATED        = 'RELATED';

    /**
     * Explanatory Properties : constants
     */
    public const CATEGORIES     = 'CATEGORIES';
    public const NOTE           = 'NOTE';
    public const PRODID         = 'PRODID';
    public const REV            = 'REV';
    public const SOUND          = 'SOUND';
    public const UID            = 'UID';
    public const CLIENTPIDMAP   = 'CLIENTPIDMAP';
    public const URL            = 'URL';
    public const VERSION        = 'VERSION';

    /**
     * Security Properties : constants
     */
    public const KEY            = 'KEY';

    /**
     * Calendar Properties : constants
     */
    public const FBURL          = 'FBURL';
    public const CALADRURI      = 'CALADRURI';
    public const CALURI         = 'CALURI';

    /**
     * Extended Properties and Parameters prefix: constant
     */
    public const XPREFIX        = 'X-';

    /**
     * Vcard parameter keys as constants
     */
    public const ALTID          = 'ALTID';
    public const CALSCALE       = 'CALSCALE';
//  public const GEO            = 'GEO';
    public const GROUP          = 'GROUP';
    public const LABEL          = 'LABEL';
    public const LANGUAGE       = 'LANGUAGE';
    public const PREF           = 'PREF';
    public const PID            = 'PID';
    public const MEDIATYPE      = 'MEDIATYPE';
    public const SORT_AS        = 'SORT-AS';
    public const TYPE           = 'TYPE';
//  public const TZ             = 'TZ';
    public const VALUE          = 'VALUE';

    /**
     * Vcard property valueTypes as constants, also parameter VALUE key values
     */
    public const TEXT           = 'text';
    public const URI            = 'uri';
    public const DATE           = 'date';
    public const TIME           = 'time';
    public const DATETIME       = 'date-time';
    public const DATEANDORTIME  = 'date-and-or-time';
    public const TIMESTAMP      = 'timestamp';
    public const BOOLEAN        = 'boolean';
    public const INTEGER        = 'integer';
    public const FLOAT          = 'float';
    public const UTCOFFSET      = 'utc-offset';
    public const LANGUAGETAG    = 'language-tag';

    /**
     * General parameter TYPE values
     */
    public const WORK           = 'work';
    public const HOME           = 'home';

    /**
     * Tel parameter TYPE values
     */
//  public const TEXT           = 'text';
    public const VOICE          = 'voice';
    public const FAX            = 'fax';
    public const CELL           = 'cell';
    public const VIDEO          = 'video';
    public const PAGER          = 'pager';
    public const TEXTPHONE      = 'textphone';

    /**
     * Related parameter TYPE values
     */
    public const CONTACT        = 'contact';
    public const ACQUAINTANCE   = 'acquaintance';
    public const FRIEND         = 'friend';
    public const MET            = 'met';
    public const CO_WORKER      = 'co-worker';
    public const COLLEAGUE      = 'colleague';
    public const CO_RESIDENT    = 'co-resident';
    public const NEIGHBOR       = 'neighbor';
    public const CHILD          = 'child';
    public const PARENT         = 'parent';
    public const SIBLING        = 'sibling';
    public const SPOUSE         = 'spouse';
    public const KIN            = 'kin';
    public const MUSE           = 'muse';
    public const CRUSH          = 'crush';
//  public const DATE           = 'date';
    public const SWEETHEART     = 'sweetheart';
    public const ME             = 'me';
    public const AGENT          = 'agent';
    public const EMERGENCY      = 'emergency';

    /**
     * Misc use
     */
    public const DATETIMEfmt    = 'Ymd\THis';
    public const OFFSETfmt      = 'O';
    public const GMTfmt         = 'GMT';
    public const UTCfmt         = 'UTC';
    public const Zfmt           = 'Z';
}
