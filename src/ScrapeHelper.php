<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use \Datetime;

class ScrapeHelper
{
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    
    public static function getDate(string $shippingInformation) {

        // Mapping arrays for day and month values
        $daysMap = array_combine(range(1, 9), array_map('sprintf', array_fill(0, 9, '%02d'), range(1, 9)));
        $monthsMap = [
            "Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06",
            "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12"
        ];

        // Case when date shows "tomorrow"
        if (str_contains($shippingInformation, 'tomorrow')) {
            $date = new DateTime('tomorrow');
            return $date->format('Y-m-d');
        }

        // Case when date matches the format: 03/03/2012 , 30-01-22
        if (preg_match('/(\d{1,2})[-\/]+(\d{1,2})[-\/]+(\d{2,4})/', $shippingInformation, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
        } else {
            // Case when date matches the format: 25 Aug 2022, 1st Sep 2022
            if (preg_match('/(\d{1,2})(?:st|nd|rd|th)?\s([A-Z][a-z]{2})\s(\d{4})/', $shippingInformation, $matches)) {
                $day = sprintf('%02d', (int)$matches[1]);
                $month = $monthsMap[$matches[2]];
                $year = $matches[3];
            } else {
                // Case when no date was found: "Free Delivery"
                return null;
            }
        }

        // Concatenate string into the correct date format
        $shippingDate = "$year-$month-$day";

        return $shippingDate;

    }

}
