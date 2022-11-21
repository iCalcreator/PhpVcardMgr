
# PhpVcardMgr

is the PHP class package managing

> Vcard / Xcard / Jcard information

###### supporting
* vCard Format Specification, 4.0, [rfc6350], format / parse
  
* xCard: vCard XML Representation, [rfc6351], format / parse

* jCard: The JSON Format for vCard, 4.0, [rfc7095], format / parse

* vCard MIME Directory Profile, Vcard 3.0, [rfc2426]
   * format Vcard 3.0 from a 4.0 structure
   * parse Vcard 3.0 into a 4.0 structure

###### provides
* access to a Vcard / Property 4.0 class structure for arbitrary use, more info in [PhpVcardMgr], [Vcard], or [Properties] docs 

#### Usage

Parse an input string into vCards :

```php
<?php
namspace Kigkonsult\PhpVcardMgr;

// load an input string
$inputString = ....

// parse Vcard 4.0 input string
$vCards = PhpVcardMgr::factory()->vCard4Parse( $inputString )->getVCards();

// parse Vcard 3.0 input string
// $vCards = PhpVcardMgr::factory()->vCard3Parse( $inputString )->getVCards();

// parse Jcard json input string
// $vCards = PhpVcardMgr::factory()->jCardParse( $inputString )->getVCards();

// parse Xcard XML input string
// $vCards = PhpVcardMgr::factory()->xCardParse( $inputString )->getVCards();

// examine each vcard content
foreach( $vCards as $vCard) {
    if( $vCard->hasProperty( PhpVcardMgr::N )) {
        // Exactly one instance per vCard MAY be present
        $property = $vCard->getProperty( PhpVcardMgr::N );
        $name       = $property->isGroupSet()     // string
            ? $property->getGroupPropName()
            : $property->getPropName();
        $parameters = $property->getParameters(); // array
        $valueType  = $property->getValueType();  // string
        // five-element array : surname/given/additional/prefix/suffix
        $value      = $property->getValue();
            ...
    } // end if
    if( $vCard->hasProperty( PhpVcardMgr::ADR )) {
         // One or more instances per vCard MAY be present
        foreach( $vCard->getProperty( PhpVcardMgr::ADR ) as $property ) {
            $name       = $property->isGroupSet()     // string
                ? $property->getGroupPropName()
                : $property->getPropName();
            $parameters = $property->getParameters(); // array
            $valueType  = $property->getValueType();  // string
            // seven-element array : pobox/ext/street/locality/region/code/country
            $value      = $property->getValue();
            ...
        } // end foreach
    } // end if
    ...
} // end foreach

```

Format vCards into Vcard / Jcard / Xcard string :

```php
<?php
namspace Kigkonsult\PhpVcardMgr;

use Kigkonsult\PhpVcardMgr\Property\Adr;
use Kigkonsult\PhpVcardMgr\Property\Email;
use Kigkonsult\PhpVcardMgr\Property\N;

// load a Vcard
$vCard = Vcard::factory()
   ->addProperty( Adr::factory( <value> [, <parameters> [, <valueType> [, <group> ]]] )
   ->addProperty( N::factory( <value> [, <parameters> [, <valueType> [, <group> ]]] )
   ->addProperty( Email::factory( <value> [, <parameters> [, <valueType> [, <group> ]]] );

$phpVcardMgr = PhpVcardMgr::factory()->addVCard( $vCard );

// format Vcard 4.0 output string
$outputString = $phpVcardMgr->vCard4Format();

// format Vcard 3.0 output string
// $outputString = $phpVcardMgr->vCard3Format();

// format Jcard json output string
// $outputString = $phpVcardMgr->jCardFormat();

// format Xcard XML output string
// $outputString = $phpVcardMgr->xCardFormat();

```

For details, please explore Vcard4 [rfc6350], Vcard3 [rfc2426], Jcard [rfc7095], Xcard [rfc6351] or [PhpVcardMgr], [Vcard], or [Properties] docs.

To support the development, maintenance and test process
[PHPCompatibility], [PHPStan] and [php-arguments-detector] are included.

###### Support

For support use [github.com/PhpVcardMgr]. Non-emergence support issues are, unless sponsored, fixed in due time.


###### Sponsorship

Donation using [paypal.me/kigkonsult] are appreciated.
For invoice, please e-mail</a>.

###### Installation

Composer

From the Command Line:

```
composer require kigkonsult/phpvcardmgr
```

In your composer.json:

```
{
    "require": {
        "kigkonsult/phpvcardmgr": "*"
    }
}
```

###### License

PhpVcardMgr is licensed under the LGPLv3 License.

[github.com/PhpVcardMgr]:https://github.com/iCalcreator/phpvcardmgr/issues
[paypal.me/kigkonsult]:https://paypal.me/kigkonsult
[php-arguments-detector]:https://github.com/DeGraciaMathieu/php-arguments-detector
[PHPCompatibility]:https://github.com/PHPCompatibility/PHPCompatibility
[PHPStan]:https://github.com/phpstan/phpstan
[PhpVcardMgr]:docs/PhpVcardMgr.md
[Properties]:docs/Properties.md
[rfc2426]:https://www.rfc-editor.org/rfc/rfc2426.html
[rfc6350]:https://www.rfc-editor.org/rfc/rfc6350.html
[rfc6351]:https://www.rfc-editor.org/rfc/rfc6351.html
[rfc7095]:https://www.rfc-editor.org/rfc/rfc7095.html
[Vcard]:docs/Vcard.md

[comment]: # (This file is part of PhpVcardMgr, the PHP class package managing Vcard/Xcard/Jcard information. Copyright 2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence GPL 3.0)
