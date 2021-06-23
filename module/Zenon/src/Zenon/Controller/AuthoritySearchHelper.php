<?php

namespace Zenon\Controller;
use VuFindSearch\Query\Query;

class AuthoritySearchHelper
{
    public static function searchAuthorities($searchService, string $q, $limit = 100, $offset = 0) {
        $query = new Query(self::escapeForSolr($q));
        return $searchService->search('SolrAuth', $query, $offset, $limit);
    }

    public static function getAuthorityId($searchService, string $system, string $id){
        $query = new Query($system . ":" . self::escapeForSolr($id));
        $authoritySearchResults = $searchService->search('SolrAuth', $query)->first();
        if (is_null($authoritySearchResults)) {
            return null;
        }
       return $authoritySearchResults->getRawData()['id'];
    }

    public static function getBibliosForAuthorityId($searchService, $authorityId){
        return $searchService->search(
            'Solr', 
            new Query('authority_id_str_mv:' . self::escapeForSolr($authorityId))
        );
    }

     /**
     * Escape a string for inclusion in a Solr query.
     *
     * @param string $str String to escape
     *
     * @return string
     */
    public static function escapeForSolr($str)
    {
        return '"' . addcslashes($str, '"') . '"';
    }
}