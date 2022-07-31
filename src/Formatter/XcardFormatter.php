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

use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use XMLWriter;

class XcardFormatter extends XcardFormatterBase
{
    /**
     * @inheritDoc
     */
    public function format( ? array $vCards = null ) : string
    {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent( true );
        $this->writer->startDocument( '1.0', 'UTF-8' );
        $this->writer->startElement( self::XVCARDS );
        $this->writer->startAttribute( self::XVCARDSAttr );
        $this->writer->text( self::XVCARDSAttrval );
        $this->writer->endAttribute();
        $xcardPropertyFormatter = XcardPropertyFormatter::factory( $this->writer );
        foreach( $vCards as $vCard ) {
            $prevGroup = StringUtil::$SP0;
            $this->writer->startElement( self::XVCARD ); // start xCard
            foreach( $vCard->getProperties() as $property) {
                if( self::VERSION === $property->getPropName()) {
                    continue;
                }
                $group = $property->getGroup() ?? StringUtil::$SP0;
                if( $prevGroup !== $group ) {
                    if( ! empty( $prevGroup )) {
                        $this->writer->endElement(); // end group
                        $prevGroup = null;
                    }
                    if( ! empty( $group )) {
                        $this->writer->startElement( self::XGROUP );  // start group
                        $this->writer->startAttribute( self::XNAME );
                        $this->writer->text( $group );
                        $this->writer->endAttribute();
                        $prevGroup = $group;
                    }
                } // end if
                $xcardPropertyFormatter->write( $property );
            } // end foreach properties
            if( ! empty( $prevGroup )) {
                $this->writer->endElement();
            }
            $this->writer->endElement(); // end xCard
        } // end forech
        $this->writer->endElement();
        return $this->writer->outputMemory();
    }
}
