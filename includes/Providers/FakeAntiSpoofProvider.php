<?php

/**
 * FakeAntiSpoofProvider short summary.
 *
 * FakeAntiSpoofProvider description.
 *
 * @version 1.0
 * @author stwalkerster
 */
class FakeAntiSpoofProvider implements IAntiSpoofProvider
{
    public function getSpoofs($username)
    {
        return array( "JWales", "Jwales", "JWhales" );   
    }
}
