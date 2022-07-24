[comment]: # (This file is part of PhpVcardMgr, the PHP class package managing Vcard/Xcard/Jcard information. Copyright 2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence GPL 3.0)

#### Vcard 4.0 Property details

For base definition of Vcard 4.0 properties, please explore [rfc6350].

--- 
For Vcard class details, explore [Vcard].

The [rfc6350] Vcard properties are managed by
* _Adr_, _Anniversary_, _Bday_, _Caladruri_, _Caluri_, _Categories_, _Clientpidmap_, 
_Email_, _Fburl_, _FullName_ (manages the _Fn_ Vcard 4.0 property), _Gender_, _Geo_, 
_Impp_, _Key_, _Kind_, _Lang_, _Logo_, _Member_, _N_, _Nickname_, _Note_, 
_Org_, _Photo_, _Prodid_, _Related_, _Rev_, _Role_, _Sound_, _Source_, 
_Tel_, _Title_, _Tz_, _Uid_, _Url_, _Version_, _Xml_ and _Xprop_ (manages _X-_ prefixed Vcard 4.0 properties) classes.


* All implements [BaseInterface] with useful constants for propNames, valueTypes, parameter keys etc.


* _iana_ Vcard 4.0 properties are not accepted
* _x-named_ Vcard 4.0 properties (prefixed by _X-_) are accepted

--- 

* Exactly one instance per [Vcard] is allowed for _ANNIVERSARY_, _BDAY_, _GENDER_, _N_, _REV_, _UID_ or _X-_
  property classes, adding a second will replace the first
 
* Opt property comma-separated **value** lists are accepted (as-is),
  <br>but recommend to split **value** into two or more properties


* Property classes _VERSION_, _PRODID_, _UID_ (uuid) always auto set in [Vcard] 

* _ADR_ property class
    * accepts _value_ as seven-element array or SEMICOLON separated string

* _N_ property class
  * accepts _value_ as five-element array or SEMICOLON separated string
  
* _CLIENTPIDMAP_ property class
  * accepts _value_ as two-element array or SEMICOLON separated string
  
* _ANNIVERSARY_ property class
   * expects _valueType_ DATE-AND-OR-TIME (, DATE-TIME, DATE) OR TEXT,
   <br>accepts (value as) DateTime or date(-time) string
 
* _BDAY_ property class
  * expects _valueType_ DATE-AND-OR-TIME (, DATE-TIME, DATE) OR TEXT,
  <br>accepts (value as) DateTime or date(-time) string
 
* _REV_ property class
  * (_valueType_ TIMESTAMP) accepts _value_ (UTC) DateTime or date-time string


* using the Vcard3Formatter
  * Vcard 4.0 unique properties are renamed and formatted as X-property
  * X-properties are formatted as-is

* using the Vcard3Parser
  * Vcard 3.0 unique properties are parsed and renamed as X-property
  * X-prefixed Vcard 4.0 unique properties identified
  * X-properties are parsed as-is


* _iana_ and _x-named_ class property **parameter** keys (prefixed by _X-_) are mostly accepted

#### Property Methods

With `Property`, below, means any PHP property class, above. 
Some limitations on `Prodid` and `Version` classes.

###### Property contruct Methods


```Property::__construct( value [, parameters [, valueType [, group ]]] )```
* `value` _scalar_|_array_, depend on property type
* `parameters` _array_, depend on property type
* `valueType` _string_, depend on property type
  <br>if missing, using 1. parameter\[VALUE] or 2. [rfc6350] property default
  <br>`valueType` will override opt set parameter\[VALUE]
* `group` _string_
* Return _Property_
* Throws _InvalidArgumentException_

```Property::factory( value [, parameters [, valueType [, group ]]] )```
* Arguments same as for `__construct`, above
* Return _Property_

###### Property _parameters_ methods

```Property::getParameters( [ key ] ] )```
* Return all parameters as array or spec parameter key value (false if not found)
* `key` _string_
* return _bool_ | _string_ | _array_

```Property::hasParameter( key )```
* Return bool true if parameter key is set
* `key` _string_
* return _bool_

```Property::hasTypeParameter( [ typeValue ] )```
* Return bool true if parameter key _Type_ (opt with typeValue ) is set
* `typeValue` _string_
* return _bool_

```Property::hasValueParameter()```
* Return bool true if parameter key _Value_ is set
* return _bool_

```Property::isParametersSet()```
* Return bool true if any parameter is set
* return _bool_

```Property::addParameter( key, value )```
* Add parameter, if exists, replace.<br>For some properties, the TYPE parameter is (silently) skiped, otherwise forced into array
* `key` _string_
* `value` _scalar_
* return _Property_
* throws InvalidArgumentException

```Property::setParameters( parameters )```
* Set array of parameters
* `parameters` _array_  key/value pairs
* return _Property_
* throws InvalidArgumentException

```Property::unsetParameter( key )```
* Remove parameter (if set)
* `key` _string_
* return _Property_

###### Property _valueType_ methods

```Property::getValueType()```
* Return type of property value
* return _null_ | _string_

```Property::setValueType( valueType )```
* Set type of property value
* `key` _valueType_
* return _Property_
* throws InvalidArgumentException

###### Property _value_ methods

```Property::getValue()```
* Return property value
* return _null_ | _string_ | _array_

```Property::isValueSet()```
* Return bool true if value is set
* return _bool_

```Property::setValue( value )```
* Set property value
* `value` _mixed_
* return _Property_


###### Property _group_ methods

```Property::getGroup()```
* Return group value
* return _null_ | _string_

```Property::getGroupPropName()```
* Return group and name formatted as '_group_._name_'
* return string


```Property::isGroupSet()```
* Return bool true if group is set
* return _bool_

```Property::setgroup( value )```
* Set property group
* `group` _string_
* return _Property_

###### Property misc. methods

```Property::__toString()```
* Return nicely rendered property 
* return _string_

---

<small>Return to [README] / go to [PhpVcardMgr] / go to [Vcard]</small>

[BaseInterface]:../src/BaseInterface.php
[PhpVcardMgr]:PhpVcardMgr.md
[rfc6350]:https://www.rfc-editor.org/rfc/rfc6350.html
[README]:../README.md
[Vcard]:Vcard.md
