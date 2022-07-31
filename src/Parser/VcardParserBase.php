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

use Exception;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use RuntimeException;

abstract class VcardParserBase extends ParserBase implements VcardParserInterface
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function parse( string $source ) : array
    {
        $rows      = self::conformParseInput( $source );
        $vCardRows = $vCards = [];
        $hasBegin  = false;
        foreach( $rows as $row ) {
            switch( true ) {
                case ( 0 === stripos( $row, self::END_VCARD )) :
                    $vCards[] = $this->vCardParse( $vCardRows );
                    $hasBegin = false;
                    break;
                case ( 0 === stripos( $row, self::BEGIN_VCARD )) :
                    $vCardRows = [];
                    $hasBegin = true;
                    break;
                case ( ! $hasBegin && empty( $row )) :
                    break;
                default :
                    $vCardRows[] = $row;
            } // end switch
        } // end foreach
        return $vCards;
    }

    /**
     * Return concatenated calendar rows, one row for each property
     *
     * @param string[] $rows
     * @return string[]
     */
    protected static function concatRows( array $rows ) : array
    {
        static $CHARs = [ ' ', "\t" ];
        $output   = [];
        $cnt      = count( $rows );
        for( $i = 0; $i < $cnt; $i++ ) {
            $line = rtrim( $rows[$i], StringUtil::$CRLF );
            $i1   = $i + 1;
            while(( $i < $cnt ) && isset( $rows[$i1] ) &&
                ! empty( $rows[$i1] ) &&
                in_array( $rows[$i1][0], $CHARs )) {
                ++$i;
                $line .= rtrim( substr( $rows[$i], 1 ), StringUtil::$CRLF );
                $i1 = $i + 1;
            } // end while
            $output[] = $line;
        } // end for
        return $output;
    }

    /**
     * Return rows to parse from string
     *
     * @param string $vcardString
     * @return string[]
     * @throws Exception
     */
    protected static function conformParseInput( string $vcardString ) : array
    {
        static $ERR10 = 'Only %d rows in vCard content :%s';
        /* fix line folding, convert to array */
        $rows = StringUtil::convEolChar( $vcardString );
        /* skip leading (empty/invalid) lines (and remove leading BOM chars etc) */
        $rows  = self::trimLeadingRows( $rows );
        /* skip trailing empty lines and ensure an end row */
        $rows  = self::trimTrailingRows( $rows );
        $cnt   = count( $rows );
        if( 2 === $cnt ) { /* err 10 */
            throw new RuntimeException(
                sprintf( $ERR10, $cnt, PHP_EOL . implode( PHP_EOL, $rows ))
            );
        }
        return $rows;
    }

    /**
     * Return array (opt) group, property name and (params+)value from (string) row
     *
     * @param  string $row
     * @return string[]   propName and the trailing part of the row
     */
    protected static function getPropName( string $row ) : array
    {
        $semicPos   = strpos( $row, StringUtil::$SEMIC );
        $colonPos   = strpos( $row, StringUtil::$COLON );
        switch( true ) {
            case (( false === $semicPos ) && ( false === $colonPos )) : // no params and no value
                return [ $row, StringUtil::$SP0, StringUtil::$SP0 ];
            case (( false !== $semicPos ) && ( false === $colonPos )) : // param exist and NO value ??
                $propName = StringUtil::before( StringUtil::$SEMIC, $row );
                break;
            case (( false === $semicPos ) && ( false !== $colonPos )) : // no params
                $propName = StringUtil::before( StringUtil::$COLON, $row );
                break;
            case ( $semicPos < $colonPos ) :                            // param(s) and value
                $propName = StringUtil::before( StringUtil::$SEMIC, $row );
                break;
            default : // ie $semicPos > $colonPos                       // no params
                $propName = StringUtil::before( StringUtil::$COLON, $row );
                break;
        } // end switch
        /* separate (opt) group and name */
        $group = null;
        if( false !== strpos( $propName, StringUtil::$DOT )) {
            [ $group, $propName ] = explode( StringUtil::$DOT, $propName, 2 );
        }
        return [ $group, strtoupper( $propName ), StringUtil::after( $propName, $row ) ];
    }

    /**
     * Return array property value and parameters
     *
     * Parameters are prefixed by ';', value by ':', BUT they may exist in both attr (quoted?) and values
     *
     * @param string $line   property content
     * @return array        [ value, [ *( propAttrKey => propAttrValue) ] ]
     */
    protected static function splitContent( string $line ) : array
    {
        static $CSS = '://';
        static $EQ  = '=';
        $clnPos     = strpos( $line, StringUtil::$COLON );
        if(( false === $clnPos )) {
            return [ $line, [] ]; // no params
        }
        if( 0 === $clnPos ) { // no params,  most frequent
            return [ substr( $line, 1 ) , [] ];
        }
        if( self::checkSingleParam( $line )) { // one param
            $param = StringUtil::between( StringUtil::$SEMIC, StringUtil::$COLON, $line );
            return [
                StringUtil::after( StringUtil::$COLON, $line ),
                [
                    StringUtil::before( $EQ, $param ) =>
                        trim( StringUtil::after( $EQ, $param ), StringUtil::$QQ )
                ]
            ];
        } // end if
        /* more than one param here (or a tricky one...) */
        $attr          = [];
        $attrix        = -1;
        $WithinQuotes  = false;
        $len           = strlen( $line );
        $cix           = 0;
        while( $cix < $len ) {
            $str1 = $line[$cix];
            $cix1 = $cix + 1;
            if( ! $WithinQuotes &&
                ( StringUtil::$COLON === $str1 ) &&
                ( $CSS !== substr( $line, $cix, 3 ))) {
                $line = substr( $line, $cix1 );
                break;
            }
            if( StringUtil::$QQ === $str1 ) { // '"'
                $WithinQuotes = ! $WithinQuotes;
            }
            if( StringUtil::$SEMIC === $str1 ) { // ';'
                ++$attrix;
                $attr[$attrix] = StringUtil::$SP0; // initiate
            }
            else {
                $attr[$attrix] .= $str1;
            }
            ++$cix;
        } // end while...
        /* make attributes in array format */
        $propAttr = [];
        foreach( $attr as $attribute ) {
            if( ! str_contains($attribute, $EQ )) {
                continue;// skip empty? attributes
            }
            $attrSplit = explode( $EQ, $attribute, 2 );
            $propAttr[$attrSplit[0]] = $attrSplit[1];
        }
        return [ $line, $propAttr ];
    }

    /**
     * Return true if single param only (and no colon/semicolon in param values)
     *
     * 2nd most frequent
     *
     * @param string $line
     * @return bool
     */
    protected static function checkSingleParam( string $line ) : bool
    {
        if( StringUtil::$SEMIC !== $line[0] )  {
            return false;
        }
        return (( 1 === substr_count( $line, StringUtil::$SEMIC )) &&
            ( 1 === substr_count( $line, StringUtil::$COLON )));
    }

    /**
     * Replace '\\', '\,', '\;' by '\', ',', ';'
     *
     * @param string $string
     * @return string
     */
    protected static function strunrep( string $string ) : string
    {
        static $BS4     = '\\\\';
        static $BSSEMIC = '\;';
        $string = str_replace( $BS4, StringUtil::$BS2, $string );
        $string = StringUtil::unEscapeComma( $string );
        return str_replace( $BSSEMIC, StringUtil::$SEMIC, $string );
    }

    /**
     * Return array to parse with leading (empty/invalid) lines removed (incl leading BOM chars etc)
     *
     * Ensure BEGIN:VCARD on the first row
     *
     * @param string[] $rows
     * @return string[]
     */
    protected static function trimLeadingRows( array $rows ) : array
    {
        $beginFound = false;
        foreach( $rows as $lix => $row ) {
            if( false !== stripos( $row, self::BEGIN_VCARD )) {
                $rows[$lix] = self::BEGIN_VCARD;
                $beginFound = true;
                continue;
            }
            if( ! empty( trim( $row ))) {
                break;
            }
            unset( $rows[$lix] );
        } // end foreach
        if( ! $beginFound ) {
            array_unshift( $rows, self::BEGIN_VCARD );
        }
        return $rows;
    }

    /**
     * Return array to parse with trailing empty lines removed and ensured an end row
     *
     * Ensure END:VCARD on the last row
     *
     * @param string[] $rows
     * @return string[]
     */
    protected static function trimTrailingRows( array $rows ) : array
    {
        static $NLCHARS = '\n';
        end( $rows );
        $lix = key( $rows );
        while( 0 <= $lix ) {
            $tst = trim( $rows[$lix] );
            if(( $NLCHARS === $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
                $lix--;
                continue;
            }
            if( false === stripos( $rows[$lix], self::END_VCARD )) {
                $rows[] = self::END_VCARD;
            }
            else {
                $rows[$lix] = self::END_VCARD;
            }
            break;
        } // end while
        return $rows;
    }

}
