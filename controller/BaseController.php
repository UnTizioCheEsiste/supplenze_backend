<?php
class BaseController
{
    /**
     * Ottieni gli elementi del URI.
     * 
     * @return array
     */
    protected function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);
        return $uri;
    }

    /**
     * Ottieni i querystring.
     * 
     * @return array 
     */
    protected function getQueryStringParams()
    {
        parse_str($_SERVER['QUERY_STRING'], $parsed);
        return $parsed;
    }
}
