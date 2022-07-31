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
namespace Kigkonsult\PhpVcardMgr\Property;

use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

/**
 * ADR
 *
* the components of the delivery address for the vCard object
 *
 * Value type:  A single structured text value, separated by the SEMICOLON character
 *              Each component can have multiple (comma-separated) values.
 * Cardinality:  *, One or more instances per vCard MAY be present.
 *
 * label-param = "LABEL=" param-value
 *
 * ADR-param = "VALUE=text" / label-param / language-param / geo-parameter / tz-parameter / altid-param / pid-param
 *             / pref-param / type-param / any-param
 *
 * ADR-value = ADR-component-pobox ";" ADR-component-ext ";"
 *             ADR-component-street ";" ADR-component-locality ";"
 *             ADR-component-region ";" ADR-component-code ";"
 *             ADR-component-country
 * ADR-component-pobox    = list-component
 * ADR-component-ext      = list-component
 * ADR-component-street   = list-component
 * ADR-component-locality = list-component
 * ADR-component-region   = list-component
 * ADR-component-code     = list-component
 * ADR-component-country  = list-component
 */
final class Adr extends PropertyBase
{
    /**
     * The structured type value
     *
     * ...consists of a sequence of address components, separated by the SEMICOLON character
     * The component values MUST be specified in their corresponding position.
     * Each component can have multiple (comma-separated) values.
     *
     * @var string[]
     */
    public static $valueComponents = [
        'pobox',
        'ext',
        'street',
        'locality',
        'region',
        'code',
        'country',
    ];

    /**
     * @inheritDoc
     */
    public function getPropName() : string
    {
        return self::ADR;
    }

    /**
     * @inheritDoc
     */
    public static function getAcceptedParameterKeys() : array
    {
        return [
            self::VALUE,
            self::LABEL,
            self::LANGUAGE,
            self::GEO,
            self::TZ,
            self::ALTID,
            self::PID,
            self::PREF,
            self::TYPE,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function isAnyParameterAllowed() : bool
    {
        return true;
    }

    /**
     * @override
     * @param string|array $value    list-component  string (pobox) or a 7-items array
     * @param null|string|array $ext list-component   or parameters
     * @param null|string $street    list-component
     * @param null|string $locality  list-component
     * @param null|string $region    list-component
     * @param null|string $code      list-component
     * @param null|string $country   list-component
     */
    public function setValue(
        $value,
        ? string $ext = null,
        ? string $street = null,
        ? string $locality = null,
        ? string $region = null,
        ? string $code = null,
        ? string $country = null
    ) : PropertyInterface
    {
        static $ERR = 'Adr expects pobox/ext/street/locality/region/code/country, got ' ;
        switch( true ) {
            case ( is_string(  $value ) && ( false !== strpos( $value, StringUtil::$SEMIC ))) :
                $value = explode( StringUtil::$SEMIC, $value );
                // fall through
            case is_array( $value ) :
                $arrCnt = count( $value );
                switch( true ) {
                    case ( 7 === $arrCnt ) :
                        break;
                    case ( 7 > $arrCnt ) :
                        $value = array_pad( $value, 7, StringUtil::$SP0 );
                        break;
                    default :
                        throw new InvalidArgumentException( $ERR . var_export( $value, true ));
                } // end switch
                break;
            default :
                $value = [ $value, $ext, $street, $locality, $region, $code, $country ];
        } // end switch
        $value = self::trimSub( $value );
        if( empty( implode( $value ))) {
            throw new InvalidArgumentException( $ERR . var_export( $value, true ));
        }
        $this->value = $value;
        return $this;
    }
}
