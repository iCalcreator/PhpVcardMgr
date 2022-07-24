[comment]: # (This file is part of PhpVcardMgr, the PHP class package managing Vcard/Xcard/Jcard information. Copyright 2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence GPL 3.0)

## Vcard details

For base definition of Vcard and properties, please explore [rfc6350].

* Vcard implements [BaseInterface] with useful constants for propNames, valueTypes, par meter keys etc.
* For Vcard properties details, explore [Properties].

#### Vcard Methods


###### Vcard construct Methods

```Vcard::__construct()```
* Properties _VERSION_, _PRODID_, _UID_ always auto set
* Return _Vcard_

```Vcard::__factory()```
* Return _Vcard_

###### Vcard _property_ methods

```Vcard::count( [ propName ] )```
* Return count properties, opt propNamed ones (if found, otherwise false)
* `propName` string
* return bool | int

```Vcard::getProperties( [ propName [, valuesOnly [, typeValue ]]] )```
* Return mixed | array, all properties or spec propNamed ones, opt only property values
* propName _ANNIVERSARY_, _BDAY_, _GENDER_, _N_, _PRODID_, _REV_, _UID_, _VERSION_ or _X-_ prop 
  returns _mixed_ | _PropertyInterface_, others _array_ | _PropertyInterface[]_
 <br>propname _X-_ returns all X-props, NOT found propName, empty array 
* `propName` _string_
* `valuesOnly` _bool_, default false
* `typeValue` _string_, only properties with TYPE=typeValue returned
* Return _mixed_ | _PropertyInterface_ | _PropertyInterface[]_

```Vcard::hasProperty( propName [, typeValue ] )```
* Return bool false if propName property \[with type] not exists, otherwise count of propNamed \[with type] properties
* `propName` _string_
* `typeValue` _string_
* Return _bool_ | _int_

```Vcard::removeProperty( [ propName ] )```
* Remove all properties or spec propNamed ones
* `propName` _string_
* Return _Vcard_

```Vcard::replaceProperty( property )```
* Replace property/properties,<br>single property input will replace ALL opt existing ones
* `property` _PropertyInterface_ | _PropertyInterface[]_
* Return _Vcard_

```Vcard::addProperty( property )```
* Add property to collection, last in chain
* Exactly one instance per vCard is allowed for _ANNIVERSARY_, _BDAY_, _GENDER_, _N_, _REV_, _UID_ or _X-_ props,
  adding a second will replace the first
* `property` _PropertyInterface_
* Return _Vcard_

```Vcard::setProperties( properties )```
* Add properties to collection
* `properties` _PropertyInterface[]_
* Return _Vcard_

---

<small>Return to [README] / go to [PhpVcardMgr] / go to [Properties]</small>

[BaseInterface]:../src/BaseInterface.php
[PhpVcardMgr]:PhpVcardMgr.md
[Properties]:Properties.md
[rfc6350]:https://www.rfc-editor.org/rfc/rfc6350.html
[README]:../README.md
