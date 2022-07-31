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

use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Gender;
use Kigkonsult\PhpVcardMgr\Property\N;
use Kigkonsult\PhpVcardMgr\Property\PropertyInterface;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;

class XcardPropertyFormatter extends XcardFormatterBase
{
    /**
     * @var string
     */
    private static $INTEGER = 'integer';

    /**
     * Write Vcard property to XML
     *
     * @param PropertyInterface $property
     * @return void
     */
    public function write( PropertyInterface $property) : void
    {
        $propName = $property->getPropName();
        $this->writer->startElement( strtolower( $propName ));
        $this->writeParameters( $property->getParameters());
        switch( $propName ) {
            case self::ADR :
                $this->writeAdr( $property );
                break;
            case self::CATEGORIES : // fall through
            case self::NICKNAME :   // fall through
            case self::ORG :
                $this->writeListValue( $property );
                break;
            case self::CLIENTPIDMAP :
                $this->writeClientPidMap( $property );
                break;
            case self::GENDER :
                $this->writeGender( $property );
                break;
            case self::N :
                $this->writeN( $property );
                break;
            case self::XML :
                /*
                $this->writer->startElement( strtolower( $property->getValueType()));
                $this->writer->writeRaw( $property->getValue());
                $this->writer->endElement();
                */
                $this->writeTextElement(
                    strtolower( $property->getValueType()),
                    htmlspecialchars( $property->getValue())
                );
                break;
            default :
                $this->writeTextElement( strtolower( $property->getValueType()), $property->getValue());
                break;
        } // end switch
        $this->writer->endElement();
    }

    /**
     * Write Adr
     *
     * @param PropertyInterface $property
     */
    private function writeAdr( PropertyInterface $property ) : void
    {
        $this->writeAdrN( $property, Adr::$valueComponents );
    }

    /**
     * Write N
     * @param PropertyInterface $property
     */
    private function writeN( PropertyInterface $property ) : void
    {
        $this->writeAdrN( $property, N::$valueComponents );
    }

    /**
     * Write Adr and N
     *
     * @param PropertyInterface $property
     * @param array $valueKeys
     */
    private function writeAdrN( PropertyInterface $property, array $valueKeys ) : void
    {
        foreach( $property->getValue() as $vix => $valuePart ) {
            if( empty( $valuePart )) {
                $this->writeTextElement( $valueKeys[$vix], StringUtil::$SP0 );
                continue;
            }
            foreach( explode( StringUtil::$COMMA, $valuePart ) as $valuePart3 ) {
                $this->writeTextElement( $valueKeys[$vix], trim( $valuePart3 ));
            }
        }
    }

    /**
     * Write ClientPidMap
     *
     * @param PropertyInterface $property
     */
    private function writeClientPidMap( PropertyInterface $property ) : void
    {
        $value = $property->getValue();
        $this->writeTextElement( self::$INTEGER, (string) $value[0] );
        $this->writeTextElement( strtolower( $property->getValueType()), $value[1] );
    }

    /**
     * Write Gender
     * @param PropertyInterface $property
     */
    private function writeGender( PropertyInterface $property ) : void
    {
        $value = $property->getValue();
        $this->writeTextElement( Gender::$valueComponents[0], ( $value[0] ?? StringUtil::$SP0 ));
        if( isset( $value[1] )) {
            $this->writeTextElement( Gender::$valueComponents[1], $value[1] );
        }
    }

    /**
     * Write list value
     *
     * @param PropertyInterface $property
     */
    private function writeListValue( PropertyInterface $property ) : void
    {
        $valueType = strtolower( $property->getValueType());
        foreach( $property->getValue() as $value ) {
            $this->writeTextElement( $valueType, $value );
        }
    }

    /**
     * Write parameters
     *
     * The pkey types MUST exist in XcardPropertyParser::parseParameters
     *
     * @param array $parameters
     */
    private function writeParameters( array $parameters ) : void
    {
        unset( $parameters[self::VALUE] );
        if( empty( $parameters )) {
            return;
        }
        $this->writer->startElement( self::XPARAMETERS );
        foreach( $parameters as $pKey => $pValue ) {
            $this->writer->startElement( strtolower( $pKey ));
            switch( $pKey ) {
                case self::GEO :
                    $valueType = self::URI;
                    break;
                case self::LANGUAGE :
                    $valueType = self::LANGUAGETAG;
                    break;
                case self::PREF :
                    $valueType = self::$INTEGER;
                    break;
                case self::SORT_AS :
                    if(( false !== strpos( $pValue, StringUtil::$COMMA ))) {
                        $pValue = explode( StringUtil::$COMMA, $pValue );
                    }
                    $valueType = self::TEXT;
                    break;
                case self::LABEL :
                    if(( false !== strpos( $pValue, StringUtil::$STREOL ))) {
                        $pValue = str_replace( StringUtil::$STREOL, StringUtil::$NEWLINE, $pValue );
                    } // fall through
                default :
                    $valueType = self::TEXT;
            }
            foreach((array) $pValue as $pValue2 ) {
                $this->writeTextElement( $valueType, (string) $pValue2 );
            }
            $this->writer->endElement();
        } // end foreach
        $this->writer->endElement();
    }
}
