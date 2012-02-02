<?php
/**
 * Validates multiple attributes using any Yii Core Validator
 * depending on some another attribute's condition (validation) is true;
 *
 * @author Sidney Lins <solucoes@wmaior.com>
 * @copyright Copyright &copy; 2011 Sidney Lins
 * @version 0.1
 * @license New BSD Licence
 */
class YiiConditionalValidator extends CValidator{
    public $baseValidator='required';
    public $baseValidatorParams=array();
    public $dependentAttributesAndValidators=array();

    public $message = "{dependentAttribute} is not valid.";

    protected function validateAttribute($object, $attribute)
    {
        /* @var $object CActiveRecord */

        $baseErrorsBackup=$object->getErrors();
        $object->clearErrors();

        $relAttribute=$attribute;
        $relObject=$object;

        if(strpos($attribute, '.')!==false)
        {
            $parts=explode('.', $attribute);
            $relAttribute=array_pop($parts);
            $relations=$parts;
            foreach($relations as $relation)
                $relObject=$relObject->getRelated($relation);
        }

        $baseValidator=CValidator::createValidator($this->baseValidator, $relObject, $relAttribute, $this->baseValidatorParams);
        $baseValidator->validate($relObject, $relAttribute);

        if($relObject->hasErrors($relAttribute))
        {
            $object->clearErrors();
            $object->addErrors($baseErrorsBackup);
            return false;
        }

        foreach($this->dependentAttributesAndValidators as $currentAttribute=>$currentValidatorData)
            $this->validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $currentAttribute, $currentValidatorData);

        $object->addErrors($baseErrorsBackup);
    }

    protected function validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $dependentAttribute, $validatorData) {
        if(strpos($dependentAttribute, ',') !== false)
        {
            $attributes=array_map('trim', explode(',', $dependentAttribute));
            foreach($attributes as $dependentAttribute)
                $this->validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $dependentAttribute, $validatorData);
            return true;
        }

        if(!isset($validatorData['validator']))
            throw new CException('Yii Conditional Validator: you must specify a validator to each dependent attribute.');

        $validatorName   = $validatorData['validator'];
        $validatorParams = isset($validatorData['params']) ? $validatorData['params'] : array();

        $errorsBackup = $object->getErrors();
        $object->clearErrors();

        $validator = CValidator::createValidator($validatorName, $object, $dependentAttribute, $validatorParams);
        $validator->validate($object, $dependentAttribute);

        if ($object->hasErrors($dependentAttribute)) {
            $object->clearErrors();

            if (isset($validatorParams['message']))
                $message = $validatorParams['message'];
            elseif ($validator->message)
                $message = $validator->message;
            else
                $message = $this->message;

            $object->addError($dependentAttribute, Yii::t(
                        'yii', $message, array(
                        '{attribute}' => $relObject->getAttributeLabel($relAttribute),
                        '{value}' => $relObject->{$relAttribute},
                        '{dependentAttribute}' => $object->getAttributeLabel($dependentAttribute),
                        '{dependentValue}' => $object->{$dependentAttribute},
                    )
            ));
        }
        $object->addErrors($errorsBackup);
    }
}
?>
