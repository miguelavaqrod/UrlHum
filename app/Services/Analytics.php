<?php

/*
 * UrlHum (https://urlhum.com)
 *
 * @link      https://github.com/urlhum/UrlHum
 * @copyright Copyright (c) 2019 Christian la Forgia
 * @license   https://github.com/urlhum/UrlHum/blob/master/LICENSE.md (MIT License)
 */

namespace App\Services;

use App\ViewUrl;

/**
 * Class Analytics, used to retrieve analytics data about Short URLs.
 *
 *
 * @author Christian la Forgia <christian@optiroot.it>
 */
class Analytics
{
    /**
     * Get the list of the URL's visitors countries.
     *
     * @param $url
     * @return array
     */
    public static function getCountriesViews($url)
    {
        $countriesViews = ViewUrl::where('short_url', $url)
            ->select('country_full', \DB::raw('count(*) as views'), \DB::raw('sum(real_click) as real_views'))
            ->groupBy('country_full')
            ->get();

        return $countriesViews;
    }

    /**
     * Generate a random set of colors, depending on how much countries
     * are present in the analytics data.
     *
     * @param $countriesViews
     * @return array
     */
    public static function getCountriesColor($countriesViews)
    {
        $rgbColor = [];
        $countriesColor = [];
        $countriesNum = count($countriesViews);

        // Iterate same time as the number of the countries
        for ($i = 0; $i <= $countriesNum; $i++) {
            foreach (['r', 'g', 'b'] as $color) {
                $rgbColor[$color] = mt_rand(0, 255);
            }
            $countriesColor[] = $rgbColor['r'].', '.$rgbColor['g'].', '.$rgbColor['b'];
        }

        return $countriesColor;
    }

    /**
     * Load the short URL referers' list.
     *
     * @param $url
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getUrlReferers($url)
    {
        $referers = ViewUrl::where(['short_url' => $url])
            ->select('referer', \DB::raw('sum(click+real_click) as clicks'), \DB::raw('sum(real_click) as real_clicks'))
            ->groupBy('referer')
            ->orderBy('real_clicks', 'DESC')
            ->paginate(20);

        return $referers;
    }

    /**
     * Get latest Short URL Clicks, for dashboard widget.
     *
     * @param $url
     * @return mixed
     */
    public static function getLatestClicks($url)
    {
        $clicks = ViewUrl::where(['short_url' => $url])
            ->select('referer', 'created_at')
            ->orderBy('created_at', 'DESC')
            ->take(8)
            ->get();

        return $clicks;
    }
}
