<?php
function alkalmazottSema(){
$alkalmazottSema = [
    "name" => [kotelezo(),maxHossz(30)],
    "role" => [kotelezo(), valasztasiLehetosegek("manager","leader","worker")],
    "age" => [kotelezo(), kozott(18, 120)],
    "salary" => [kotelezo(),nagyobbMint(0)],
    "email" => [emailFormatum()],
    "isVerified" => [kotelezo()],
];
return toSchema($alkalmazottSema);
}
function toSchema($items) {
    $ret = [];
    foreach ($items as $key => $value) {
        $ret[$key] = array_reduce($value, fn ($acc, $item) => array_merge($acc, [$item['validatorName'] => $item]), []);
    }
    return $ret;
}
function valasztasiLehetosegek(...$options){
    return [
        "validatorName" => "enum",
        "validatorFn" => fn ($input)=> in_array($input,$options),
        "params" => implode(", ",$options)

    ];
 }
 function emailFormatum(){
    return [
        "validatorName" => "email",
        "validatorFn" => fn ($input)=> filter_var($input,FILTER_VALIDATE_EMAIL),
        "params" => null

    ];
 }
 function nagyobbMint($lower){
    return [
        "validatorName" => "largerThan",
        "validatorFn" => fn ($input)=> strlen($input)>$lower,
        "params" => $lower

    ];
 }
 function maxHossz($lenght){
    return [
        "validatorName" => "maxLenght",
        "validatorFn" => fn ($input)=> strlen($input)<$lenght,
        "params" => $lenght

    ];
}
function kozott($lower, $upper)
{

    return [
        "validatorName" => "between",
        "validatorFn" => function ($input) use ($lower, $upper) {

            return $input >= $lower && $input <= $upper;
        },
        "params" => [$lower, $upper]

    ];
}
function kotelezo()
{
    return [
        "validatorName" => "required",
        "validatorFn" => function ($input) {

            return (bool)$input;
        },
        "params" => null

    ];
}
function getErrorMessages($schema, $errors)
{
    $validatorNameToMessage = [
        "required" => fn () => "Mező megadása kötelező.",
        "largerThan" => fn ($value, $param) => "Mező nagyobb kell legyen mint $param. " . (!$value ? "Semmi nem" : $value) . " lett megadva.",
        "maxLength" => fn ($value, $param) => "Mező kevesebb karakterből álljon mint $param. " . strlen($value) . " karakter lett megadva.",
        "between" =>  fn ($value, $params) => "Mező értékének " .  $params[0] . " és " . $params[1] . " között kell lennie. " . (!$value ? "Semmi nem" : $value) . " lett megadva.",
        "enum" => fn ($value, $param) =>  "Mező a következő értékek valamelyikének kell lennie: " .  $param . ". " . ((int)$value ?? "Semmi nem") . " lett megadva.",
        "email" => fn ($value, $param) => "Mező értéknek érvényes email címnek kell lennie. '" . ($value ?? "nothing") . "' lett megadva.",
    ];

    $ret = [];
    foreach ($errors as $fieldName => $errorsForField) {
        foreach ($errorsForField as $err) {
            $toMessageFunction = $validatorNameToMessage[$err['validatorName']];
            $schemaForField = $schema[$fieldName];
            $ret[$fieldName][] = $toMessageFunction($err['value'], $schemaForField[$err['validatorName']]['params']);
        }
    }
    return $ret;
}

function isError ($errors) {
    return count(array_reduce(array_values($errors), 'array_merge', []));
}
function validate($schema, $body)
{
    $fieldNames = array_keys($schema);

    // https://kodbazis.hu/cikkek/a-leghasznosabb-tombfuggvenyek
    $ret = array_reduce(
        $fieldNames,
        fn ($gyujto, $fieldName) => array_merge($gyujto, [$fieldName => []]),
        []
    );

    foreach ($fieldNames as $fieldName) {
        $validators = $schema[$fieldName];

        $isRequiredField = count(array_filter($validators, fn ($validator) => $validator['validatorName'] === 'required'));

        if (!$isRequiredField && !($body[$fieldName] ?? null)) {
            continue;
        }


        foreach ($validators as $validator) {
            $validatorFn = $validator['validatorFn'];
            $isFieldValid = $validatorFn($body[$fieldName] ?? null);
            if (!$isFieldValid) {
                $ret[$fieldName][] = [
                    'validatorName' => $validator['validatorName'],
                    'value' => $body[$fieldName] ?? null
                ];
            }
        }
    }

    return $ret;
}