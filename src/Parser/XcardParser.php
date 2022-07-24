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

use DOMNode;
use Exception;
use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Util\StringUtil;
use Kigkonsult\PhpVcardMgr\Vcard;
use RuntimeException;
use XMLReader;

class XcardParser extends XcardParserBase
{
    /**
     * Parse xml-string
     *
     * @inheritDoc
     * @param string $source  XML
     * @param bool|null $asDomNode
     * @return Vcard[]|DOMNode
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function parse( string $source, ? bool $asDomNode = null )
    {
        static $FMTerr1 = 'Error #%d parsing xml';
        static $FMTerr3 = 'No xml root element found';
        $useInternalXmlErrors = libxml_use_internal_errors( true ); // enable user error handling
        if( false === ( $this->reader = XMLReader::XML( $source, null, self::$XMLReaderOptions ) ) ) {
            throw new InvalidArgumentException( sprintf( $FMTerr1, 1 ) );
        }
        $vCards = [];
        while( @$this->reader->read()) {
            if(( XMLReader::ELEMENT === $this->reader->nodeType ) &&
                ( self::XVCARDS === $this->reader->localName )) {
                    if( $asDomNode ) {
                        return $this->reader->expand();
                    }
                    while( @$this->reader->read()) {
                        if(( XMLReader::ELEMENT === $this->reader->nodeType ) &&
                            ( self::XVCARD === $this->reader->localName )) {
                            $vCards[] = $this->vcardParse();
                        }
                    } // end while 2
            } // end if
        } // end while 1
        $libxmlErrors = libxml_get_errors();
        libxml_use_internal_errors( $useInternalXmlErrors ); // disable user error handling
        libxml_clear_errors();
        $libXarr = self::renderXmlError( $libxmlErrors, null, $source );
        if( ( 0 < count( $libXarr ) ) && self::checkLibxmlErrors( $libXarr )) {
            throw new RuntimeException( sprintf( $FMTerr1, 2 ) .
                PHP_EOL . var_export( $libXarr, true ));
        }
        $this->reader->close();
        if( empty( $vCards ) ) {
            throw new RuntimeException( $FMTerr3 );
        }
        return $vCards;
    }

    /**
     * Parse single XML Vcard
     *
     * @return Vcard
     */
    private function vcardParse() : Vcard
    {
        static $skipProps = [ self::PRODID, self::VERSION ];
        $vCard = new Vcard();
        $group = $propName = null;
        $xcardPropertyParser = XcardPropertyParser::factory( $this->reader );
        while( @$this->reader->read()) {
            if( XMLReader::END_ELEMENT === $this->reader->nodeType ) {
                if( self::XVCARD === $this->reader->localName ) {
                    break;
                }
                if(( self::XGROUP === $this->reader->localName ) && ! empty( $group )) {
                    $group = null;
                }
                continue;
            } // end if
            if( XMLReader::ELEMENT !== $this->reader->nodeType ) {
                continue;
            }
            if( in_array( $propName, $skipProps, true )) {
                $propName = null;
                continue;
            }
            if( self::XGROUP === $this->reader->localName ) {
                if( $this->reader->hasAttributes ) {
                    while( $this->reader->moveToNextAttribute()) {
                        if( self::XNAME === $this->reader->localName ) {
                            $group = $this->reader->value;
                            $this->reader->moveToElement();
                            continue 2;
                        }
                    }
                } // end while
                continue; // but group are expected to have attribute 'name'
            } // end if
            $propName = strtoupper( $this->reader->localName );
            if( in_array( $propName, $skipProps, true )) {
                continue;
            }
            $property = $xcardPropertyParser->parse( $propName );
            if( ! empty( $group )) {
                $property->setGroup( $group );
            }
            $vCard->addProperty( $property );
        }
        return $vCard;
    }

    /**
     * @var int
     *           libxml default options
     *             LIBXML_NONET          Disable network access when loading documents
     *             LIBXML_NOERROR        Suppress error reports
     *             LIBXML_NOWARNING      Suppress warning reports
     *             LIBXML_NSCLEAN        Remove redundant namespace declarations
     *             LIBXML_HTML_NODEFDTD  Sets HTML_PARSE_NODEFDTD flag, which prevents a default doctype being added when one is not found. ??
     */
    public static $XMLReaderOptions = LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NSCLEAN | LIBXML_HTML_NODEFDTD;
    // LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NSCLEAN;

    /**
     * Log libxml error
     *
     * @param array $libXarr
     * @return bool           true on critical
     */
    private static function checkLibxmlErrors( array $libXarr ) : bool
    {
        static $CRITICAL = 'critical';
        foreach( $libXarr as $errorSets ) {
            foreach( $errorSets as $logLevel => $msg ) {
                if( $CRITICAL === $logLevel ) {
                    return true;
                }
            } // end foreach
        } // end foreach
        return false;
    }

    /*
     * Return rendered (array) XML error
     *
     * @param array $errors   array of libxml error object
     * @param null|string $fileName
     * @param null|string $content
     * @return array   [ *(logLevel => msg)]
     * @see http://php.net/manual/en/function.libxml-get-errors.php
     */
    private static function renderXmlError(
        array $errors,
        ? string $fileName = null,
        ? string $content = null
    ) : array
    {
        static $CRITICAL = 'critical';
        static $WARNING  = 'warning';
        static $INFO     = 'info';
        static $FMT0     = ' No XML to parse';
        static $FMT1     = ' %s #%d, errCode %s : %s';
        static $FMT2     = ' line: %d col: %d';
        static $FMT3     = '%s%s%s%s^%s';
        static $D        = '-';
        static $LIBXML_Warning           = 'LIBXML Warning';
        static $LIBXML_recoverable_Error = 'LIBXML (recoverable) Error';
        static $LIBXML_Fatal_Error       = 'LIBXML Fatal Error';
        if( empty( $errors )) {
            return [];
        }
        if( empty( $content )) {
            if( empty( $fileName )) {
                return [ $CRITICAL => $FMT0 ];
            }
            $content = empty( $fileName ) ? StringUtil::$SP0 : (string) @file_get_contents( $fileName );
        }
        $xml     = empty( $content ) ? false : explode( PHP_EOL, $content );
        $libXarr = [];
        $baseFileName = empty( $fileName ) ? StringUtil::$SP0 : basename( $fileName );
        foreach( $errors as $ex => $error ) {
            $str1   = sprintf(
                $FMT1, $baseFileName, ( $ex + 1 ), $error->code, trim( $error->message )
            );
            $str2   = sprintf( $FMT2, $error->line, $error->column );
            if( false !== $xml ) {
                $lineNo = ( 0 < $error->line ) ? ( $error->line - 1 ) : 0;
                $str2  .= sprintf(
                    $FMT3, PHP_EOL, $xml[$lineNo], PHP_EOL, str_repeat( $D, $error->column ), PHP_EOL
                );
            }
            switch( $error->level ) {
                case LIBXML_ERR_WARNING:    // 1
                    $str3     = $LIBXML_Warning;
                    $logLevel = $WARNING;
                    break;
                case LIBXML_ERR_ERROR:      // 2
                    $str3     = $LIBXML_recoverable_Error;
                    $logLevel = ( 522 === $error->code ) ? $INFO : $WARNING; // Validation failed: no DTD found !
                    break;
                case LIBXML_ERR_FATAL:      // 3
                default :
                    $str3     = $LIBXML_Fatal_Error;
                    $logLevel = $CRITICAL;
                    break;
            } // end switch
            $libXarr[$ex][$logLevel] = $str3 . $str1;
            $libXarr[$ex][$INFO]     = $str3 . $str2;
        }  // end foreach
        return $libXarr;
    }
}
