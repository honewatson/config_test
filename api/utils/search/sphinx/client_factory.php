<?php

namespace api\utils\search\sphinx;

use api;

class client_factory {
    
    public static function build() {

            $search_client = new \SphinxClient;
            $search_client->setServer("localhost", 9312);
            $search_client->setMatchMode(SPH_MATCH_ANY);
            $search_client->setMaxQueryTime(3);
            $search_client->SetRankingMode(SPH_RANK_WORDCOUNT, "" );
            return $search_client;
        
    }
    
}