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
     * @param string $domain
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
            if ($response->status == 400) {
                return false;
            }

            // Rate limit exceeded
            if ($response->status == 429) {
                return $this->decision_rate_limit == 'allow' ? true : false;
            }

            if ($response->status != 200) {
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
     * Check email address
     * @param string $email
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
     * @param string $domain
     * @return \stdClass         API response data
     * @throws ClientException
     */
    private function query(string $domain): \stdClass
    {
        $uri = '/domain/' . strtolower($domain);

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
            'status'     => 200,
            'domain'     => $data->domain,
            'mx'         => optional($data)->mx ?? false,
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
     * @param \stdClass $data
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
