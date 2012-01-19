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

        $baseValidator=CValidator::createValidator($this->baseValidator, $object, $attribute, $this->baseValidatorParams);
        $baseValidator->validate($object, $attribute);

        if($object->hasErrors($attribute))
        {
            $object->clearErrors();
            $object->addErrors($baseErrorsBackup);
            return false;
        }

        foreach($this->dependentAttributesAndValidators as $currentAttribute=>$currentValidatorData)
            $this->validateDependentAttribute($object, $attribute, $currentAttribute, $currentValidatorData);

        $object->addErrors($baseErrorsBackup);
    }

    protected function validateDependentAttribute($object, $attribute, $dependentAttribute, $validatorData) {
        if(strpos($dependentAttribute, ',') !== false)
        {
            $attributes=array_map('trim', explode(',', $dependentAttribute));
            foreach($attributes as $dependentAttribute)
                $this->validateDependentAttribute($object, $attribute, $dependentAttribute, $validatorData);
            return true;
        }

        $validatorName   = $validatorData['validator'];
        $validatorParams = $validatorData['params'];

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
                        '{attribute}' => $object->getAttributeLabel($attribute),
                        '{value}' => $object->{$attribute},
                        '{dependentAttribute}' => $object->getAttributeLabel($dependentAttribute),
                        '{dependentValue}' => $object->{$dependentAttribute},
                    )
            ));
        }
        $object->addErrors($errorsBackup);
    }
}
?>
