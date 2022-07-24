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
namespace Kigkonsult\PhpVcardMgr\Util;

use Exception;
use Kigkonsult\PhpVcardMgr\BaseInterface;

class StringUtil
{
    /**
     * @var string
     */
    public static $BS2    = '\\';

    /**
     * @var string
     */
    public static $COLON  = ':';

    /**
     * @var string
     */
    public static $COMMA  = ',';

    /**
     * @var string
     */
    public static $CRLF   = "\r\n";

    /**
     * @var string[]
     */
    public static $CRLFs  = [ "\r\n", "\n\r", "\n", "\r" ];

    /**
     * @var string
     */
    public static $DOT    = '.';

    /**
     * @var string
     */
    public static $NEWLINE = "\n";

    /**
     * @var string
     */
    public static $QQ     = '"';

    /**
     * @var string
     */
    public static $SEMIC  = ';';

    /**
     * @var string
     */
    public static $STREOL = '\n';

    /**
     * @var string
     */
    public static $SP0    = '';

    /**
     * @var string
     */
    public static $SP1    = ' ';

    /**
     * Return an unique id as urn uuid GUID v4 string
     *
     * @return string
     * @throws Exception
     * @see https://www.php.net/manual/en/function.com-create-guid.php#117893
     */
    public static function getNewUid() : string
    {
        static $FMT = 'urn:uuid:%s%s-%s-%s-%s-%s%s%s';
        $bytes      = random_bytes( 16 );
        $bytes[6]   = chr( ord( $bytes[6] ) & 0x0f | 0x40 ); // set version to 0100
        $bytes[8]   = chr( ord( $bytes[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
        return vsprintf( $FMT, str_split( bin2hex( $bytes ), 4 ));
    }

    /**
     * Return substring after first found needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function after( string $needle, string $haystack ) : string
    {
        if( false === ( $pos = strpos( $haystack, $needle ))) {
            return self::$SP0;
        }
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring before first found needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function before( string $needle, string $haystack ) : string
    {
        if( false === ( $pos = strpos( $haystack, $needle ))) {
            return self::$SP0;
        }
        return substr( $haystack, 0, $pos );
    }

    /**
     * Return substring between (first found) needles in haystack
     *
     * Case-sensitive search for needles in haystack
     * If no needles found in haystack, '' is returned
     * If only needle1 found, substring after is returned
     * If only needle2 found, substring before is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function between( string $needle1, string $needle2, string $haystack ) : string
    {
        $exists1 = str_contains( $haystack, $needle1 );
        $exists2 = str_contains( $haystack, $needle2 );
        switch( true ) {
            case ! $exists1 && ! $exists2 :
                return self::$SP0;
            case $exists1 && ! $exists2:
                return self::after( $needle1, $haystack );
            case ! $exists1 && $exists2 :
                return self::before( $needle2, $haystack );
            default :
                return self::before( $needle2, self::after( $needle1, $haystack ));
        } // end switch
    }

    /**
     * Return bool true if name is X-prefixed
     *
     * @param string $name
     * @return bool
     */
    public static function isXprefixed( string $name ) : bool
    {
        return ( 0 === stripos( $name, BaseInterface::XPREFIX ) );
    }

    /**
     * Semicolon-split string into array with 'splitNo' elements
     * @param string $value
     * @param int $splitNo
     * @return array
     */
    public static function semicSplit( string $value, int $splitNo ) : array
    {
        return array_pad( explode( self::$SEMIC, $value, $splitNo ), $splitNo, self::$SP0 );
    }

    /**
     * Return strings with removed eol line folding
     *
     * Remove any line-endings that may include spaces or tabs
     * and convert all line endings,
     * takes care of '\r\n', '\r' and '\n' and mixed '\r\n'+'\r', '\r\n'+'\n'
     *
     * @param string $text
     * @param null|bool $convEolSp12Sp0
     * @return string[]
     * @throws Exception
     */
    public static function convEolChar( string $text, ? bool $convEolSp12Sp0 = true ) : array
    {
        static $BASEDELIM  = null;
        static $BASEDELIMs = null;
        static $EMPTYROW   = null;
        static $FMT        = '%1$s%2$75s%1$s';
        static $CRLFexts   = [ "\r\n ", "\r\n\t" ];
        /* fix dummy line separator etc */
        if( empty( $BASEDELIM ) ) {
            $BASEDELIM  = bin2hex( self::getRandChars( 16 ) );
            $BASEDELIMs = $BASEDELIM . $BASEDELIM;
            $EMPTYROW   = sprintf( $FMT, $BASEDELIM, self::$SP0 );
        }
        /* fix eol chars */
        $text = str_replace( self::$CRLFs, $BASEDELIM, $text );
        /* fix empty lines */
        $text = str_replace( $BASEDELIMs, $EMPTYROW, $text );
        /* fix line folding */
        $text = str_replace( $BASEDELIM, self::$CRLF, $text );
        if( $convEolSp12Sp0 ) {
            $text = str_replace( $CRLFexts, self::$SP0, $text );
        }
        /* split in component/property lines */
        return explode( self::$CRLF, $text );
    }

    /**
     * Return a random (and unique) sequence of characters
     *
     * @param int $cnt
     * @return string
     * @throws Exception
     */
    public static function getRandChars( int $cnt ) : string
    {
        $cnt = (int)floor( $cnt / 2 );
        return bin2hex( random_bytes( $cnt ) );
    }

    /**
     * Replace backslash+char by char
     *
     * @param array $chars
     * @param string $string
     * @return string
     */
    public static function unEscapeComma( string $string ) : string
    {
        static $BSCOMMA = '\,';
        return str_replace( $BSCOMMA, StringUtil::$COMMA, $string );
    }
}
