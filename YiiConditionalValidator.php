<?php
/**
 * Validates multiple attributes using any Yii Core Validator
 * depending on some another attribute's condition (validation) is true;
 *
 * @author Sidney Lins <solucoes@wmaior.com>
 * @copyright Copyright &copy; 2011 Sidney Lins
 * @version 0.2.0
 * @license New BSD Licence
 */
class YiiConditionalValidator extends CValidator{
    public $validation=array('required');
    public $dependentValidations=array();
    public $message = "{dependentAttribute} is invalid.";

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

        $validator=CValidator::createValidator($this->validation[0], $relObject, $relAttribute, array_slice($this->validation, 1, count($this->validation)-1));
        $validator->validate($relObject, $relAttribute);

        if($relObject->hasErrors($relAttribute))
        {
            $object->clearErrors();
            $object->addErrors($baseErrorsBackup);
            return false;
        }

        foreach($this->dependentValidations as $currentAttribute=>$validationData)
        {
            if(!is_array($validationData) || !count($validationData))
                throw new CException('YiiConditionalValidator: dependentAttributesAndValidators must be an array and must have at least one value.');
            $this->validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $currentAttribute, $validationData);
        }

        $object->addErrors($baseErrorsBackup);
    }

    protected function validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $dependentAttribute, $validationData) {
        if(strpos($dependentAttribute, ',') !== false)
        {
            $attributes=array_map('trim', explode(',', $dependentAttribute));
            foreach($attributes as $dependentAttribute)
                $this->validateDependentAttribute($object, $relObject, $attribute, $relAttribute, $dependentAttribute, $validationData);
            return true;
        }

        foreach($validationData as $validation)
        {
            $validatorName=$validation[0];
            $validatorParams = array_slice($validation, 1, count($validation)-1);

            $errorsBackup = $object->getErrors();
            $object->clearErrors();

            $validator = CValidator::createValidator($validatorName, $object, $dependentAttribute, $validatorParams);
            $validator->validate($object, $dependentAttribute);

            if ($object->hasErrors($dependentAttribute))
            {
                $errorMessage=$object->getError($dependentAttribute);
                $object->clearErrors();

                if (isset($validatorParams['message']))
                    $message = $validatorParams['message'];
                else
                    $message = $errorMessage;

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
}
?>
