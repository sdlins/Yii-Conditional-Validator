/* USING MARKDOWN SYNTAX */


##General Information

Yii Conditional Validator validates any number of attributes if certain attribute validation is true.  

You can use any Yii Core Validator like usually you would do or any other Class Based/Inline validator. You can even use **CASCADE** validation using Yii Conditional Validator itself;


##How it works
YCV executes the `baseValidator` on a attribute and, if it validates (baseValidator returns true), YCV executes the `dependentAttributesAndValidators` specified in the rules options for that attribute.


##Syntax

Short reference:
~~~
[php]
array(
    'attribName', 
    'path.to.YCV',
    'baseValidator', 
    'baseValidatorParams', //array
    'dependentAttributesAndValidators', //array
)
~~~  
  

Detailed reference:
~~~
[php]
public function rules()
{
    return array(
        ...,
        array(
            'attribute', //to be validated using 'baseValidator'
            'application.extensions.YiiConditionalValidator',
            'baseValidator'=>'compare', //any validator reference
            'baseValidatorParams'=>array(...), //params to 'baseValidator'
            'dependentAttributesAndValidators'=>array(
                'someAttribute'=>array(
                    'validator'=>'required', //any validator reference
                    'params'=>array(...), //params to pass to 'validator'
                ),
            ),
        ),
        ...
    )
}
~~~


##Examples

**birthdate** and **city** are `required` only if **type of customer** is `TYPE_X`:
~~~
[php]
public function rules()
{
    return array(
        ...,
        array('type_of_customer', 'application.extensions.YiiConditionalValidator',
            'baseValidator'=>'compare',
            'baseValidatorParams'=>array('compareValue'=>TYPE_X),
            'dependentAttributesAndValidators'=>array(
                'birthdate, city'=>array('validator'=>'required')
            ),
        ),
~~~

**birthdate** and **city** are `required` and **birthdate** must be a valid date only if *type_of_customer* is specified:
~~~
[php]
public function rules()
{
    return array(
      ...,
      array('type_of_customer',
        'application.extensions.YiiConditionalValidator',
        'baseValidator'=>'required',
        'dependentAttributesAndValidators'=>array(
            'birthdate, city'=>array(
                'validator'=>'required',
                /* params to required validator */
                'params'=>array(
                    //using placeholders
                    'message'=>'{dependentAttribute} is required if the {attribute} specified is {value}.',
                ),
            ),
            'birthdate'=>array(
                'validator'=>'date', //since Yii 1.1.7
                'params'=>array(...)
            ),
        ),
    ),
~~~

**info_attribute** must be a valid url only if *it itself* starts with 'http://' or must be a valid e-mail only if *it itself* contains an '@':
~~~
[php]
public function rules()
{
    return array(
    ...,
        array(
            'info_attribute', 
            'application.extensions.YiiConditionalValidator',
            'baseValidator'=>'match',
            'baseValidatorParams'=>array('pattern'=>'/^http:\/\//'),
            'dependentAttributesAndValidators'=>array(
                'info_attribute'=>array('validator'=>'url'),
            ),
        ),
        array(
            'info_attribute',
            'application.extensions.YiiConditionalValidator',
            'baseValidator'=>'match',
            'baseValidatorParams'=>array('pattern'=>'/@/'),
            'dependentAttributesAndValidators'=>array(
                'info_attribute'=>array('validator'=>'email'),
            ),
        ),
~~~

You can use any validator you want, mixing them to compound your rules.

##Placeholders for error messages
- `{attribute}`: the attribute label being checked;
- `{value}`: the value of the attribute being checked;
- `{dependentAttribute}`: the dependent attribute label being checked;
- `{dependentValue}`: the value of the dependent attribute being checked;


##Validation using related data (coming soon)

Some days more and it will be available a new option to allow use dot-notation on a validator attribute to check any data from a related model and use them in your validation rules.  

Hypotethical example:  

When creating a `Customer`, assuming that a business is selected, one might need check the `customer.business.city` (assuming `business` is the relation name in `Customer`) to validate if the customer.city is equal to business.city. 


##Installation

1. Put YiiConditionalValidator.php in your application.extensions folder;


##Requirements
- Tested in Yii 1.1.7. Should work ok with earlier versions;


##Resources
- [Forum Discussion](http://www.yiiframework.com/forum/index.php?/topic/27930-extension-yii-conditional-validator/)
- [Validators Wiki Reference](http://www.yiiframework.com/wiki/56/reference-model-rules-validation/)

##Change Log
- Version 0.1.1
    - Throwns exception when a dependent validator is not specified;
    - Fixes bug when params of a dependent validator is not specified;
    - Fixes bug concerning to backup/recover of errors of the model;
- Version 0.1
    First Version;
