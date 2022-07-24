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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\PhpVcardMgr\Util\DateUtil;

abstract class PropertyDate extends PropertyBase
{
    /**
     * @inheritDoc
     */
    public static function isAnyParameterAllowed() : bool
    {
        return true;
    }

    /**
     * @override
     * @param string|DateTime $value Ymd-string or DateTime
     * @return static
     * @throws InvalidArgumentException
     */
    public function setValue( $value ) : PropertyInterface
    {
        if( $value instanceof DateTime ) {
            $offset      = $value->format( self::OFFSETfmt );
            $this->value = $value->format( self::DATETIMEfmt) . ( empty( $offset ) ? self::Zfmt : $offset );
            return $this;
        }
        $valueType = $this->getValueType() ?? self::getAcceptedValueTypes( true );
        switch( $valueType ) {
            case self::DATEANDORTIME :
                DateUtil::assertVcardDateAndOrTime( $this->getPropName(), $value );
                break;
            case self::DATETIME :
                DateUtil::assertVcardDateTime( $this->getPropName(), $value );
                break;
            case self::DATE :
                DateUtil::assertVcardDate( $this->getPropName(), $value );
                break;
            case self::TIME :
                DateUtil::assertVcardTime( $this->getPropName(), $value );
                break;
            // for valueType TEXT: accept as-is
        } // end switch
        $this->value = $value;
        return $this;
    }

    /**
     * @override
     */
    public static function getAcceptedValueTypes( ? bool $default = false )
    {
        return $default
            ? self::DATEANDORTIME
            : [ self::DATEANDORTIME, self::DATETIME, self::DATE, self::TIME, self::TEXT ];
    }
}
