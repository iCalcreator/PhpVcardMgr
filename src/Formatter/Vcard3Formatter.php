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

use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class Vcard3Formatter implements FormatterInterface
{
    /**
     * @var string[]
     */
    protected static $UNIQUE3PROPS = [ 'AGENT', 'CLASS', 'LABEL', 'MAILER', 'NAME' ];

    /**
     * @inheritDoc
     */
    public function format( array $vCards ) : string
    {
        static $VERSIONno = '3.0';
        $propRows = [];
        foreach( $vCards as $vCard ) {
            $propRows[] = self::BEGIN_VCARD;
            foreach( $vCard->getProperties() as $property ) {
                $propName = $property->getPropName();
                switch( $propName ) {
                    case self::VERSION :
                        $propRows[] = self::VERSION . StringUtil::$COLON . $VERSIONno;
                        break;
                    case self::BDAY :     // fall through
                    case self::REV :
                        $propRows[] = self::processDate( $property );
                        break;
                    case self::KEY :
                        $propRows[] = self::processKey( $property );
                        break;
                    case self::SOUND :
                        $propRows[] = self::processSound( $property );
                        break;
                    case self::UID :
                        $propRows[] = self::processUid( $property );
                        break;
                    case self::LOGO :     // fall through
                    case self::NICKNAME : // fall through
                    case self::PHOTO :    // fall through
                    case self::SOURCE :   // fall through
                    case self::TEL :      // fall through
                    case self::URL :
                        $propRows[] = self::processAsIs( $property );
                        break;
                    case self::ORG :
                        $propRows[] = self::processOrg( $property );
                        break;
                    case self::GEO :
                        $propRows[] = self::processGeo( $property);
                        break;
                    case self::TZ :
                        $propRows[] = self::processTz( $property );
                        break;
                    case self::N :
                        foreach( self::processN( $property ) as $row ) {
                            $propRows[] = $row;
                        }
                        break;
                    case self::ADR :
                        $propRows[] = self::processValueArrSemic( $property );
                        break;
                    case self::CATEGORIES :
                        $propRows[] = self::processValueArrComma( $property );
                        break;
                    case self::PRODID :   // fall througyh
                    case self::EMAIL :    // fall througyh
                    case self::FN :       // fall througyh
                    case self::NOTE :     // fall througyh
                    case self::ROLE :     // fall througyh
                    case self::TITLE :    // fall througyh
                        $propRows[] = self::processString( $property);
                        break;
                    default :
                        $propRows[] = in_array( substr( $propName, 2 ), self::$UNIQUE3PROPS, true )
                            ? self::processX4Props( $property) // format as Vcard3 prop
                            : self::process4props( $property); // format as Vcard3 X-prop
                        break;
                } // end switch
            } // end foreach
            $propRows[] = self::END_VCARD;
        } // end foreach
        $output = StringUtil::$SP0;
        foreach( $propRows as $propRow ) {
            $output .= VcardFormatterUtil::size75( $propRow );
        }
        return $output;
    }

    /**
     * Remove X-prefix from some prameter keys
     *
     * @param string[]  $parameters
     * @return string[]
     */
    private static function unprepXparameters( array $parameters ) : array
    {
        static $specPkeys = [
            'CHARSET',
            'CONTEXT',
            'ENCODING'
        ];
        foreach( $specPkeys as $specPkey ) {
            $xKey = self::XPREFIX . $specPkey;
            if( ! isset( $parameters[$xKey] )) {
                continue;
            }
            $parameters[$specPkey] = $parameters[$xKey];
            unset( $parameters[$xKey] );
        }
        return $parameters;
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processNameParameters( PropertyInterface $property ) : string
    {
        return $property->getGroupPropName() .
            VcardFormatterUtil::createParams(
                self::unprepXparameters( $property->getParameters()),
                $property::getAcceptedParameterKeys()
            ) .
            StringUtil::$COLON;
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processAsIs( PropertyInterface $property ) : string
    {
        $value = $property->getValue();
        if( self::TEXT === $property->getValueType() && ( self::NICKNAME !== $property->getPropName())) {
            $value = VcardFormatterUtil::strrep( $value );
        }
        return self::processNameParameters( $property ) . $value;
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processDate( PropertyInterface $property ) : string
    {
        $valueType  = $property->getValueType();
        $parameters = $property->getParameters();
        $propName   = $property->getPropName();
        if(( self::BDAY === $propName ) && ( self::DATE !== $valueType )) {
            $parameters[self::VALUE] = self::DATETIME;
        }
        elseif(( self::REV === $propName ) && ( self::TIMESTAMP !== $valueType )) {
            $parameters[self::VALUE] = $valueType;
        }
        else {
            unset( $parameters[self::VALUE] );
        }
        return $property->getGroupPropName() .
            VcardFormatterUtil::createParams(
                self::unprepXparameters( $parameters ),
                $property::getAcceptedParameterKeys()
            ) .
            StringUtil::$COLON .
            DateUtil::convertVcard2JcardDates( $property->getValue(), $valueType );
    }

    /**
     * A single structured value consisting of two float values separated by the SEMI-COLON character
     *
     * @param PropertyInterface $property
     * @return string
     */
    private static function processGeo( PropertyInterface $property ) : string
    {
        static $GEOprefix = 'geo:';
        $value = $property->getValue();
        if( false !== strpos( $value, StringUtil::$SEMIC )) {
            $value = StringUtil::before( StringUtil::$SEMIC, $value );
        }
        if( 0 === strpos( $value, $GEOprefix )) {
            $value = substr( $value, 4 );
        }
        $value = explode( StringUtil::$COMMA, $value );
        return self::processNameParameters( $property ) .
            $value[0] . StringUtil::$SEMIC . $value[1];
    }

    /**
     * @param PropertyInterface $property
     * @return string[]
     */
    private static function processN( PropertyInterface $property ) : array
    {
        static $SORTSTRING = 'SORT-STRING';
        $property     = clone $property;
        $output       = [];
        $row          = null;
        if( $property->hasParameter( self::SORT_AS )) {
            $row      = $SORTSTRING . StringUtil::$COLON .
                VcardFormatterUtil::strrep( $property->getParameters( self::SORT_AS ));
            $property->unsetParameter( self::SORT_AS );
        }
        $output[]     = self::processValueArrSemic( $property );
        if( ! empty( $row )) {
            $output[] = $row;
        }
        return $output;
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processKey( PropertyInterface $property ) : string
    {
        $property = clone $property;
        $orgType  = $property->getParameters( self::TYPE );
        if( self::URI === $orgType ) {
            $property->unsetParameter( self::TYPE );
        }
        $value = $property->getValue();
        return self::processNameParameters( $property ) .
            (
                ( self::TEXT === $orgType )
                    ? VcardFormatterUtil::strrep( $value )
                    : $value
            );
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processOrg( PropertyInterface $property ) : string
    {
        $value = $property->getValue();
        if( is_array( $value )) {
            $value = implode( StringUtil::$SEMIC, $value);
        }
        return self::processNameParameters( $property ) .
            VcardFormatterUtil::escapeChar( [ StringUtil::$COMMA ], $value );
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processSound( PropertyInterface $property ) : string
    {
        $property = clone $property;
        if( self::URI === $property->getValueType()) {
            $property->addParameter( self::VALUE, self::URI );
        }
        return self::processNameParameters( $property ) . $property->getValue();
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processUid( PropertyInterface $property ) : string
    {
        $property = clone $property;
        if( self::URI === $property->getValueType()) {
            $property->unsetParameter( self::VALUE );
        }
        return self::processNameParameters( $property ) . $property->getValue();
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processString( PropertyInterface $property ) : string
    {
        return self::processNameParameters( $property ) .
            VcardFormatterUtil::strrep( $property->getValue());

    }

    /**
     * Vcard3 :
     * The default is a single utc-offset value.
     * It can also be reset to a single text value.
     * Vcard4 :
     * The default is a single text value.
     * It can also be reset to a single URI or utc-offset value.
     *
     * @param PropertyInterface $property
     * @return string
     */
    private static function processTz( PropertyInterface $property ) : string
    {
        $property = clone $property;
        $orgType  = $property->getParameters( self::TYPE );
        if( ! $property->hasTypeParameter()) { // default text
            $property->addParameter( self::TYPE, self::TEXT );
        }
        elseif( self::URI === $orgType ) {
            $property->addParameter( self::TYPE, self::URI );
        }
        $value = $property->getValue();
        return self::processNameParameters( $property ) .
            (
                ( self::UTCOFFSET === $orgType )
                    ? DateUtil::convertVcard2JcardZone( $value )
                    : $value
            );
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processValueArrComma( PropertyInterface $property ) : string
    {
        return self::processNameParameters( $property ) .
            implode( StringUtil::$COMMA, $property->getValue());
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function processValueArrSemic( PropertyInterface $property ) : string
    {
        return self::processNameParameters( $property ) .
            implode( StringUtil::$SEMIC, $property->getValue());
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private static function process4props( PropertyInterface $property ) : string
    {
        $propName = $property->getPropName();
        if( strpos( $propName, self::XPREFIX ) !== 0 ) {
            $propName = self::XPREFIX . $propName;
        }
        $value    = $property->getValue();
        if( is_array( $value )) {
            $value = implode( StringUtil::$SEMIC, $value );
        }
        elseif( self::TEXT === $property->getValueType()) {
            $value = VcardFormatterUtil::strrep( $value );
        }
        return ( $property->isGroupSet() ? $property->getGroup() . StringUtil::$DOT . $propName : $propName ) .
            VcardFormatterUtil::createParams(
                self::unprepXparameters( $property->getParameters()),
                $property::getAcceptedParameterKeys()
            ) .
            StringUtil::$COLON .
            $value;
    }

    /**
     * Process Vcard3 unique props, saved as Vcard4 X-props
     *
     * @param PropertyInterface $property
     * @return string
     */
    private static function processX4Props( PropertyInterface $property ) : string
    {
        $propName = substr( $property->getPropName(), 2 );
        $value    = $property->getValue();
        $value =  is_array( $value )
            ? implode( StringUtil::$SEMIC, $value )
            : VcardFormatterUtil::strrep( $property->getValue());
        return ( $property->isGroupSet() ? $property->getGroup() . StringUtil::$DOT . $propName : $propName ) .
            VcardFormatterUtil::createParams(
                self::unprepXparameters( $property->getParameters()),
                $property::getAcceptedParameterKeys()
            ) .
            StringUtil::$COLON .
            $value;
    }
}
