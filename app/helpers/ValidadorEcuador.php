<?php
/**
 * DigiSports — Validador de Documentos Ecuador
 * 
 * Implementa algoritmos de validación para:
 *   - Cédula de ciudadanía (módulo 10)
 *   - RUC persona natural (módulo 10 + 001)
 *   - RUC sociedades (módulo 11)
 *   - RUC sector público (módulo 11)
 * 
 * Reutilizable por todos los subsistemas deportivos.
 *
 * @package DigiSports\Helpers
 * @version 1.0.0
 * @since 2026-02-09
 */

class ValidadorEcuador {

    /**
     * Validar cédula ecuatoriana (10 dígitos, módulo 10)
     *
     * Algoritmo:
     *   1. Verificar longitud = 10 y que solo tenga dígitos.
     *   2. Los 2 primeros dígitos = código de provincia (01-24).
     *   3. El tercer dígito < 6.
     *   4. Posiciones impares (1,3,5,7,9): multiplicar por 2; si resultado > 9, restar 9.
     *   5. Posiciones pares (2,4,6,8): dejar igual.
     *   6. Sumar todo. El dígito verificador = (decena superior - suma). Si resultado = 10, verificador = 0.
     *   7. Comparar con el décimo dígito.
     *
     * @param string $cedula  Cédula a validar (puede venir con o sin guiones/espacios)
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarCedula(string $cedula): array {
        // Limpiar caracteres no numéricos
        $cedula = preg_replace('/[^0-9]/', '', $cedula);

        // Longitud exacta 10
        if (strlen($cedula) !== 10) {
            return ['valido' => false, 'mensaje' => 'La cédula debe tener exactamente 10 dígitos'];
        }

        // Código de provincia: 01–24
        $provincia = (int)substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return ['valido' => false, 'mensaje' => 'Código de provincia inválido (debe ser 01-24)'];
        }

        // Tercer dígito < 6 (persona natural)
        $tercerDigito = (int)$cedula[2];
        if ($tercerDigito >= 6) {
            return ['valido' => false, 'mensaje' => 'El tercer dígito de la cédula debe ser menor a 6'];
        }

        // Algoritmo módulo 10
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2]; // 9 posiciones
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $valor = (int)$cedula[$i] * $coeficientes[$i];
            if ($valor > 9) $valor -= 9;
            $suma += $valor;
        }

        $residuo = $suma % 10;
        $verificadorCalculado = ($residuo === 0) ? 0 : 10 - $residuo;
        $verificadorReal = (int)$cedula[9];

        if ($verificadorCalculado !== $verificadorReal) {
            return ['valido' => false, 'mensaje' => 'Número de cédula inválido (dígito verificador no coincide)'];
        }

        return ['valido' => true, 'mensaje' => 'Cédula válida'];
    }

    /**
     * Validar RUC de persona natural (13 dígitos = cédula + 001)
     *
     * @param string $ruc
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarRucNatural(string $ruc): array {
        $ruc = preg_replace('/[^0-9]/', '', $ruc);

        if (strlen($ruc) !== 13) {
            return ['valido' => false, 'mensaje' => 'El RUC debe tener 13 dígitos'];
        }

        // Los 3 últimos dígitos deben ser 001
        if (substr($ruc, 10, 3) !== '001') {
            return ['valido' => false, 'mensaje' => 'Los 3 últimos dígitos del RUC de persona natural deben ser 001'];
        }

        // Validar los primeros 10 como cédula
        return self::validarCedula(substr($ruc, 0, 10));
    }

    /**
     * Validar RUC de sociedad privada (tercer dígito = 9, módulo 11)
     *
     * @param string $ruc
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarRucSociedad(string $ruc): array {
        $ruc = preg_replace('/[^0-9]/', '', $ruc);

        if (strlen($ruc) !== 13) {
            return ['valido' => false, 'mensaje' => 'El RUC debe tener 13 dígitos'];
        }

        $provincia = (int)substr($ruc, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return ['valido' => false, 'mensaje' => 'Código de provincia inválido'];
        }

        if ((int)$ruc[2] !== 9) {
            return ['valido' => false, 'mensaje' => 'El tercer dígito para RUC de sociedad debe ser 9'];
        }

        if (substr($ruc, 10, 3) !== '001') {
            return ['valido' => false, 'mensaje' => 'Los 3 últimos dígitos deben ser 001'];
        }

        // Módulo 11 con 9 coeficientes
        $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $suma += (int)$ruc[$i] * $coeficientes[$i];
        }

        $residuo = $suma % 11;
        $verificador = ($residuo === 0) ? 0 : 11 - $residuo;

        if ($verificador !== (int)$ruc[9]) {
            return ['valido' => false, 'mensaje' => 'RUC de sociedad inválido'];
        }

        return ['valido' => true, 'mensaje' => 'RUC de sociedad válido'];
    }

    /**
     * Validar RUC de sector público (tercer dígito = 6, módulo 11)
     *
     * @param string $ruc
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarRucPublico(string $ruc): array {
        $ruc = preg_replace('/[^0-9]/', '', $ruc);

        if (strlen($ruc) !== 13) {
            return ['valido' => false, 'mensaje' => 'El RUC debe tener 13 dígitos'];
        }

        $provincia = (int)substr($ruc, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            return ['valido' => false, 'mensaje' => 'Código de provincia inválido'];
        }

        if ((int)$ruc[2] !== 6) {
            return ['valido' => false, 'mensaje' => 'El tercer dígito para RUC público debe ser 6'];
        }

        if (substr($ruc, 9, 4) !== '0001') {
            return ['valido' => false, 'mensaje' => 'Los 4 últimos dígitos deben ser 0001'];
        }

        $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        for ($i = 0; $i < 8; $i++) {
            $suma += (int)$ruc[$i] * $coeficientes[$i];
        }

        $residuo = $suma % 11;
        $verificador = ($residuo === 0) ? 0 : 11 - $residuo;

        if ($verificador !== (int)$ruc[8]) {
            return ['valido' => false, 'mensaje' => 'RUC público inválido'];
        }

        return ['valido' => true, 'mensaje' => 'RUC público válido'];
    }

    /**
     * Validar automáticamente según el tipo de documento
     *
     * @param string $documento  Número a validar
     * @param string $tipo       'CED', 'RUC', 'PAS', 'OTR'
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validar(string $documento, string $tipo = 'CED'): array {
        $documento = trim($documento);

        if (empty($documento)) {
            return ['valido' => true, 'mensaje' => 'Documento vacío (no se valida)']; // Opcional
        }

        switch (strtoupper($tipo)) {
            case 'CED':
                return self::validarCedula($documento);

            case 'RUC':
                $doc = preg_replace('/[^0-9]/', '', $documento);
                if (strlen($doc) !== 13) {
                    return ['valido' => false, 'mensaje' => 'El RUC debe tener 13 dígitos'];
                }
                $tercero = (int)$doc[2];
                if ($tercero < 6) return self::validarRucNatural($doc);
                if ($tercero === 6) return self::validarRucPublico($doc);
                if ($tercero === 9) return self::validarRucSociedad($doc);
                return ['valido' => false, 'mensaje' => 'Tercer dígito del RUC no reconocido'];

            case 'PAS':
                // Pasaporte: solo verificar longitud mínima y alfanumérico
                if (strlen($documento) < 5 || strlen($documento) > 20) {
                    return ['valido' => false, 'mensaje' => 'El pasaporte debe tener entre 5 y 20 caracteres'];
                }
                if (!preg_match('/^[A-Za-z0-9]+$/', $documento)) {
                    return ['valido' => false, 'mensaje' => 'El pasaporte solo puede contener letras y números'];
                }
                return ['valido' => true, 'mensaje' => 'Pasaporte válido'];

            case 'OTR':
                // Otro: sin validación específica, solo longitud razonable
                if (strlen($documento) < 3 || strlen($documento) > 30) {
                    return ['valido' => false, 'mensaje' => 'El documento debe tener entre 3 y 30 caracteres'];
                }
                return ['valido' => true, 'mensaje' => 'Documento registrado'];

            default:
                return ['valido' => false, 'mensaje' => 'Tipo de documento no reconocido'];
        }
    }
}
