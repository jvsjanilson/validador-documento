<?php

namespace Jvsjanilson\ValidadorDocumento;

use InvalidArgumentException;

class ValidadorDocumento
{
    /**
     * Valida documento
     * @param string $documento
     * @param string $tipoDocumento
     * @return bool
     */

    public static function validar(string $documento, ?string $tipoDocumento = null) : bool
    {
        $valorLimpo = preg_replace('/[^A-Za-z0-9]/', '', $documento);

        $validadores = [
            'cpf'  => fn($v) => self::validarCPF($v),
            'cnpj' => fn($v) => self::validarCNPJ($v),
        ];

        if ($tipoDocumento !== null) {
            if (!isset($validadores[$tipoDocumento])) {
                throw new InvalidArgumentException("Tipo de documento não suportado");
            }

            return $validadores[$tipoDocumento]($valorLimpo);
        }


        foreach ($validadores as $tipo => $func) {
            if (
                ($tipo === 'cpf' && strlen($valorLimpo) == 11) ||
                ($tipo === 'cnpj' && strlen($valorLimpo) == 14)
            ) {
                return $func($valorLimpo);
            }
        }

        return false;
    }

    /**
     * Valida CPF
     * @param string $cpf
     * @return bool
     */
    private static function validarCPF(string $cpf) : bool
    {
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if ($cpf[$t] != $digito) {
                return false;
            }
        }

        return true;
    }

    /**
     * Conversao de char para numero
     * @param mixed $char
     * @return int
     */
    private static function charParaNumero($char) : int
    {
        return is_numeric($char) ? (int)$char : ord(strtoupper($char)) - 55;
    }

    /**
     * Valida CNPJ (Alfanumerico)
     * @param string $cnpj
     * @return bool
     */
    private static function validarCNPJ(string $cnpj): bool
    {
        if (strlen($cnpj) != 14) return false;

        $pesos1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $pesos2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];

        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += self::charParaNumero($cnpj[$i]) * $pesos1[$i];
        }

        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += self::charParaNumero($cnpj[$i]) * $pesos2[$i];
        }

        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }
}