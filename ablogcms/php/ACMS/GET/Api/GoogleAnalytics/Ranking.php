<?php
/**
 * ACMS_GET_Api_GoogleAnalytics_Ranking
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Api_GoogleAnalytics_Ranking extends ACMS_GET_Api_GoogleAnalytics
{
    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $email  = config('google_api_app_service_email');
        $key    = config('google_api_client_id_key_location');

        $this->getService($email, $key);
        $this->setStartDate(config('ga_ranking_start_date'));
        $this->setEndDate(config('ga_ranking_end_date'));

        $options = array(
            "dimensions"    => "ga:pageTitle, ga:pagePath",
            "max-results"   => config('ga_ranking_max_results', 30),
            "sort"          => "-ga:pageviews",
        );

        if ( config('ga_ranking_filters') ) {
            // e.g. "ga:pagePath=~/(.*)\.html"
            $options['filters'] = config('ga_ranking_filters');
        }

        $results = $this->getResults('ga:pageviews', $options);

        if ( count($results->getRows()) === 0 ) {
            $Tpl->add('notFound');
            return $Tpl->get();
        }

        foreach ( $results->getRows() as $row ) {
            $Tpl->add('ranking:loop', array(
                'title' => $row[0],
                'path'  => $row[1],
                'views' => $row[2],
            ));
        }

        return $Tpl->get();
    }
}
