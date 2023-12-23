<?php

namespace Src\Domain\Delivery\Widget;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Src\Domain\Delivery\Models\CdekPoint;

class Widget
{
    /**
     * @var string Base Url for API 2.0 Production
     */
    private $baseUrl;

    /**
     * @var string Auth Token
     */
    private $authToken;

    /**
     * @var array Data From Request
     */
    private $requestData;

    public function __construct($baseUrl = 'https://api.cdek.ru/v2')
    {
        $this->baseUrl = $baseUrl;
    }

    public function process($requestData, $body)
    {
        $this->requestData = array_merge($requestData, json_decode($body, true) ?: []);

        if (! isset($this->requestData['action'])) {
            $this->sendValidationError('Action is required');
        }

        $this->getAuthToken();

        switch ($this->requestData['action']) {
            case 'offices':
                return $this->getOffices();
            case 'calculate':
                return $this->calculate();
            default:
                $this->sendValidationError('Unknown action');
        }
    }

    private function sendValidationError($message)
    {
        $this->http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['message' => $message]);
        exit();
    }

    private function getAuthToken()
    {
        $this->authToken = cdek_auth_token();
    }

    private function httpRequest($method, $data, $useFormData = false, $useJson = false)
    {
        $ch = curl_init("$this->baseUrl/$method");

        $headers = [
            'Accept: application/json',
        ];

        if ($this->authToken) {
            $headers[] = "Authorization: Bearer $this->authToken";
        }

        if ($useFormData) {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
            ]);
        } elseif ($useJson) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
            ]);
        } else {
            curl_setopt($ch, CURLOPT_URL, "$this->baseUrl/$method?".http_build_query($data));
        }

        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => 'widget/2.0',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new RuntimeException(curl_error($ch), curl_errno($ch));
        }

        return $result;
    }

    protected function getOffices()
    {
        return CdekPoint::query()
            ->whereIn('region_code', array_values(config('delivery.cdek.allowed_regions')))
            ->when(isset($this->requestData['is_handout']), function ($query) {
                $query->where('is_handout', $this->requestData['is_handout'] === 'true');
            })
            ->when(isset($this->requestData['have_cashless']), function ($query) {
                $query->where('have_cashless', $this->requestData['have_cashless'] === 'true');
            })
            ->when(isset($this->requestData['have_cash']), function ($query) {
                $query->where('have_cash', $this->requestData['have_cash'] === 'true');
            })
            ->when(isset($this->requestData['is_dressing_room']), function ($query) {
                $query->where('is_dressing_room', $this->requestData['is_dressing_room'] === 'true');
            })
            ->when(isset($this->requestData['type']), function ($query) {
                $query->where('type', $this->requestData['type']);
            })
            ->get();
        // return $this->httpRequest('deliverypoints', $this->requestData);

    }

    protected function calculate()
    {
        $data = Http::cdek()->post('calculator/tarifflist', $this->requestData)->json();

        return $data;
    }

    private function http_response_code($code)
    {
        switch ($code) {
            case 100: $text = 'Continue';
                break;
            case 101: $text = 'Switching Protocols';
                break;
            case 200: $text = 'OK';
                break;
            case 201: $text = 'Created';
                break;
            case 202: $text = 'Accepted';
                break;
            case 203: $text = 'Non-Authoritative Information';
                break;
            case 204: $text = 'No Content';
                break;
            case 205: $text = 'Reset Content';
                break;
            case 206: $text = 'Partial Content';
                break;
            case 300: $text = 'Multiple Choices';
                break;
            case 301: $text = 'Moved Permanently';
                break;
            case 302: $text = 'Moved Temporarily';
                break;
            case 303: $text = 'See Other';
                break;
            case 304: $text = 'Not Modified';
                break;
            case 305: $text = 'Use Proxy';
                break;
            case 400: $text = 'Bad Request';
                break;
            case 401: $text = 'Unauthorized';
                break;
            case 402: $text = 'Payment Required';
                break;
            case 403: $text = 'Forbidden';
                break;
            case 404: $text = 'Not Found';
                break;
            case 405: $text = 'Method Not Allowed';
                break;
            case 406: $text = 'Not Acceptable';
                break;
            case 407: $text = 'Proxy Authentication Required';
                break;
            case 408: $text = 'Request Time-out';
                break;
            case 409: $text = 'Conflict';
                break;
            case 410: $text = 'Gone';
                break;
            case 411: $text = 'Length Required';
                break;
            case 412: $text = 'Precondition Failed';
                break;
            case 413: $text = 'Request Entity Too Large';
                break;
            case 414: $text = 'Request-URI Too Large';
                break;
            case 415: $text = 'Unsupported Media Type';
                break;
            case 500: $text = 'Internal Server Error';
                break;
            case 501: $text = 'Not Implemented';
                break;
            case 502: $text = 'Bad Gateway';
                break;
            case 503: $text = 'Service Unavailable';
                break;
            case 504: $text = 'Gateway Time-out';
                break;
            case 505: $text = 'HTTP Version not supported';
                break;
            default:
                exit('Unknown http status code "'.htmlentities($code).'"');
                break;
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol.' '.$code.' '.$text);
        $GLOBALS['http_response_code'] = $code;
    }
}
