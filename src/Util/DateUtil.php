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

use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\BaseInterface;

class DateUtil implements BaseInterface
{
    /**
     * @var string
     */
    public static $T   = 'T';

    /**
     * @var string
     */
    public static $DS1 = '-';

    /**
     * @var string
     */
    private static $DS2 = '--';

    /**
     * @var string
     */
    private static $Z   = 'Z';

    /**
     * @var string
     */
    private static $PLUS  = '+';

    /**
     * @var string
     */
    private static $MINUS = '-';

    /**
     * Return vCard date and/or time converted to Jcard
     *
     * @param string $value
     * @param string $valueType
     * @return string
     */
    public static function convertVcard2JcardDates( string $value, string $valueType ) : string
    {
        switch( $valueType ) {
            case self::DATEANDORTIME :
            case self::DATETIME :
            case self::TIMESTAMP :
                // the same date and time format restrictions noted in ... apply
                if( false === strpos( $value, self::$T )) {
                    return self::convertVcard2JcardDate( $value );
                }
                $date = StringUtil::before( self::$T, $value );
                $time = StringUtil::after( self::$T, $value );
                return self::convertVcard2JcardDate( $date ) . self::$T . self::convertVcard2JcardTime( $time );
            case self::DATE :
                return self::convertVcard2JcardDate( $value );
            case self::TIME :
                return self::convertVcard2JcardTime( $value );
            case self::TEXT : // fall through
            default :
                return $value;
        } // end switch
    }

    /**
     * Return vCard date converted to Jcard
     *
     * Jcard
     *    date-complete = year "-" month "-" day ;YYYY-MM-DD
     *    date-noreduc = date-complete
     *    / "--" month "-" day ; --MM-DD
     *    / "---" day          ; ---DDD
     *    date = date-noreduc
     *    / year; YYYY
     *    / year "-" month ; YYYY-MM
     *    / "--" month     ; --MM
     * Vcard
     *    date = year    [month  day]   case 1
     *    / year "-" month              case 2
     *    / "--"     month [day]        case 3
     *    / "--"      "-"   day         case 4
     *
     * @param string $value
     * @return string
     */
    public static function convertVcard2JcardDate( string $value ) : string
    {
        $value  = trim( $value);
        $strlen = strlen( $value );
        switch( true ) {
            case (( 5 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::equalDS1( $value, 2 ) &&
                self::isDigit( $value, 3, 2 )) :
                // case 4. day  ---DD
                return $value;
            case (( 4 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 )) :
                // case 3, month  --MM
                return $value;
            case (( 6 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 4 )) :
                // case 3, month and day  --MMDD
                return substr( $value, 0, 4 ) . self::$DS1 . substr( $value, 4, 2 );
            case (( 8 === $strlen ) && ctype_digit( $value )) :
                // case 1, Ymd
                return substr( $value, 0, 4 ) . self::$DS1 .
                    substr( $value, 4, 2 ) . self::$DS1 .
                    substr( $value, 6, 2 );
            case (( 7 === $strlen ) &&
                self::isDigit( $value, 0, 4 ) &&
                self::equalDS1( $value, 4 ) &&
                self::isDigit( $value, 5, 2 )) :
                // case 2, Y-M
                return $value;
            case (( 4 === $strlen ) && ctype_digit( $value )) :
                // case 1, Y
                return $value;
        } // end switch
        return $value;
    }

    /**
     * Return vCard time incl opt zone converted to Jcard
     *
     * Jcard
     * time-notrunc =  hour [":" minute [":" second]] [zone]
     * time = time-notrunc
     *    / "-" minute ":" second [zone]; -mm:ss
     *    / "-" minute [zone]; -mm
     *    / "--" second [zone]; --ss
     * Vcard
     *    time = hour [minute [second]] [zone]    length exl zone 2 [ 4 [ 6 ]]] case 1
     *         /  "-"  minute [second]  [zone]    length exl zone 3 [ 5 ]       case 2
     *         /  "-"   "-"    second   [zone]    length exl zone 4             case 3
     *    zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return string
     */
    public static function convertVcard2JcardTime( string $value ) : string
    {
        $value  = trim( $value);
        $strlen = strlen( $value );
        $zone   = StringUtil::$SP0;
        switch( true ) {
            case (( 4 <= $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 )) :
                // case 3
                if( 4 < $strlen ) {
                    $zone = substr( $value, 4 );
                    if( self::isVcardZone( $zone )) {
                        $zone = self::convertVcard2JcardZone( $zone );
                    }
                }
                return substr( $value, 0, 4 ) . $zone;
            case (( 3 <= $strlen ) &&
                self::equalDS1( $value, 0 ) &&
                self::isDigit( $value, 1, 2 )) :
                // case 2
                $output = substr( $value, 0, 3 ); // hour
                if( 3 < $strlen ) {
                    if( self::isDigit( $value, 3, 2 )) {
                        $output .= StringUtil::$COLON . substr( $value, 3, 2 ); // min
                        $output .= (( 5 < $strlen ) && self::isVcardZone( substr( $value, 5 )))
                            ? self::convertVcard2JcardZone( substr( $value, 5 ))
                            : StringUtil::$SP0;
                    }
                    elseif( self::isVcardZone( substr( $value, 3 ))) {
                        $output .= self::convertVcard2JcardZone( substr( $value, 3 ));
                    }
                }
                return $output;
            case (( 6 <= $strlen ) && self::isDigit( $value, 0, 6 )) :
                // case 1, HHMMSS[zone]
                if( 6 < $strlen ) {
                    $zone = substr( $value, 6 );
                    if( ! self::isVcardZone( $zone )) {
                        break;
                    }
                }
                return substr( $value, 0, 2 ) . StringUtil::$COLON .
                    substr( $value, 2, 2 ) . StringUtil::$COLON .
                    substr( $value, 4, 2 ) . self::convertVcard2JcardZone( $zone );
            case (( 4 <= $strlen ) && self::isDigit( $value, 0, 4 )) :
                // case 1, HHMM[zone]
                if( 4 < $strlen ) {
                    $zone = substr( $value, 4 );
                    if( ! self::isVcardZone( $zone )) {
                        break;
                    }
                }
                return substr( $value, 0, 2 ) . StringUtil::$COLON .
                    substr( $value, 2, 2 ) . self::convertVcard2JcardZone( $zone );
            case (( 2 <= $strlen ) && self::isDigit( $value, 0, 2 )) :
                // case 1, HH[zone]
                if( 2 < $strlen ) {
                    $zone = substr( $value, 2 );
                    if( ! self::isVcardZone( $zone )) {
                        break;
                    }
                }
                return substr( $value, 0, 2 ) . self::convertVcard2JcardZone( $zone );
        } // end switch
        return $value;
    }

    /**
     * Return vCard zone converted to Jcard
     *
     * Jcard
     *   the hour and minute components are separated by a ":" character
     * Vcard
     *    zone   = utc-designator / utc-offset
     *       utc-designator = %x5A  ; uppercase "Z"
     *       utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return string
     */
    public static function convertVcard2JcardZone( string $value ) : string
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        switch( true ) {
            case empty( $value ) :
                return StringUtil::$SP0;
            case self::isAnyUtc( $value ) :
                return ( self::Zfmt === $value ) ? $value : StringUtil::$SP1 . $value;
            case ( ! in_array( $value[0], [ self::$PLUS, self::$MINUS ], true )) :
                break;
            case (( 3 === $strlen ) && self::isDigit( $value, 1, 2 )) :
                break;
            case (( 5 <= $strlen ) && self::isDigit( $value, 1, 4 )) :
                $sHour = substr( $value, 0, 3 );
                $min   = substr( $value, 3, 2 );
                $value = $sHour . StringUtil::$COLON . $min;
        } // end switch
        return $value;
    }

    /**
     * Vcard
     *   timestamp = date-complete time-designator time-complete
     *   date-complete = year     month  day
     *   time-designator = %x54  ; uppercase "T"
     *   time-complete = hour  minute  second   [zone]
     * Jcard
     *   the year, month and day components are separated by a "-" character
     *   followed by time-designator = %x54  ; uppercase "T"
     *   the hour, minute and second components are separated by a ":" character
     *   opt. trailing zone
     *
     * @param string $value
     * @return string
     */
    public static function convertVcard2JcardTimestamp( string $value ) : string
    {
        $value  = trim( $value );
        if( ! self::isVcardTimestamp( $value )) {
            return $value;
        }
        $output =
            substr( $value, 0, 4 ) .
            self::$DS1 .
            substr( $value, 4, 2 ) .
            self::$DS1 .
            substr( $value, 6, 2 ) .
            self::$T .
            substr( $value, 9, 2 ) .
            StringUtil::$COLON .
            substr( $value, 11, 2 ) .
            StringUtil::$COLON .
            substr( $value, 13, 2 );
        if( 15 < strlen( $value )) {
            $output .= self::convertVcard2JcardZone( substr( $value, 15 ));
        }
        return $output;
    }

    /**
     * Return Jcard date and/or time converted to vCard
     *
     * @param string $value
     * @param string $valueType
     * @return string
     */
    public static function convertJcard2VcardDates( string $value, string $valueType ) : string
    {
        switch( $valueType ) {
            case self::DATEANDORTIME :
            case self::DATETIME :
            case self::TIMESTAMP :
                // the same date and time format restrictions noted in ... apply
                if( false === strpos( $value, self::$T )) {
                    return self::convertJcard2VcardDate( $value );
                }
                $date = StringUtil::before( self::$T, $value );
                $time = StringUtil::after( self::$T, $value );
                return self::convertJcard2VcardDate( $date ) . self::$T . self::convertJcard2VcardTime( $time );
            case self::DATE :
                return self::convertJcard2VcardDate( $value );
            case self::TIME :
                return self::convertJcard2VcardTime( $value );
            case self::TEXT : // fall through
            default :
                return $value;
        } // end switch
    }

    /**
     * Return Jcard date converted to vCard
     *
     * Jcard
     *    date-complete = year "-" month "-" day ;YYYY-MM-DD
     *    date-noreduc = date-complete
     *    / "--" month "-" day ; --MM-DD
     *    / "---" day          ; ---DDD
     *    date = date-noreduc
     *    / year; YYYY
     *    / year "-" month ; YYYY-MM
     *    / "--" month     ; --MM
     * Vcard
     *    date = year    [month  day]   case 1
     *    / year "-" month              case 2
     *    / "--"     month [day]        case 3
     *    / "--"      "-"   day         case 4
     *
     * @param string $value
     * @return string
     */
    public static function convertJcard2VcardDate( string $value ) : string
    {
        $value  = trim( $value);
        $strlen = strlen( $value );
        switch( true ) {
            case (( 10 === $strlen ) &&
                self::isDigit( $value, 0, 4 ) &&
                self::equalDS1( $value, 4 ) &&
                self::isDigit( $value, 5, 2 ) &&
                self::equalDS1( $value, 7 ) &&
                self::isDigit( $value, 8, 2 )) :
                // Y-m-d
                return substr( $value, 0, 4 ) .
                    substr( $value, 5, 2 ) .
                    substr( $value, 8, 2 );
            case (( 7 === $strlen ) &&
                self::isDigit( $value, 0, 4 ) &&
                self::equalDS1( $value, 4 ) &&
                self::isDigit( $value, 5, 2 )) :
                // Y-m
                return $value;
            case (( 7 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 ) &&
                self::equalDS1( $value, 4 ) &&
                self::isDigit( $value, 5, 2 )) :
                // --m-d
                return self::$DS2 . substr( $value, 2, 2 ) . substr( $value, 5, 2 );
            case (( 5 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::equalDS1( $value, 2 ) &&
                self::isDigit( $value, 3, 2 )) :
                // ---d
                return $value;
            case (( 4 === $strlen ) && self::isDigit( $value, 0, 4 )) :
                // Y
                return substr( $value, 0, 4 );
            case (( 4 === $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 )) :
                // --m
                return self::$DS2 . substr( $value, 2, 2 );
        } // end switch
        return $value;
    }

    /**
     * Return Jcard time incl opt zone converted to vCard
     *
     * Jcard
     * time-notrunc =  hour [":" minute [":" second]] [zone]
     * time = time-notrunc
     *    / "-" minute ":" second [zone]; -mm:ss
     *    / "-" minute [zone]; -mm
     *    / "--" second [zone]; --ss
     * Vcard
     *    time-designator = %x54  i.e. uppercase "T"
     *    time = hour [minute [second]] [zone]    length exl zone 2 [ 4 [ 6 ]]] case 1
     *         /  "-"  minute [second]  [zone]    length exl zone 3 [ 5 ]       case 2
     *         /  "-"   "-"    second   [zone]    length exl zone 4             case 3
     *    zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return string
     */
    public static function convertJcard2VcardTime( string $value ) : string
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        $zone   = StringUtil::$SP0;
        switch( true ) {
            case ( str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 )) :
                // case 3, sec, opt zone
                if(( 4 < $strlen ) && self::isJcardZone( substr( $value, 4 ))) {
                    $zone = self::convertJcard2VcardZone( substr( $value, 4 ));
                }
                return substr( $value, 0, 4 ) . $zone;
            case ( self::equalDS1( $value, 0 ) && self::isDigit( $value, 1, 2 )) :
                // case 2 min
                $output = substr( $value, 0, 3 );
                if( 3 < $strlen ) {
                    if(( StringUtil::$COLON === $value[3] ) &&
                        ( 5 <= $strlen ) &&
                        self::isDigit( $value, 4, 2 )) {
                        $output .= substr( $value, 4, 2 ); // sec
                        if(( 6 < $strlen ) && self::isJcardZone( substr( $value, 6 ))) {
                            $output .= self::convertJcard2VcardZone( substr( $value, 6 ) );
                        }
                    }
                    elseif( self::isJcardZone( substr( $value, 3 ))) {
                        $output .= self::convertJcard2VcardZone( substr( $value, 3 ));
                    }
                }
                return $output;
            case self::equalDS1( $value, 0 ) :
                break;
            case (( 8 <= $strlen ) &&
                self::isDigit( $value, 0, 2 ) &&
                ( StringUtil::$COLON === $value[2] ) &&
                self::isDigit( $value, 3, 2 ) &&
                ( StringUtil::$COLON === $value[5] ) &&
                self::isDigit( $value, 6, 2 )) :
                // case 1, full
                $output = substr( $value, 0, 2 ) .
                    substr( $value, 3, 2 ) .
                    substr( $value, 6, 2 );
                if(( 8 < $strlen ) && self::isJcardZone( substr( $value, 8 ))) {
                    $zone = self::convertJcard2VcardZone( substr( $value, 8 ));
                }
                return $output . $zone;
            case (( 5 <= $strlen ) &&
                self::isDigit( $value, 0, 2 ) &&
                ( StringUtil::$COLON === $value[2] ) &&
                self::isDigit( $value, 3, 2 )) :
                // case 1, hour + min, opt zone
                $output = substr( $value, 0, 2 ) .
                    substr( $value, 3, 2 );
                if(( 5 < $strlen ) && self::isJcardZone( substr( $value, 5 ))) {
                    $output .= self::convertJcard2VcardZone( substr( $value, 5 ));
                }
                return $output;
            case (( 2 <= $strlen ) && self::isDigit( $value, 0, 2 )) :
                // case 1, hour, opt zone
                $output = substr( $value, 0, 2 );
                if(( 2 < $strlen ) && self::isJcardZone( substr( $value, 2 ))) {
                    $output .= self::convertJcard2VcardZone( substr( $value, 2 ));
                }
                return $output;
        } // end switch
        return $value;
    }

    /**
     * Return Jcard zone converted to vCard
     *
     * Jcard
     *   the hour and minute components are separated by a ":" character
     * Vcard
     *    zone   = utc-designator / utc-offset
     *       utc-designator = %x5A  ; uppercase "Z"
     *       utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return string
     */
    public static function convertJcard2VcardZone( string $value ) : string
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        switch( true ) {
            case ( empty( $value ) || self::isAnyUtc( $value )) :
                break;
            case ( ! in_array( $value[0], [ self::$PLUS, self::$MINUS ], true )) :
                break;
            case (( 3 === $strlen ) && self::isDigit( $value, 1, 2 )) :
                break;
            case (( 5 <= $strlen ) &&
                self::isDigit( $value, 1, 2 ) &&
                ( StringUtil::$COLON === $value[3] ) &&
                self::isDigit( $value, 4, 2 )) :
                $output  = substr( $value, 0, 3 );
                $output .= substr( $value, 4, 2 );
                $value = $output;
        } // end switch
        return $value;
    }

    /**
     * Return Jcard timestamp converted to vCard
     *
     * Vcard
     *   timestamp = date-complete time-designator time-complete
     *   date-complete = year     month  day
     *   time-designator = %x54  ; uppercase "T"
     *   time-complete = hour  minute  second   [zone]
     * Jcard
     *   the year, month and day components are separated by a "-" character
     *   followed by time-designator = %x54  ; uppercase "T"
     *   the hour, minute and second components are separated by a ":" character
     *   opt. trailing zone
     *
     * @param string $value
     * @return string
     */
    public static function convertJcard2VcardTimestamp( string $value ) : string
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        if(( 19 > $strlen ) ||
            ( false === strtotime( substr( $value, 0, 19 )))) {
            return $value;
        }
        return
            substr( $value, 0, 4 ) .
            substr( $value, 5, 2 ) .
            substr( $value, 8, 2 ) .
            self::$T .
            substr( $value, 11, 2 ) .
            substr( $value, 14, 2 ) .
            substr( $value, 17, 2 ) .
            (( 19 < $strlen ) ? self::convertJcard2VcardZone( substr( $value, 19 )) : StringUtil::$SP0 );
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardDateAndOrTime( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'date-and-or-time\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardDateTime( $value ) &&
            ! self::isVcardDate( $value ) &&
            ! self::isVcardTime( $value )) {
            throw new InvalidArgumentException( sprintf( $ERR ,$propName, $value ));
        }
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardDateTime( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'datetime\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardDateTime( $value )) {
            throw new InvalidArgumentException( sprintf( $ERR ,$propName, $value ));
        }
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardDate( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'date\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardDate( $value)) {
            throw new InvalidArgumentException( sprintf( $ERR ,$propName, $value ));
        }
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardTime( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'time\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardTime( $value)) {
            throw new InvalidArgumentException( sprintf( $ERR ,$propName, $value ));
        }
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardTimestamp( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'timestamp\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardTimestamp( $value )) {
            throw new InvalidArgumentException( sprintf( $ERR ,$propName, $value ));
        }
    }

    /**
     * @param string $propName
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function assertVcardOffset( string $propName, string $value ) : void
    {
        static $ERR = 'Value for %s type \'offset\' not accepted, got %s';
        $value = trim( $value );
        if( ! self::isVcardOffset( $value )) {
            throw new InvalidArgumentException( sprintf( $ERR, $propName, $value ));
        }
    }

    /**
     * Return bool true if value is a Vcard dateTime
     * 
     * date-time = date-noreduc  time-designator time-notrunc
     *    date-noreduc  = year     month  day
     *    / "--"     month  day
     *    / "--"      "-"   day
     *    time-designator = %x54  ; uppercase "T"
     *    time-notrunc  = hour [minute [second]] [zone]
     *    zone   = utc-designator / utc-offset
     *
     * @param string $value
     * @return bool
     */
    public static function isVcardDateTime( string $value ) : bool
    {
        if( false === strpos( $value, self::$T )) {
            return false;
        }
        $date = StringUtil::before( self::$T, $value );
        $time = StringUtil::after( self::$T, $value );
        return self::isVcardDate( $date ) && self::isVcardTime( $time );
    }

    /**
     * Return bool true if value is a Vcard date
     *
     *    date = year    [month  day]   case 1
     *    / year "-" month              case 2
     *    / "--"     month [day]        case 3
     *    / "--"      "-"   day         case 4
     *
     * @param string $value
     * @return bool
     */
    public static function isVcardDate( string $value ) : bool
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        switch( true ) {
            case (( 5 <= $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::equalDS1( $value, 2 ) &&
                self::isDigit( $value, 3, 2 )) :
                // case 4, no year-month but day exists ---DD
                $day = (int) substr( $value, 3 );
                return (( 1 <= $day ) && ( $day <= 31 ));
            case (( 4 <= $strlen ) &&
                str_starts_with( $value, self::$DS2 ) &&
                self::isDigit( $value, 2, 2 )) :
                // case 3, no year but month exists and opt day
                $value = substr( $value, 2 );
                $year  = 2024;
                break;
            case (( 6 <= $strlen ) &&
                self::isDigit( $value, 0, 4 ) &&
                self::equalDS1( $value, 4 ) &&
                self::isDigit( $value, 5, 2 )) :
                // case 2, year-month exists but no day
                $year  = (int) substr( $value, 0,4 );
                $value = substr( $value, 5, 2 );
                break;
            case (( 4 > $strlen ) ||
                ! self::isDigit( $value, 0, 4 )) : // odd year
                return false;
            default :
                // case 1, test year
                $year = (int) substr( $value, 0, 4 );
                $value = substr( $value, 4 );
                if( empty( $value )) {
                    return true;
                }
        } // end switch
        // month
        $month = (int) substr( $value, 0, 2 );
        if(( 1 > $month ) || ( $month > 12 )) {
            return false;
        }
        $value = substr( $value, 2 );
        if( empty( $value )) {
            return true;
        }
        // day
        if( ! self::isDigit( $value, 0, 2 )) {
            return false;
        }
        $day = (int) substr( $value, 0, 2 );
        return checkdate( $month, $day, $year );
    }

    /**
     * Return bool true if value is a time
     *
     *    time = hour [minute [second]] [zone]    length exl zone 2 [ 4 [ 6 ]]] case 1
     *         /  "-"  minute [second]  [zone]    length exl zone 3 [ 5 ]       case 2
     *         /  "-"   "-"    second   [zone]    length exl zone 4             case 3
     *    zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset = sign hour [minute]
     * @param string $value
     * @return bool
     */
    public static function isVcardTime( string $value ) : bool
    {
        static $ZERO2 = '00';
        $zone1  = [ self::$Z, self::$PLUS, self::$MINUS ];
        $value  = trim( $value );
        $strlen = strlen( $value );
        switch( true ) {
            case str_starts_with( $value, self::$DS2 ) :
                // case 3, sec only, opt zone
                $hour = $min = $ZERO2;
                $sec  = substr( $value, 2, 2 );
                $zone = ( 4 < $strlen ) ? substr( $value, 4 ) : StringUtil::$SP0;
                break;
            case self::equalDS1( $value, 0 ) :
                // case 2, minute, opt sec/zone
                $hour = $ZERO2;
                $min  = substr( $value, 1, 2 );
                if( 3 >= $strlen ) {
                    $sec  = $ZERO2;
                    $zone = StringUtil::$SP0;
                    break;
                }
                if( in_array( $value[3], $zone1, true )) {
                    $sec  = $ZERO2;
                    $zone = substr( $value, 3 );
                    break;
                }
                $sec  = substr( $value, 3, 2 );
                $zone = ( 5 < $strlen ) ? substr( $value, 5 ) : StringUtil::$SP0;
                break;
            default :
                // case 1, hour only, opt minute/sec/zone
                $hour = substr( $value, 0, 2 );
                if( 2 >= $strlen ) {
                    $min = $sec = $ZERO2;
                    $zone = StringUtil::$SP0;
                    break;
                }
                if( in_array( $value[2], $zone1, true )) {
                    $min = $sec = $ZERO2;
                    $zone = substr( $value, 2 );
                    break;
                }
                $min  = substr( $value, 2, 2 );
                if( 4 >= $strlen ) {
                    $sec = $ZERO2;
                    $zone = StringUtil::$SP0;
                    break;
                }
                if( in_array( $value[4], $zone1, true )) {
                    $sec = $ZERO2;
                    $zone = substr( $value, 4 );
                    break;
                }
                $sec  = substr( $value, 4, 2 );
                $zone = ( 6 < $strlen ) ? substr( $value, 6 ) : StringUtil::$SP0;
                break;
        } // end switch
        return ( self::isHour( $hour ) &&
            self::isMinute( $min ) &&
            self::isSecond( $sec ) &&
            self::isVcardZone( $zone ));
    }

    /**
     * Return bool true if value is an hour
     *
     * @param string $value
     * @return bool
     */
    private static function isHour( string $value ) : bool
    {
        return self::isIntMinmax( $value, 23 );
    }

    /**
     * Return bool true if value is a minute
     *
     * @param string $value
     * @return bool
     */
    private static function isMinute( string $value ) : bool
    {
        return self::isIntMinmax( $value, 59 );
    }

    /**
     * Return bool true if value is a second
     *
     * @param string $value
     * @return bool
     */
    private static function isSecond( string $value ) : bool
    {
        return self::isIntMinmax( $value, 59 );
    }

    /**
     * @param string $value
     * @param int    $max  inclusive
     * @return bool
     */
    private static function isIntMinmax( string $value, int $max ) : bool
    {
        if( ! ctype_digit( $value[0] ) && ! ctype_digit( $value[1] )) {
            return false;
        }
        $int = (int) substr( $value, 0, 2 );
        return (( 0 <= $int ) && ( $int <= $max ));
    }

    /**
     * Return bol true on no zone OR valid Vcard zone
     *
     * zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return bool
     */
    public static function isVcardZone( string $value ) : bool
    {
        static $PMarr = [ '-0000', '+0000' ];
        $value  = trim( $value );
        $strlen = strlen( $value );
        switch( true ) {
            case ( empty( $value ) ||
                in_array( $value, $PMarr, true ) ||
                self::isAnyUtc( $value )) :
                return true;
            case ( ! in_array( $value[0], [ self::$PLUS, self::$MINUS ], true )) :
                return false;
            case (( 3 === $strlen ) && self::isDigit( $value, 1, 2 )) :
                $hour = substr( $value, 1, 2 );
                return self::isHour( $hour );
            case (( 5 <= $strlen ) && self::isDigit( $value, 1, 4 )) :
                $hour = substr( $value, 1, 2 );
                $min  = substr( $value, 3, 2 );
                return ( self::isHour( $hour ) && self::isMinute( $min ));
        } // end switch
        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    private static function isAnyUtc( string $value ) : bool
    {
        return in_array( $value, [ self::GMTfmt, self::UTCfmt, self::Zfmt ], true );
    }

    /**
     * Return bol true on valid Jcard ofset
     *
     *    utc-offset = sign hour [minute]
     *
     * @param string $value
     * @return bool
     */
    public static function isVcardOffset( string $value ) : bool
    {
        $value  = trim( $value );
        if( isset( $value[0] ) && ! in_array( $value[0], [ self::$PLUS, self::$MINUS ], true )) {
            return false;
        }
        $strlen = strlen( $value );
        if(( 3 === $strlen ) &&  self::isDigit( $value, 1, 2 )) {
            $hour = substr( $value, 1, 2 );
            return self::isHour( $hour );
        }
        if(( 5 <= $strlen ) && self::isDigit( $value, 1, 4 )) {
            $hour = substr( $value, 1, 2 );
            $min  = substr( $value, 3, 2 );
            return ( self::isHour( $hour ) && self::isMinute( $min ));
        }
        return false;
    }

    /**
     * Return bool true on no zone OR valid Jcard zone
     *
     * zone   = utc-designator / utc-offset
     *    utc-designator = %x5A  ; uppercase "Z"
     *    utc-offset = sign hour [ : minute]
     *
     * @param string $value
     * @return bool
     */
    public static function isJcardZone( string $value ) : bool
    {
        $value = trim( $value );
        return ( ! empty( $value ) &&
            self::isVcardZone( str_replace( StringUtil::$COLON, StringUtil::$SP0, $value )));
    }

    /**
     * Return bool true if value is a Vcard timestamp
     *
     * timestamp = date-complete time-designator time-complete
     * date-complete   = year     month  day
     * time-designator = %x54  ; uppercase "T"
     * time-complete   = hour  minute  second   [zone]
     *
     * @param string $value
     * @return bool
     */
    public static function isVcardTimestamp( string $value ) : bool
    {
        $value  = trim( $value );
        $strlen = strlen( $value );
        if( 15 > $strlen ) {
            return false;
        }
        if( ! self::isDigit( $value, 0, 8 ) ||
            ( self::$T !== $value[8] ) ||
            ! self::isDigit( $value, 9, 6 ) ||
            ( false === strtotime( substr( $value, 0, 15 )))) {
            return false;
        }
        return ( 15 === $strlen ) || self::isVcardZone( substr( $value, 15 ));
    }

    /**
     * @param string $value
     * @param int $pos
     * @return bool
     */
    private static function equalDS1( string $value, int $pos ) : bool
    {
        return ( self::$DS1 === $value[$pos] );
    }

    /**
     * @param string $value
     * @param int $offset
     * @param int $length
     * @return bool
     */
    private static function isDigit( string $value, int $offset, int $length ) : bool
    {
        return ctype_digit( substr( $value, $offset, $length ));
    }
}
