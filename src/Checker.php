<?php

namespace romanzipp\ValidatorPizza;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use romanzipp\ValidatorPizza\Models\ValidatedDomain;

class Checker
{
    /**
     * @var integer
     */
    public $remaining = 0;

    /**
     * @var bool
     */
    public $from_cache;

    /**
     * @var bool
     */
    private $store_checks;

    /**
     * @var bool
     */
    private $cache_checks;

    /**
     * @var integer
     */
    private $cache_duration;

    /**
     * @var string
     */
    private $decision_rate_limit;

    /**
     * @var string
     */
    private $decision_no_mx;

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://www.validator.pizza',
        ]);

        $this->store_checks = config('validator-pizza.store_checks');

        $this->cache_checks = config('validator-pizza.cache_checks');

        $this->cache_duration = config('validator-pizza.cache_duration');

        $this->decision_rate_limit = config('validator-pizza.decision_rate_limit');

        $this->decision_no_mx = config('validator-pizza.decision_no_mx');
    }

    /**
     * Check domain
     * @param  string $domain
     * @return bool
     */
    public function allowedDomain(string $domain): bool
    {
        $cacheKey = 'validator_pizza_' . $domain;

        // Retreive from Cache if enabled

        if ($this->cache_checks && Cache::has($cacheKey)) {

            $data = Cache::get($cacheKey);

            $this->from_cache = true;
        }

        if ( ! $this->from_cache) {

            try {

                $data = $this->query($domain);

            } catch (ClientException $e) {

                // Rate Limit exceeded

                if ($e->getResponse()->getStatusCode() == 429) {

                    return $this->decision_rate_limit == 'allow' ? true : false;
                }

            } catch (\Exception $e) {

                return false;
            }
        }

        // Store in Cache if enabled

        if ($this->cache_checks && ! $this->from_cache) {

            Cache::put($cacheKey, $data, $this->cache_duration);
        }

        // Store in Database or update Database query hits

        if ($this->store_checks) {

            $this->storeResponse($data);
        }

        return $this->decideIsValid($data);
    }

    /**
     * Check email address
     * @param  string $email
     * @return bool
     */
    public function allowedEmail(string $email): bool
    {
        if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        list($local, $domain) = explode('@', $email, 2);

        return $this->allowedDomain($domain);
    }

    /**
     * Query the API
     * @param  string            $domain
     * @throws ClientException
     * @return \stdClass         API response data
     */
    private function query(string $domain): \stdClass
    {
        $uri = '/domain/' . strtolower($domain);

        $request = new Request('GET', $uri, [
            'Accept' => 'application/json',
        ]);

        $response = $this->client->send($request);

        $data = json_decode($response->getBody());

        return (object) [
            'domain'     => $data->domain,
            'mx'         => optional($data)->mx ?? false,
            'disposable' => optional($data)->disposable ?? false,
        ];
    }

    private function storeResponse(\stdClass $data)
    {
        $this->remaining = $data->remaining_requests ?? 0;

        if ($this->store_checks) {

            $check = ValidatedDomain::firstOrCreate(
                [
                    'domain' => $data->domain,
                ], [
                    'mx'           => $data->mx,
                    'disposable'   => $data->disposable,
                    'last_queried' => Carbon::now(),
                ]
            );

            if ( ! $check->wasRecentlyCreated) {

                $check->hits++;
                $check->last_queried = Carbon::now();

                $check->save();
            }
        }
    }

    /**
     * Decide wether the given data represents a valid domain
     * @param  \stdClass $data
     * @return bool
     */
    private function decideIsValid(\stdClass $data): bool
    {
        if ($this->decision_no_mx == 'deny' && optional($data)->mx !== true) {
            return false;
        }

        return optional($data)->disposable === false;
    }
}
