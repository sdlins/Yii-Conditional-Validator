/* USING MARKDOWN SYNTAX */

##General Information

Yii Conditional Validator validates any number of attributes if certain attribute validation is true.  

You can use any Yii Core Validator like usually you would do or any other Class Based/Inline validator. You can even use **CASCADE** validation using Yii Conditional Validator itself;


##How it works
YCV executes the `validation` on a attribute and, if there are no validation errors, YCV executes the `dependentValidations` specified in the rules options for that attribute.

> NOTE:
This version (0.2.0) is **NOT** compatible with earlier versions!

##Syntax

~~~
[php]
array('attribName', 'path.to.YCV',
    'validation', //array, optional
    'dependentValidations', //array
)
~~~  
  
- `attribName`: [string, required] The name of the conditional attribute;
- `path.to.YCV`: [string, required] The place where you unzipped the extension file. Defaults to `application.extensions.YiiConditionalValidator`;
- `validation`: [array, optional] The conditional validation to be applyed to `attribName` (eg. `array('compare', 'compareValue'=>123)`). Dependent validations will be applyed **ONLY** if this validation has no errors. Defaults to `array('required')`;
- `dependentValidations`: [array, required] The validations will be applyed if, and only if, `validation` has no errors (eg. `myAttrib=>array( array('required'), array('date', 'format'=>...) )`). You can define multiple attributes using `myAttrib1, myAttrib2, myAttribN...=>...` and/or you can define multiple validatons to those attributes (please, see examples);


##Examples

**birthdate** and **city** are `required` only if **type_of_customer** is `TYPE_X`:
~~~
[php]
public function rules()
{
    return array(
        ...,
        array('type_of_customer', 'application.extensions.YiiConditionalValidator',
            'validation'=>array('compare', 'compareValue'=>TYPE_X),
            'dependentValidations'=>array(
                'birthdate, city'=>array(
                	array('required'),
        		),
            ),
        ),
~~~

**birthdate and city** are `required` and **birthdate** must be a valid date only if **type_of_customer** is specified:
~~~
[php]
    array('type_of_customer', 'application.extensions.YiiConditionalValidator',
        'validation'=>array('required'), //optional for required validator
        'dependentValidations'=>array(
            'birthdate, city'=>array(
                array('required', 'message'=>'{dependentAttribute} is required if the {attribute} specified is {value}.'),
            ),
            'birthdate'=>array(
                array('date', 'format'=>...),
            ),
        ),
    ),
~~~

**info_attribute** must be a valid url only if **it itself** starts with 'http://' OR it must be a valid e-mail only if **it itself** contains an '@':
~~~
[php]
        array('info_attribute', 'application.extensions.YiiConditionalValidator',
            'validation'=>array('match', 'pattern'=>'/^http:\/\//'),
            'dependentValidations'=>array(
                'info_attribute'=>array(
                	array('url'),
            	),
            ),
        ),
        array('info_attribute', 'application.extensions.YiiConditionalValidator',
            'validation'=>array('match', 'pattern'=>'/@/'),
            'dependentValidations'=>array(
                'info_attribute'=>array(
                	array('email'),
            	),
            ),
        ),
~~~

You can use any validator you want, mixing them to compound your rules.


##Validation using related data

> Note:
This feature was not fully tested yet. Maybe not work on complex situations.

You can use dot.notation on a validator attribute to check any data from a related model and use them in your validation rules.  
  
**Example:**  

Assuming that `Customer` has a relation named `business`, you can check the `business.city` to define if `customer_phone` is required:
~~~
[php]
        //Customer rules
        array('business.city', 'application.extensions.YiiConditionalValidator',
            'validation'=>array('in', 'range'=>array(CITY_X, CITY_Y, ...)),
            'dependentValidations'=>array(
                'customer_phone'=>array(
                	array('required'),
            	),
            ),
        ),
~~~


##Placeholders for error messages
- `{attribute}`: the attribute label being checked;
- `{value}`: the value of the attribute being checked;
- `{dependentAttribute}`: the dependent attribute label being checked;
- `{dependentValue}`: the value of the dependent attribute being checked;


##Installation
1. Put YiiConditionalValidator.php in your application.extensions folder;


##Requirements
- Tested in Yii 1.1.7. Should work ok with earlier versions;


##Resources
- [Forum Discussion](http://www.yiiframework.com/forum/index.php?/topic/27930-extension-yii-conditional-validator/)
- [Validators Wiki Reference](http://www.yiiframework.com/wiki/56/reference-model-rules-validation/)

##Change Log
- Version 0.2.0
    - Usage made easier, more simplyfied and more objective;
    - New 'dot.notation' usage on attributes name (will be improoved on next versions);
    - Some bug fixes;
- Version 0.1.1
    - Throwns exception when a dependent validator is not specified;
    - Fixes bug when params of a dependent validator is not specified;
    - Fixes bug concerning to backup/recover of errors of the model;
- Version 0.1
    First Version;
