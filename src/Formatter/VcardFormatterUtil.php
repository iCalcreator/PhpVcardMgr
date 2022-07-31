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
namespace Kigkonsult\PhpVcardMgr\Formatter;

use Kigkonsult\PhpVcardMgr\BaseInterface;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class VcardFormatterUtil implements BaseInterface
{
    /**
     * Return formatted output for vCard property parameters
     *
     * @param string[] $parameters
     * @param string[] $cceptedParameterKeys
     * @return string
     */
    public static function createParams( array $parameters, array $cceptedParameterKeys ) : string
    {
        static $FMTKEQV  = '%s=%s';
        $output  = StringUtil::$SP0;
        [ $params, $xparams ] = self::quoteParams( $parameters);
        $pKeys   = array_keys( $params );
        $keys2   = array_diff( $pKeys, $cceptedParameterKeys ); // set iana(?) params last, any order
        $keys1   = array_diff( $pKeys, $keys2 );
        $stdkeys = [];
        foreach( $cceptedParameterKeys as $orderkey ) {
            if( in_array( $orderkey, $keys1, true )) {
                $stdkeys[] = $orderkey;
            }
        } // end foreach
        foreach(( $stdkeys + $keys2 ) as $paramKey ) {
            $output .= StringUtil::$SEMIC;
            $output .= sprintf( $FMTKEQV, $paramKey, $params[$paramKey] );
        } // end foreach
        foreach( $xparams as $paramKey => $paramValue ) {
            $output .= StringUtil::$SEMIC;
            $output .= sprintf( $FMTKEQV, $paramKey, $paramValue );
        } // end foreach
        return $output;
    }

    /**
     * Return parameter with opt. quoted parameter value
     *
     * "-quotes a value if value contains ':', ';' or ','
     *
     * @param array $inputParams
     * @return array[]
     */
    private static function quoteParams( array $inputParams ) : array
    {
        static $FMTQ       = '"%s"';
        $params = $xparams = [];
        foreach( array_change_key_case( $inputParams, CASE_UPPER ) as $paramKey => $paramValue ) {
            if( is_array( $paramValue )) { // TYPE, PID, SORT-AS etc
                $paramValue = implode( StringUtil::$COMMA, $paramValue );
            }
            if( in_array( $paramKey, [ self::PID, self::PREF ], true )) {
                if( $paramValue == (int) $paramValue ) { // note ==
                    $paramValue = (int) $paramValue;
                }
            }
            elseif( self::hasColonOrSemicOrComma( $paramValue )) {
                $paramValue = sprintf( $FMTQ, $paramValue );
            }
            if( StringUtil::isXprefixed( $paramKey )) {
                $xparams[$paramKey] = $paramValue;
            }
            else {
                $params[$paramKey] = $paramValue;
            }
        } // end foreach
        ksort( $xparams, SORT_STRING );
        return [ $params, $xparams ];
    }

    /**
     * Prepare property parameters, remove default VALUE type
     *
     * @param PropertyInterface $property
     * @return array
     */
    public static function prepParameters( PropertyInterface $property ) : array
    {
        $parameters = $property->getParameters();
        if( isset( $parameters[BaseInterface::VALUE] ) &&
            ( $parameters[BaseInterface::VALUE] === $property::getAcceptedValueTypes( true ))) {
            unset( $parameters[BaseInterface::VALUE] );
        }
        return $parameters;
    }

    /**
     * Return bool true if string contains any of :;,
     *
     * @param mixed $string
     * @return bool
     */
    private static function hasColonOrSemicOrComma( $string ): bool
    {
        return ( is_string( $string ) &&
            (( false !== strpos( $string, StringUtil::$COLON )) ||
            ( false !== strpos( $string, StringUtil::$SEMIC )) ||
            ( false !== strpos( $string, StringUtil::$COMMA ))));
    }

    /**
     *  ESCAPED-CHAR = "\\" / "\;" / "\," / "\n" / "\N")
     * ; \\ encodes \, \n or \N encodes newline
     * ; \; encodes ;, \, encodes ,
     *
     * @param string $string
     * @return string
     */
    public static function strrep( string $string ) : string
    {
        static $SPECCHAR = [ 'n', 'N', 'r', ',', ';' ];
        static $SQ       = "'";
        static $QBSLCR   = "\r";
        static $QBSLCN   = "\n";
        static $BSUCN    = '\N';
        $strLen = strlen( $string );
        $pos    = 0;
        // replace single (solo-)backslash by double ones
        while( $pos < $strLen ) {
            if( false === ( $pos = strpos( $string, StringUtil::$BS2, $pos ))) {
                break;
            }
            if( ! in_array( $string[$pos], $SPECCHAR )) {
                $string = substr( $string, 0, $pos ) . StringUtil::$BS2 . substr( $string, ( $pos + 1 ));
                ++$pos;
            }
            ++$pos;
        } // end while
        // replace double quote by single ones
        if( str_contains( $string, StringUtil::$QQ )) {
            $string = str_replace( StringUtil::$QQ, $SQ, $string );
        }
        // replace comma by backslash+comma but skip any previously set of backslash+comma
        // replace semicolon by backslash+semicolon but skip any previously set of backslash+semicolon
        $string = self::escapeChar( [ StringUtil::$COMMA, StringUtil::$SEMIC ], $string );
        // replace "\r\n" by '\n'
        if( str_contains( $string, StringUtil::$CRLF )) {
            $string = str_replace( StringUtil::$CRLF, StringUtil::$STREOL, $string );
        }
        // or replace "\r" by '\n'
        elseif( str_contains( $string, $QBSLCR )) {
            $string = str_replace( $QBSLCR, StringUtil::$STREOL, $string );
        }
        // or replace "\n" by '\n'
        elseif( str_contains( $string, $QBSLCN )) {
            $string = str_replace( $QBSLCN, StringUtil::$STREOL, $string );
        }
        // replace '\N' by  '\n'
        if( str_contains( $string, $BSUCN )) {
            $string = str_replace( $BSUCN, StringUtil::$STREOL, $string );
        }
        return $string;
    }

    /**
     * Replace char by backslash+char but skip any previously set of backslash+char
     *
     * @param array $chars
     * @param string $string
     * @return string
     */
    public static function escapeChar( array $chars, string $string ) : string
    {
        foreach( $chars as $char ) {
            $offset = 0;
            while( false !== ( $pos = strpos( $string, $char, $offset ))) {
                if(( 0 < $pos ) && ( StringUtil::$BS2 !== substr( $string, ( $pos - 1 )))) {
                    $string = substr( $string, 0, $pos ) . StringUtil::$BS2 . substr( $string, $pos );
                }
                $offset = $pos + 2;
            } // end while
            $string = str_replace(
                StringUtil::$BS2 . StringUtil::$BS2 . $char,
                StringUtil::$BS2 . $char,
                $string
            );
        } // end foreach
        return $string;
    }

    /**
     * Return wrapped string with (byte oriented) line breaks at pos 75
     *
     * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
     * break. Long content lines SHOULD be split into a multiple line
     * representations using a line "folding" technique. That is, a long
     * line can be split between any two characters by inserting a CRLF
     * immediately followed by a single linear white space character (i.e.,
     * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
     * of CRLF followed immediately by a single linear white space character
     * is ignored (i.e., removed) when processing the content type.
     *
     * Edited 2007-08-26 by Anders Litzell, anders@litzell.se to fix bug where
     * the reserved expression "\n" in the arg $string could be broken up by the
     * folding of lines, causing ambiguity in the return string.
     *
     * @param string $string
     * @return string
     * @link   http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
     */
    public static function size75( string $string ) : string
    {
        static $LCN     = 'n';
        static $UCN     = 'N';
        static $SPBSLCN = ' \n';
        $tmp    = $string;
        $inLen  = strlen( $tmp );
        $string = StringUtil::$SP0;
        $outLen = $x = 0;
        while( true ) {
            $x1 = $x + 1;
            if( $inLen <= $x ) {
                $string .= StringUtil::$CRLF; // loop breakes here
                break;
            }
            if(( 74 <= $outLen ) &&
                ( StringUtil::$BS2 === $tmp[$x]) && // '\\'
                (( $LCN === $tmp[$x1]) ||
                    ( $UCN === $tmp[$x1]))) {
                $string .= StringUtil::$CRLF . $SPBSLCN; // don't break lines inside '\n'
                $x      += 2;
                if( $inLen < $x ) {
                    $string .= StringUtil::$CRLF;
                    break; // or here...
                }
                $outLen = 3;
            }
            elseif( 75 <= $outLen ) {
                $string .= StringUtil::$CRLF;
                if( $inLen === $x ) {
                    break; // or here..
                }
                $string .= StringUtil::$SP1;
                $outLen  = 1;
            }
            $str1    = $tmp[$x];
            $byte    = ord( $str1 );
            $string .= $str1;
            switch( true ) {
                case(( $byte >= 0x20 ) && ( $byte <= 0x7F )) :
                    ++$outLen;                     // characters U-00000000 - U-0000007F (same as ASCII)
                    break;                         // add a one byte character
                case(( $byte & 0xE0 ) === 0xC0 ) : // characters U-00000080 - U-000007FF, mask 110XXXXX
                    if( $inLen > ( $x + 1 )) {
                        ++$outLen;
                        ++$x;                      // add second byte of a two bytes character
                        $string .= $tmp[$x];
                    }
                    break;
                case(( $byte & 0xF0 ) === 0xE0 ) : // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    if( $inLen > ( $x + 2 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x1, 2 );
                        ++$x;                      // add byte 2-3 of a three bytes character
                    }
                    break;
                case(( $byte & 0xF8 ) === 0xF0 ) : // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    if( $inLen > ( $x + 3 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x1, 3 );
                        $x      += 2;              // add byte 2-4 of a four bytes character
                    }
                    break;
                case(( $byte & 0xFC ) === 0xF8 ) : // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    if( $inLen > ( $x + 4 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x, 4 );
                        $x      += 3;              // add byte 2-5 of a five bytes character
                    }
                    break;
                case(( $byte & 0xFE ) === 0xFC ) : // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    if( $inLen > ( $x + 5 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x, 5 );
                        $x      += 4;              // add byte 2-6 of a six bytes character
                    }
                    break;
                default:                           // add any other byte without counting up $cCnt
                    break;
            } // end switch( true )
            ++$x;    // next 'byte' to test
        } // end while( true )
        return $string;
    }
}
