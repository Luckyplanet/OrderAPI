<?php
namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order as Order;
use Illuminate\Http\Response;

class OrderValidation
{
    protected static $create_validation
        = [
        'origin' => 'bail|required|array|size:2',
        'origin.0' => "required|string", //lattitude
        'origin.1' => 'required|string', //longitude
        'destination' => 'bail|required|array|size:2',
        'destination.0' => 'required|string', //lattitude
        'destination.1' => 'required|string', //longitude
        ];
    
    protected static $update_validation = [
        'id' => 'bail|required|integer',
    ];

    protected static $index_validation
        = [
        'limit' => 'bail|required|integer|min:1',
        'page' => 'bail|required|integer|min:1',
        ];
    
    
    protected static $response_message
        = [
            'success' => 'SUCCESS',
            'fail' => [
                'required' => 'INVALID_PARAMETER',
                'string' => 'INVALID_PARAMETER_DATA_TYPE',
                'integer' => 'INVALID_PARAMETER_DATA_TYPE',
                'array' => 'INVALID_PARAMETER_DATA_TYPE',
                'size' => 'INVALID_NUMBER_OF_PARAMETER',
                'min' => 'NUMBER_MUST_BE_ABOVE_0',
            ]
        ];
    

    /**
     * Function to validate listing order request.
     *
     * @param  Request $request
     * @return array
     */
    public static function validateIndexRequest(Request $request): array
    {
        if (empty($request->get('page')) || empty($request->get('limit'))) {
            return ['error' => "INVALID_PARAMETER",
                'code' => Response::HTTP_BAD_REQUEST];
        }
        $validator = Validator::make(
            $request->all(),
            self::$index_validation,
            self::getMessage('fail')
        );
        if ($validator->fails()) {
            return ['error' => $validator->errors()->first() ,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY];
        }
        return [];
    }

    /**
     * Function to validate placing order request.
     *
     * @param  Request $request
     * @return array
     */
    public static function validateCreateRequest(Request $request): array
    {
        $inputs = $request->all();
        if (empty($inputs)) {
            return ['error' => "BAD_REQUEST" ,
                'code' => Response::HTTP_BAD_REQUEST];
        }
        $response = Validator::make(
            $inputs,
            self::$create_validation,
            self::getMessage('fail')
        );
        if ($response->fails()) {
            return ['error' => $response->errors()->first() ,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY];
        }
        $lat1 = array_get($inputs, 'origin.0', '');
        $long1 = array_get($inputs, 'origin.1', '');
        $lat2 = array_get($inputs, 'destination.0', '');
        $long2 = array_get($inputs, 'destination.1', '');
        if (!is_numeric($lat1) ||  !is_numeric($long1) ||  !is_numeric($lat2) ||  !is_numeric($long2)) {
            return ["error" => "ALL_LATITUDE_AND_LONGITUDE_ARE_NOT_VALID.",
                "code" => Response::HTTP_UNPROCESSABLE_ENTITY];
        }
        return [];
    }

    /**
     * Function to validate taking order request.
     *
     * @param  Request $request
     * @param integer $id
     * @return array
     */
    public static function validateUpdateRequest(Request $request, $id): array
    {
        if (empty($request->all())) {
            return ['error' => "BAD_REQUEST" , 'code' => Response::HTTP_BAD_REQUEST];
        }
        $request['id'] = $id;
        $inputs = $request->all();
        if (empty(array_get($inputs, 'status', '')) ||
            array_get($inputs, 'status', '') != Order::TAKEN
        ) {
            return ['error' => "INVALID_PARAMETER" ,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY];
        }
        $response = Validator::make(
            $inputs,
            self::$update_validation,
            self::getMessage('fail')
        );
        if ($response->fails()) {
            return ['error' => $response->errors()->first() ,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY];
        }
        return [];
    }
    
    /**
     * Function to get message.
     *
     * @param  string $key ,
     * @return string
     */
    public static function getMessage($key = null)
    {
        if ($key === null) {
            return self::$response_message;
        }
        return array_get(self::$response_message, $key);
    }
}
