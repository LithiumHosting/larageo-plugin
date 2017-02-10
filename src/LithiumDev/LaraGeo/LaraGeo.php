<?php
namespace LithiumDev\LaraGeo;


use Cache;
use GuzzleHttp\Client;

class LaraGeo {
    /**
     * Is the current IP stored in the cache?
     *
     * @var bool
     */
    public $isCached = false;
    /**
     * The URL of the geoPlugin API (json).
     *
     * @var string
     */
    protected $url = 'http://www.geoplugin.net/json.gp';

    private $ip;

    /**
     * Return all the information in an array.
     *
     * @param null $ip
     *
     * @return array Info from the IP parameter
     * @throws \LithiumDev\LaraGeo\LaraGeoException
     */
    public function getInfo($ip = null)
    {
        if (is_null($ip))
        {
            $ip = request()->getClientIp();
        }

        $this->ip = $ip;

        $hex = $this->ipToHex($this->ip);

        if ($hex === false)
        {
            throw new LaraGeoException('The IP ' . $this->ip . ' appears to be invalid');
        }

        // Check if the IP is in the cache
        if (Cache::has($hex))
        {
            $this->isCached = true;
        }
//        Cache::forget($hex);
        // Use the IP info stored in cache or store it
        $ipInfo = Cache::remember($hex, 10080, function ()
        {
            return $this->fetchInfo();
        });

        $ipInfo->geoplugin_cached = $this->isCached;

        return $ipInfo;
    }

    /**
     * Return a hex string of the current IP.  Used as the key for cache storage.
     *
     * @param $ipAddress
     *
     * @return bool|string
     */
    private function ipToHex($ipAddress)
    {
        $hex = '';
        if (strpos($ipAddress, ',') !== false)
        {
            $splitIp   = explode(',', $ipAddress);
            $ipAddress = trim($splitIp[0]);
        }
        $isIpV6 = false;
        $isIpV4 = false;
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)
        {
            $isIpV6 = true;
        }
        elseif (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
        {
            $isIpV4 = true;
        }
        if (! $isIpV4 && ! $isIpV6)
        {
            return false;
        }
        // IPv4 format
        if ($isIpV4)
        {
            $parts = explode('.', $ipAddress);
            for ($i = 0; $i < 4; $i++)
            {
                $parts[ $i ] = str_pad(dechex($parts[ $i ]), 2, '0', STR_PAD_LEFT);
            }
            $ipAddress = '::' . $parts[0] . $parts[1] . ':' . $parts[2] . $parts[3];
            $hex       = implode('', $parts);
        } // IPv6 format
        else
        {
            $parts = explode(':', $ipAddress);
            // If this is mixed IPv6/IPv4, convert end to IPv6 value
            if (filter_var($parts[ count($parts) - 1 ], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            {
                $partsV4 = explode('.', $parts[ count($parts) - 1 ]);
                for ($i = 0; $i < 4; $i++)
                {
                    $partsV4[ $i ] = str_pad(dechex($partsV4[ $i ]), 2, '0', STR_PAD_LEFT);
                }
                $parts[ count($parts) - 1 ] = $partsV4[0] . $partsV4[1];
                $parts[]                    = $partsV4[2] . $partsV4[3];
            }
            $numMissing    = 8 - count($parts);
            $expandedParts = [];
            $expansionDone = false;
            foreach ($parts as $part)
            {
                if (! $expansionDone && $part == '')
                {
                    for ($i = 0; $i <= $numMissing; $i++)
                    {
                        $expandedParts[] = '0000';
                    }
                    $expansionDone = true;
                }
                else
                {
                    $expandedParts[] = $part;
                }
            }
            foreach ($expandedParts as &$part)
            {
                $part = str_pad($part, 4, '0', STR_PAD_LEFT);
            }
            $ipAddress = implode(':', $expandedParts);
            $hex       = implode('', $expandedParts);
        }
        // Validate the final IP
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP))
        {
            return false;
        }

        return strtolower(str_pad($hex, 32, '0', STR_PAD_LEFT));
    }

//    function ip2bin($ip)
//    {
//        $ipbin = '';
//        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
//            return base_convert(ip2long($ip),10,2);
//        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
//            return false;
//        if(($ip_n = inet_pton($ip)) === false) return false;
//        $bits = 15; // 16 x 8 bit = 128bit (ipv6)
//        while ($bits >= 0)
//        {
//            $bin = sprintf("%08b",(ord($ip_n[$bits])));
//            $ipbin = $bin.$ipbin;
//            $bits--;
//        }
//        return $ipbin;
//    }

    /**
     * Fetch the info from IP using CURL or file_get_contents.
     * @throws \LithiumDev\LaraGeo\LaraGeoException
     * @return object
     */
    private function fetchInfo()
    {
        $response = null;
        $params   = [];

        $url = $this->url;
        if (! empty($this->ip))
        {
            $params['ip'] = $this->ip;
        }
        $client   = new Client;
        $response = $client->get($url, ['query' => $params]);
        $response->

        $data = json_decode($response->getBody());

        if ($data->geoplugin_status === 404 || empty($data))
        {
            throw new LaraGeoException('Invalid Response, check the IP and try again.');
        }

        return $data;
    }
}
