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
    public $attributesAndValidators=array();

    public $message = "{dependentAttribute} is not valid.";

    protected function validateAttribute($object, $attribute)
    {
        $baseErrorsBackup=$object->getErrors($attribute);
        $object->clearErrors($attribute);

        $baseValidator=CValidator::createValidator($this->baseValidator, $object, $attribute, $this->baseValidatorParams);
        $baseValidator->validate($object, $attribute);

        if($object->hasErrors($attribute))
        {
            $object->clearErrors();
            $object->addErrors($baseErrorsBackup);
            return false;
        }

        foreach($this->attributesAndValidators as $currentAttribute=>$currentValidatorData)
        {
            $currentValidatorName=$currentValidatorData['validator'];
            $currentValidatorParams=$currentValidatorData['params'];

            $errorsBackup=$object->getErrors();
            $object->clearErrors();

            $validator=CValidator::createValidator($currentValidatorName, $object, $currentAttribute, $currentValidatorParams);
            $validator->validate($object, $currentAttribute);

            if($object->hasErrors($currentAttribute))
            {
                $object->clearErrors();

                if(isset($currentValidatorParams['message']))
                    $message=$currentValidatorParams['message'];
                elseif($validator->message)
                    $message=$validator->message;
                else
                    $message=$this->message;

                $object->addError($currentAttribute, Yii::t(
                        'yii',
                        $message,
                        array(
                            '{attribute}'=>$object->getAttributeLabel($attribute),
                            '{value}'=>$object->{$attribute},
                            '{dependentAttribute}'=>$object->getAttributeLabel($currentAttribute),
                            '{dependentValue}'=>$object->{$currentAttribute},
                        )
                ));
            }
            $object->addErrors($errorsBackup);
        }
        $object->addErrors($baseErrorsBackup);
    }
}
?>
