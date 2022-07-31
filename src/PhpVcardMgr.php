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
namespace Kigkonsult\PhpVcardMgr;

use Exception;
use Kigkonsult\PhpVcardMgr\Formatter\FormatterInterface;
use Kigkonsult\PhpVcardMgr\Formatter\JcardFormatter;
use Kigkonsult\PhpVcardMgr\Formatter\Vcard3Formatter;
use Kigkonsult\PhpVcardMgr\Formatter\Vcard4Formatter;
use Kigkonsult\PhpVcardMgr\Formatter\XcardFormatter;
use Kigkonsult\PhpVcardMgr\Parser\JcardParser;
use Kigkonsult\PhpVcardMgr\Parser\Vcard3Parser;
use Kigkonsult\PhpVcardMgr\Parser\Vcard4Parser;
use Kigkonsult\PhpVcardMgr\Parser\ParserInterface;
use Kigkonsult\PhpVcardMgr\Parser\XcardParser;

final class PhpVcardMgr implements BaseInterface
{
    /**
     * Constant
     */
     public const PVM_VERSION ='PhpVcardMgr 1.0.7';

    /**
     * @var Vcard[]
     */
    private $vCards = [];

    /**
     * Class constructor
     *
     * @param null|Vcard $vCard
     */
    public function __construct( ? Vcard $vCard = null ) {
        if( $vCard !== null ) {
            $this->addVCard( $vCard );
        }
    }

    /**
     * Class factory method
     *
     * @param null|Vcard $vCard
     * @return PhpVcardMgr
     */
    public static function factory( ? Vcard $vCard = null ) : PhpVcardMgr
    {
        return new self( $vCard );
    }

    /**
     * Formats vCards dep. on formatter, default Vcard4, return formatted string
     *
     * @param null|FormatterInterface $formatter  default Vcard4Formatter
     * @return string
     */
    public function format( ? FormatterInterface $formatter = null ) : string
    {
        if( null === $formatter ) {
            $formatter = new Vcard4Formatter();
        }
        return $formatter->format( $this->getVCards());
    }

    /**
     * @return string
     */
    public function vCard3Format() : string
    {
        return $this->format( new Vcard3Formatter());
    }

    /**
     * @return string
     */
    public function vCard4Format() : string
    {
        return $this->format( new Vcard4Formatter());
    }

    /**
     * @return string
     */
    public function jCardFormat() : string
    {
        return $this->format( new JcardFormatter());
    }

    /**
     * @return string
     */
    public function xCardFormat() : string
    {
        return $this->format( new XcardFormatter());
    }

    /**
     * Return bool true if vCard string has version 4.0 (default or 3.0)
     *
     * @param string $vcardString
     * @param null|string $version default '4.0'
     * @return bool
     */
    public static function isVcardString( string $vcardString, ? string $version = self::VERSION4 ) : bool
    {
        static $VERSIONS = [ self::VERSION4, self::VERSION3 ];
        return (( false !== strpos( $vcardString, self::BEGIN_VCARD )) &&
            ( false !== ( $pos = strpos( $vcardString, self::VERSION ))) &&
            in_array( $version, $VERSIONS, true ) &&
            ( substr( $vcardString, ( $pos + 8 ), 3 ) === $version ));
    }

    /**
     * Parse input string into Vcards, default from vCard4 string
     *
     * @param string $inputString
     * @param null|ParserInterface $parser default Vcard4Parser
     * @return PhpVcardMgr
     * @throws Exception
     */
    public function parse( string $inputString, ? ParserInterface $parser = null ) : PhpVcardMgr
    {
        if( null === $parser ) {
            $parser = new Vcard4Parser();
        }
        $this->vCards = $parser->parse( $inputString );
        return $this;
    }

    /**
     * @param string $vcardString
     * @return $this
     * @throws Exception
     */
    public function vCard3Parse( string $vcardString ) : PhpVcardMgr
    {
        return $this->parse( $vcardString, new Vcard3Parser());
    }
    /**
     * @param string $vcardString
     * @return $this
     * @throws Exception
     */

    public function vCard4Parse( string $vcardString ) : PhpVcardMgr
    {
        return $this->parse( $vcardString, new Vcard4Parser());
    }

    /**
     * @param string $jsonString
     * @return $this
     * @throws Exception
     */
    public function jcardParse( string $jsonString ) : PhpVcardMgr
    {
        return $this->parse( $jsonString, new JcardParser());
    }

    /**
     * @param string $xcardString
     * @return $this
     * @throws Exception
     */
    public function xCardParse( string $xcardString ) : PhpVcardMgr
    {
        return $this->parse( $xcardString, new XcardParser());
    }

    /**
     * Get- and setters
     */

    /**
     * @return Vcard[]
     */
    public function getVCards() : array
    {
        return $this->vCards;
    }

    /**
     * @param Vcard $vCard
     * @return PhpVcardMgr
     */
    public function addVCard( Vcard $vCard ) : PhpVcardMgr
    {
        $this->vCards[] = $vCard;
        return $this;
    }

    /**
     * @param Vcard[] $vCards
     * @return PhpVcardMgr
     */
    public function setVCards( array $vCards ) : PhpVcardMgr
    {
        foreach( $vCards as $vCard ) {
            $this->addVCard( $vCard );
        }
        return $this;
    }
}
