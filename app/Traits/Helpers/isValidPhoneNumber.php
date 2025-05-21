<?php

namespace App\Traits\Helpers;

trait IsValid
{

    /**
     * Valide un numéro de téléphone.
     *
     * @param string $phone Le numéro à valider.
     * @return bool True si valide, sinon false.
     */
    function validatePhoneNumber(string $phone): bool
    {
        $path = app_path('Traits/Helpers/data/countries');

        $normalized = preg_replace('/[\s\-\(\)]/', '', $phone);

        [$countryCode, $localNumber] = $this->extractPhoneParts($normalized);
        
        // Extraire code pays international (ex: +229, 00229)
        if (!preg_match('/^(?:\+|00)?(\d{1,4})(\d+)$/', $normalized, $matches)) {
            return false;
        }

        $countryCode = $matches[1];
        $localNumber = $matches[2];

        // Charger la config JSON pays
        $configPath = "$path/country_code.json";

        if (!file_exists($configPath)) {
            return false;
        }

        $countries = json_decode(file_get_contents($configPath), true);

        if (!isset($countries[$countryCode])) {
            return false; // Pays non supporté
        }

        $countryInfo = $countries[$countryCode];
        $start = 0;

        // Vérifier que le numéro local commence par l'indicatif local
        if (isset($countryInfo['local_indicatif'])) {
            if (substr($localNumber, 0, strlen($countryInfo['local_indicatif'])) !== $countryInfo['local_indicatif']) {
                return false;
            }

            $start = strlen($countryInfo['local_indicatif']);

            // Valider longueur téléphone
            if (strlen(substr($localNumber, $start)) != $countryInfo['local_number_length']) {
                return false;
            }
        } else {
            // Valider longueur téléphone
            if (strlen($localNumber) != $countryInfo['local_number_length']) {
                return false;
            }
        }

        // Charger la liste des préfixes locaux
        $prefixesFile = $path . '/' . $countryInfo['local_prefix'];
        if (!file_exists($prefixesFile)) {
            return false;
        }

        $prefixes = json_decode(file_get_contents($prefixesFile), true);
        if (!is_array($prefixes)) {
            return false;
        }

        // Extraire le préfixe (2 chiffres)
        $localPrefix = substr($localNumber, $start, 2);

        // Vérifier que le préfixe est valide
        if (!in_array(intval($localPrefix), $prefixes, true)) {
            return false;
        }

        return true;
    }

    private function extractPhoneParts(string $phone): ?array {
        $normalized = preg_replace('/[\s\-\(\)]/', '', $phone);
        if (!preg_match('/^(?:\+|00)?(\d{1,4})(\d+)$/', $normalized, $matches)) {
            return null;
        }
        return ['country' => $matches[1], 'number' => $matches[2]];
    }
}
