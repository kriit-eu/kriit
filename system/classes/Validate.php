<?php namespace App;

class Validate
{

    public static function id($value, $message = 'Vale ID formaat'): void
    {
        if (!is_numeric($value) || intval($value) <= 0) {
            stop(400, $message);
        }
    }

    public static function string($value, $message = 'Vale teksti formaat'): void
    {
        if (!is_string($value)) {
            stop(400, $message);
        }
    }

    public static function url(string $url, string $message = 'Vigane URL'): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            stop(400, 'Vigane URL');
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Accept: */*',
                    'Accept-Encoding: gzip, deflate',
                    'User-Agent: ' . 'HTTPie/3.2.4'
                ]
            ]);

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode > 299) {
                $httpMessage = match ($httpCode) {
                    301 => '(püsiv ümbersuunamine). Kopeeri brauseri aadressirealt lõplik URL',
                    302 => '(ajutine ümbersuunamine). Kopeeri brauseri aadressirealt lõplik URL',
                    401 => '(autoriseerimine nõutud). Kontrolli, kas leht on avalik',
                    403 => '(ligipääs keelatud). Kontrolli, kas leht on avalik',
                    404 => '(lehte ei leitud). Kontrolli, kas leht on avalik, tegid aadressis vea või lehte enam ei eksisteeri',
                    429 => '(liiga palju päringuid). Oota mõni aeg ja kontrolli uuesti',
                    500 => '(serveri sisemine viga). Kontrolli aadressi või serveri olekut',
                    503 => '(server on hetkel ülekoormatud või hoolduses). Oota mõni aeg ja kontrolli uuesti',
                    default => '(tundmatu viga). Kontrolli aadressi'
                };
                stop(400, "URL ei ole kättesaadav. Server tagastas koodi $httpCode $httpMessage.");
            }
        } catch (\Exception $e) {
            stop(400, 'URL-i kontrollimisel tekkis viga');
        }
    }

    public static function bool(mixed $value, string $string)
    {
        if (!is_bool($value)) {
            stop(400, $string);
        }
    }
}