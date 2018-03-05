<?php

namespace romanzipp\ValidatorPizza;

class Checker
{
    /**
     * Check domain
     * @param  string $domain
     * @return bool
     */
    public function allowedDomain(string $domain): bool
    {
        # code...
    }

    /**
     * Check email address
     * @param  string $email
     * @return bool
     */
    public function allowedEmail(string $email): bool
    {
        list($local, $domain) = explode('@', $email, 2);

        return $this->checkDomain($domain);
    }
}
