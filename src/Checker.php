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
     * @var int
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
     * @var int
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
     * @var string
     */
    private $key;

    /**
     * Constructor.
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

        $this->key = config('validator-pizza.key');
    }

    /**
     * Check domain.
     *
     * @param string $domain
     *
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
            $response = $this->query($domain);

            // The email address is invalid
            if (400 == $response->status) {
                return false;
            }

            // Rate limit exceeded
            if (429 == $response->status) {
                return 'allow' == $this->decision_rate_limit ? true : false;
            }

            if (200 != $response->status) {
                return false;
            }

            $data = $response;
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
     * Check email address.
     *
     * @param string $email
     *
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
     * Query the API.
     *
     * @param string $domain
     *
     * @throws ClientException
     *
     * @return \stdClass API response data
     */
    private function query(string $domain): \stdClass
    {
        $uri = '/domain/' . strtolower($domain);

        if ($this->key) {
            $uri .= '?key=' . $this->key;
        }

        $request = new Request('GET', $uri, [
            'Accept' => 'application/json',
        ]);

        try {
            $response = $this->client->send($request);
        } catch (\Exception $e) {
            return (object) [
                'status' => $e->getResponse()->getStatusCode(),
            ];
        }

        $data = json_decode($response->getBody());

        return (object) [
            'status' => 200,
            'domain' => $data->domain,
            'mx' => optional($data)->mx ?? false,
            'disposable' => optional($data)->disposable ?? false,
        ];
    }

    private function storeResponse(\stdClass $data)
    {
        $this->remaining = $data->remaining_requests ?? 0;

        if ($this->store_checks) {
            /** @var ValidatedDomain $check */
            $check = ValidatedDomain::query()->firstOrCreate(
                [
                    'domain' => $data->domain,
                ], [
                    'mx' => $data->mx,
                    'disposable' => $data->disposable,
                    'last_queried' => Carbon::now(),
                ]
            );

            if ( ! $check->wasRecentlyCreated) {
                ++$check->hits;
                $check->last_queried = Carbon::now();

                $check->save();
            }
        }
    }

    /**
     * Decide wether the given data represents a valid domain.
     *
     * @param \stdClass $data
     *
     * @return bool
     */
    private function decideIsValid(\stdClass $data): bool
    {
        if ('deny' == $this->decision_no_mx && true !== optional($data)->mx) {
            return false;
        }

        return false === optional($data)->disposable;
    }
}
