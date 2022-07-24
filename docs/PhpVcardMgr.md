[comment]: # (This file is part of PhpVcardMgr, the PHP class package managing Vcard/Xcard/Jcard information. Copyright 2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence GPL 3.0)

#### PhpVcardMgr details

* PhpVcardMgr implements [BaseInterface] with useful constants for propNames, valueTypes, parameter keys etc.
* For Vcard class details, explore [Vcard].

#### PhpVcardMgr Methods

###### PhpVcardMgr contruct Methods

```PhpVcardMgr::__construct()```
* Return _PhpVcardMgr_

```PhpVcardMgr::__factory()```
* Return _PhpVcardMgr_

###### PhpVcardMgr format methods

```PhpVcardMgr::vCard3Format()```
* Format (internal) Vcards into Vcard 3.0 string
* Return _string_

```PhpVcardMgr::vCard4Format()```
* Format (internal) Vcards into Vcard 4.0 string
* Return _string_

```PhpVcardMgr::jCardFormat()```
* Format (internal) Vcards into Jcard string
* Return _string_

```PhpVcardMgr::xCardFormat()```
* Format (internal) Vcards into Xcard XML string
* Return _string_

###### PhpVcardMgr parse methods

```PhpVcardMgr::vCard3Parse( inputString )```
* Parse inputString into (internal) Vcards 
* `inputString` _string_, in Vcard 3.0 format 
* Return _static_
* Throws _InvalidArgumentException_

```PhpVcardMgr::vCard4Parse( inputString )```
* Parse inputString into (internal) Vcards
* `inputString` _string_, in Vcard 4.0 format
* Return _static_
* Throws _InvalidArgumentException_

```PhpVcardMgr::jCardParse( inputString )```
* Parse inputString into (internal) Vcards
* `inputString` _string_, in Jcard format
* Return _static_
* Throws _InvalidArgumentException_, _RuntimeException_

```PhpVcardMgr::xCardParse( inputString )```
* Parse inputString into (internal) Vcards
* `inputString` _string_, in Xcard XML format
* Return _static_
* Throws _InvalidArgumentException_, _RuntimeException_

###### PhpVcardMgr misc. method

```isVcardString( vcardString [, version ] )```
* Return bool true if string is vCard and has version 4.0 (3.0)
* _static_
* `inputString` _string_
* `version` _string_, only _4.0_ (default) or _3.0_ allowed
* Return _bool_

###### PhpVcardMgr property getter and setter methods

```PhpVcardMgr::getVCards()```
* Return _Vcard[]_

```PhpVcardMgr::addVCard( vCard )```
* `vCard` _Vcard_
* Return _static_


```PhpVcardMgr::setVCards( vCards )```
* `vCards` _Vcard[]_
* Return _static_

---

<small>Return to [README] / go to [Vcard] / go to [properties]</small>

[BaseInterface]:../src/BaseInterface.php
[properties]:Properties.md
[README]:../README.md
[Vcard]:Vcard.md
