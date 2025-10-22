<?php

namespace App\Constants;

class ErrorCodes
{
    public const SUCCESS = '00';
    public const CAMPOS_REQUERIDOS = '01';
    public const CLIENTE_DUPLICADO = '02';
    public const CLIENTE_NO_ENCONTRADO = '03';
    public const DATOS_INCORRECTOS = '04';
    public const SALDO_INSUFICIENTE = '05';
    public const SESION_PAGO_NO_ENCONTRADA = '06';
    public const TOKEN_INCORRECTO = '07';
    public const SESION_EXPIRADA = '08';
    public const ERROR_BD = '09';
    public const ERROR_EMAIL = '10';

    public static function getMessages(): array
    {
        return [
            self::SUCCESS => 'Operación exitosa',
            self::CAMPOS_REQUERIDOS => 'Campos requeridos faltantes',
            self::CLIENTE_DUPLICADO => 'Cliente ya existe',
            self::CLIENTE_NO_ENCONTRADO => 'Cliente no encontrado',
            self::DATOS_INCORRECTOS => 'Datos incorrectos',
            self::SALDO_INSUFICIENTE => 'Saldo insuficiente',
            self::SESION_PAGO_NO_ENCONTRADA => 'Sesión de pago no encontrada',
            self::TOKEN_INCORRECTO => 'Token incorrecto',
            self::SESION_EXPIRADA => 'Sesión expirada',
            self::ERROR_BD => 'Error de base de datos',
            self::ERROR_EMAIL => 'Error al enviar email',
        ];
    }
}
