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

use XMLReader;

/**
 * Class XcardParserBase
 */
abstract class XcardParserBase implements ParserInterface
{
    /**
     * XCard Properties : constants
     */
    public const XVCARDS        = 'vcards';
    public const XVCARD         = 'vcard';
    public const XGROUP         = 'group';
    public const XNAME          = 'name';
    public const XTEXT          = 'text';
    public const XPARAMETERS    = 'parameters';

    /**
     * @var string
     */
    protected static $FMTattrFound = '%s Found attribute %s = %s';

    /**
     * NodeTypes, may be use in tests
     *
     * @var array $nodeTypes
    public static $nodeTypes = [
        0  => 'NONE',
        1  => 'ELEMENT',
        2  => 'ATTRIBUTE',
        3  => 'TEXT',
        4  => 'CDATA',
        5  => 'ENTITY_REF',
        6  => 'ENTITY',
        7  => 'PI',
        8  => 'COMMENT',
        9  => 'DOC',
        10 => 'DOC_TYPE',
        11 => 'DOC_FRAGMENT',
        12 => 'NOTATION',
        13 => 'WHITESPACE',
        14 => 'SIGNIFICANT_WHITESPACE',
        15 => 'END_ELEMENT',
        16 => 'END_ENTITY',
        17 => 'XML_DECLARATION',
    ];
     */

    /**
     * @var XMLReader|null
     */
    protected $reader = null;

    /**
     * Constructor
     *
     * @param null|XMLReader $reader
     */
    public function __construct( ? XMLReader $reader = null  )
    {
        if( $reader !== null ) {
            $this->reader = $reader;
        }
    }

    /**
     * Factory method
     *
     * @param null|XMLReader $reader
     * @return static
     */
    public static function factory( ? XMLReader $reader = null  )
    {
        return new static( $reader );
    }
}
