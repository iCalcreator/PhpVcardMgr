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

use XMLWriter;

abstract class XcardFormatterBase implements FormatterInterface
{
    /**
     * XCard Properties : constants
     */
    public const XVCARDS        = 'vcards';
    public const XVCARDSAttr    = 'xmlns';
    public const XVCARDSAttrval = 'urn:ietf:params:xml:ns:vcard-4.0';
    public const XVCARD         = 'vcard';
    public const XGROUP         = 'group';
    public const XNAME          = 'name';
    public const XPARAMETERS    = 'parameters';

    /**
     * @var XMLWriter|null
     */
    protected $writer = null;

    /**
     * Constructor
     *
     * @param XMLWriter|null $writer
     */
    public function __construct( ? XMLWriter $writer = null )
    {
        if( null !== $writer ) {
            $this->writer = $writer;
        }
    }

    /**
     * Factory method
     *
     * @param null|XMLWriter $writer
     * @return static
     */
    public static function factory( ? XMLWriter $writer ) : XcardFormatterBase
    {
        return new static( $writer );
    }

    /**
     * @inheritDoc
     */
    abstract public function format( array $vCards ) : ? string;

    /**
     * Set writer start element, incl opt XML-attributes
     *
     * @param string    $elementName
     * @param mixed     $value
     */
    protected function writeTextElement( string $elementName, $value ) : void
    {
        $this->writer->startElement( $elementName );
        $this->writer->text((string) $value );
        $this->writer->endElement();
    }
}
