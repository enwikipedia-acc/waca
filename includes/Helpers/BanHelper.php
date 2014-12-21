<?php

class BanHelper implements IBanHelper
{
    /**
     * Summary of nameIsBanned
     * @param string $name The name to test if is banned.
     * @return Ban|boolean
     */
    public function nameIsBanned($name)
    {
        return Ban::getBanByTarget($name, "Name");
    }
    
    /**
     * Summary of emailIsBanned
     * @param string $email 
     * @return Ban|boolean
     */
    public function emailIsBanned($email)
    {
        return Ban::getBanByTarget($email, "EMail");
    }
    
    /**
     * Summary of ipIsBanned
     * @param string $ip 
     * @return Ban|boolean
     */
    public function ipIsBanned($ip)
    {
        return Ban::getBanByTarget($ip, "IP");
    }
}
