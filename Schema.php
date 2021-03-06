<?php
function alkalmazottSema()
{
    $alkalmazottSema = [
        "name" => [kotelezo(), maxHossz(30)],
        "role" => [kotelezo(), valasztasiLehetosegek("manager", "leader", "worker")],
        "age" => [kotelezo(), kozott(18, 120)],
        "salary" => [kotelezo(), nagyobbMint(0)],
        "email" => [emailFormatum()],
        "isVerified" => [kotelezo()],
    ];
    return toSchema($alkalmazottSema);
}
function registrationSema()
{
    $regisrtrationSema = [
        "email" => [emailFormatum(), kotelezo()],
        "password" => [kotelezo(), hossz(3)],
        // "isVerified" => [kotelezo()],
    ];
    return toSchema($regisrtrationSema);
}
function loginSema()
{
    $loginSema = [

        "email" => [emailFormatum(), kotelezo()],
        "password" => [kotelezo(), hossz(3)],

    ];
    return toSchema($loginSema);
}

function toSchema($items)
{
    $ret = [];
    foreach ($items as $key => $value) {
        $ret[$key] = array_reduce($value, fn ($acc, $item) => array_merge($acc, [$item['validatorName'] => $item]), []);
    }
    return $ret;
}
function valasztasiLehetosegek(...$options)
{
    return [
        "validatorName" => "enum",
        "validatorFn" => fn ($input) => in_array($input, $options),
        "params" => implode(", ", $options)
    ];
}
function emailFormatum()
{
    return [
        "validatorName" => "email",
        "validatorFn" => fn ($input) => filter_var($input, FILTER_VALIDATE_EMAIL),
        "params" => null
    ];
}
function nagyobbMint($lower)
{
    return [
        "validatorName" => "largerThan",
        "validatorFn" => fn ($input) => strlen($input) > $lower,
        "params" => $lower
    ];
}
function maxHossz($lenght)
{
    return [
        "validatorName" => "maxLenght",
        "validatorFn" => fn ($input) => strlen($input) < $lenght,
        "params" => $lenght
    ];
}
function hossz($lenght)
{
    return [
        "validatorName" => "lenght",
        "validatorFn" => function ($input)  use ($lenght) {
            return  strlen($input) == $lenght ;
        },
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
        "required" => fn () => "Mez?? megad??sa k??telez??.",
        "largerThan" => fn ($value, $param) => "Mez?? nagyobb kell legyen mint $param. " . (!$value ? "Semmi nem" : $value) . " lett megadva.",
        "maxLength" => fn ($value, $param) => "Mez?? kevesebb karakterb??l ??lljon mint $param. " . strlen($value) . " karakter lett megadva.",
        "between" =>  fn ($value, $params) => "Mez?? ??rt??k??nek " .  $params[0] . " ??s " . $params[1] . " k??z??tt kell lennie. " . (!$value ? "Semmi nem" : $value) . " lett megadva.",
        "enum" => fn ($value, $param) =>  "Mez?? a k??vetkez?? ??rt??kek valamelyik??nek kell lennie: " .  $param . ". " . ((int)$value ?? "Semmi nem") . " lett megadva.",
        "email" => fn ($value, $param) => "Mez?? ??rt??knek ??rv??nyes email c??mnek kell lennie. '" . ($value ?? "nothing") . "' lett megadva.",
        "lenght" => fn ($value, $param) => "Mez?? pontosan $param karakterb??l ??lljon . " . strlen($value) . " karakter lett megadva.",
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

function isError($errors)
{
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
