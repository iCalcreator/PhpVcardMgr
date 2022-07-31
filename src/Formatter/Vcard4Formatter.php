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
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class Vcard4Formatter implements FormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format( array $vCards ) : string
    {
        $propRows = [];
        foreach( $vCards as $vCard ) {
            $propRows[] = self::BEGIN_VCARD;
            foreach( $vCard->getProperties() as $property ) {
                $propName = $property->getPropName();
                switch( $propName ) {
                    case self::CATEGORIES :  // fall through
                    case self::NICKNAME :
                        $propRows[] = self::processValueArrComma( $property );
                        break;
                    case self::ADR :          // fall through
                    case self::N :            // fall through
                    case self::CLIENTPIDMAP : // fall through
                    case self::GENDER :
                        $propRows[] = self::processValueArrSemic( $property );
                        break;
                    case self::MEMBER :
                        $propRows[] = self::processMember( $property );
                        break;
                    case self::ORG :
                        $propRows[] = self::processOrg( $property );
                        break;
                    case self::TEL :
                        $propRows[] = self::processNameParameters( $property ) . $property->getValue();
                        break;
                    case self::ANNIVERSARY : // fall through
                    case self::BDAY :        // fall through
                    case self::CALADRURI :   // fall through
                    case self::CALURI :      // fall through
                    case self::EMAIL :       // fall through
                    case self::FBURL :       // fall through
                    case self::FN :          // fall through
                    case self::GEO :         // fall through
                    case self::IMPP :        // fall through
                    case self::KEY :         // fall through
                    case self::KIND :        // fall through
                    case self::LANG  :       // fall through
                    case self::LOGO :        // fall through
                    case self::NOTE :        // fall through
                    case self::PHOTO :       // fall through
                    case self::PRODID :      // fall through
                    case self::RELATED :     // fall through
                    case self::REV :         // fall through
                    case self::ROLE :        // fall through
                    case self::SOUND :       // fall through
                    case self::SOURCE :      // fall through
                    case self::TITLE :       // fall through
                    case self::TZ :          // fall through
                    case self::UID :         // fall through
                    case self::URL :         // fall through
                    case self::VERSION :     // fall through
                    case self::XML :
                        $propRows[] = self::processAsIs( $property);
                        break;
                    default :
                        if( ! StringUtil::isXprefixed( $propName )) {
                            continue 2;
                        }
                        $propRows[] = self::processAsIs( $property);
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
     * @param PropertyInterface $property
     * @return string
     */
    private static function processNameParameters( PropertyInterface $property ) : string
    {
        return $property->getGroupPropName() .
            VcardFormatterUtil::createParams(
                VcardFormatterUtil::prepParameters( $property ),
                $property::getAcceptedParameterKeys()
            ) .
            StringUtil::$COLON;
    }

    /**
     * @param PropertyInterface $property
     * @return string
     * @todo more protocols..?
     */
    private static function processMember( PropertyInterface $property ) : string
    {
        static $MAILTO = 'mailto:';
        $value = $property->getValue();
        if( 0 !== stripos( substr( $value, 0, 7 ), $MAILTO ) &&
            ( false !== filter_var( $value, FILTER_VALIDATE_EMAIL ))) {
            $value = $MAILTO . $value;
        }
        return self::processNameParameters( $property ) .
            VcardFormatterUtil::strrep( $value );
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
    private static function processAsIs( PropertyInterface $property ) : string
    {
        $value = $property->getValue();
        if( self::TEXT === $property->getValueType()) {
            $value = VcardFormatterUtil::strrep( $value );
        }
        return self::processNameParameters( $property ) . $value;
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
}
